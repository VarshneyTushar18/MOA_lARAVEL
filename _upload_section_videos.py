"""Upload local video files to a page section on StackCP (e.g. factsheet W1 = section 45)."""
import argparse
import os
import subprocess
import sys

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
VIDEO_EXTS = {".mp4", ".mov", ".avi", ".webm", ".mkv"}


def main() -> None:
    parser = argparse.ArgumentParser(description="Upload videos to a MOA page section")
    parser.add_argument("folder", help="Local folder containing video files")
    parser.add_argument("--section-id", type=int, default=45, help="Page section ID (W1 = 45)")
    parser.add_argument("--limit", type=int, default=0, help="Max files to upload (0 = all)")
    args = parser.parse_args()

    folder = os.path.abspath(args.folder)
    if not os.path.isdir(folder):
        print(f"Folder not found: {folder}")
        sys.exit(1)

    files = [
        os.path.join(folder, name)
        for name in sorted(os.listdir(folder))
        if os.path.splitext(name)[1].lower() in VIDEO_EXTS
    ]

    if args.limit > 0:
        files = files[: args.limit]

    if not files:
        print(f"No video files found in: {folder}")
        sys.exit(1)

    print("Uploading:", *[os.path.basename(f) for f in files], sep="\n  ")

    remote_names = []
    for i, local in enumerate(files, start=1):
        remote = f"section_video_{i}{os.path.splitext(local)[1].lower()}"
        remote_names.append((remote, os.path.basename(local)))
        print(f"scp -> {remote} ...")
        r = subprocess.run(
            ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local, f"{HOST}:{REMOTE_UP}/{remote}"],
        )
        print("scp", remote, r.returncode, "ok" if r.returncode == 0 else "FAILED")
        if r.returncode != 0:
            sys.exit(r.returncode)

    php_lines = [
        "<?php",
        "require __DIR__.'/vendor/autoload.php';",
        "$app = require __DIR__.'/bootstrap/app.php';",
        "$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();",
        f"$section = App\\Models\\PageSection::findOrFail({args.section_id});",
        "$up = '/home/sites/41b/b/ba690bc503/moa_deploy_upload';",
        "$destDir = storage_path('app/public/page_sections/videos');",
        "if (!is_dir($destDir)) mkdir($destDir, 0755, true);",
    ]

    for remote, original in remote_names:
        php_lines.append(f'$src = $up . "/{remote}";')
        php_lines.append("if (!file_exists($src)) { echo \"Missing $src\\n\"; exit(1); }")
        php_lines.append(f"$name = '{original}';")
        php_lines.append("$stored = 'page_sections/videos/' . uniqid('w1_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);")
        php_lines.append("$full = storage_path('app/public/' . $stored);")
        php_lines.append("copy($src, $full);")
        php_lines.append("$media = $section->media()->create(['type' => 'video', 'file_path' => $stored]);")
        php_lines.append("echo \"Attached media id={$media->id} path=$stored\\n\";")

    php_lines.append("echo \"DONE section={$section->id} key={$section->section_key}\\n\";")

    attach_script = os.path.join(os.path.dirname(__file__), "_attach_section_videos.php")
    with open(attach_script, "w", encoding="utf-8", newline="\n") as f:
        f.write("\n".join(php_lines))

    subprocess.run(
        ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", attach_script, f"{HOST}:{APP}/_attach_section_videos.php"],
        check=True,
    )

    r2 = subprocess.run(
        ["ssh", "-i", KEY, HOST, f"cd {APP} && php82 _attach_section_videos.php && php82 artisan view:clear"],
        capture_output=True,
        text=True,
        timeout=120000,
    )
    print(r2.stdout)
    print(r2.stderr)
    if r2.returncode != 0:
        sys.exit(r2.returncode)


if __name__ == "__main__":
    main()
