"""Upload W-6 IMPORTENT videos to section 115 (skip file #2)."""
import argparse
import os
import subprocess
import sys
import time

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
SECTION_ID = 115
FOLDER = r"F:\1. LTBI WEBSITE FOLDERS\4. FACT Sheet\1. Training and workshop Program\6. W-6\IMPORTENT"
SKIP_PREFIXES = ("2. ", "2.")
VIDEO_EXTS = {".mp4", ".mov", ".avi", ".webm", ".mkv"}


def attach_php(remote_name: str, original: str) -> subprocess.CompletedProcess:
    safe_original = original.replace("'", "\\'")
    php = f"""<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
$section = App\\Models\\PageSection::findOrFail({SECTION_ID});
$up = '{REMOTE_UP}';
$src = $up . '/{remote_name}';
if (!file_exists($src)) {{ echo "Missing $src\\n"; exit(1); }}
$destDir = storage_path('app/public/page_sections/videos');
if (!is_dir($destDir)) mkdir($destDir, 0755, true);
$name = '{safe_original}';
$stored = 'page_sections/videos/' . uniqid('w6_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
$full = storage_path('app/public/' . $stored);
copy($src, $full);
$media = $section->media()->create(['type' => 'video', 'file_path' => $stored, 'title' => $name]);
echo "Attached media id={{$media->id}} path=$stored size=" . filesize($full) . "\\n";
echo "DONE videos=" . $section->media()->where('type', 'video')->count() . "\\n";
"""
    local_php = os.path.join(os.path.dirname(__file__), "_attach_w6_one.php")
    with open(local_php, "w", encoding="utf-8", newline="\n") as f:
        f.write(php)

    subprocess.run(
        ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local_php, f"{HOST}:{APP}/_attach_w6_one.php"],
        check=True,
    )
    return subprocess.run(
        ["ssh", "-i", KEY, HOST, f"cd {APP} && php82 _attach_w6_one.php"],
        capture_output=True,
        text=True,
    )


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--from-index", type=int, default=1, help="1-based file index to start from")
    args = parser.parse_args()

    if not os.path.isdir(FOLDER):
        print(f"Folder not found: {FOLDER}")
        sys.exit(1)

    files = []
    for name in sorted(os.listdir(FOLDER)):
        if os.path.splitext(name)[1].lower() not in VIDEO_EXTS:
            continue
        if any(name.startswith(p) for p in SKIP_PREFIXES):
            print(f"SKIP: {name}")
            continue
        files.append(os.path.join(FOLDER, name))

    if not files:
        print("No videos to upload.")
        sys.exit(1)

    print(f"W6 section {SECTION_ID} — uploading {len(files)} videos:")
    for f in files:
        print(f"  {os.path.basename(f)} ({os.path.getsize(f) / (1024**2):.1f} MB)")

    for i, local in enumerate(files, start=1):
        if i < args.from_index:
            print(f"SKIP [{i}/{len(files)}] already done: {os.path.basename(local)}")
            continue
        original = os.path.basename(local)
        remote = f"w6_video_{i}.mp4"
        size_mb = os.path.getsize(local) / (1024**2)
        print(f"\n[{i}/{len(files)}] SCP {original} ({size_mb:.1f} MB) ...")
        t0 = time.time()
        r = subprocess.run(
            ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local, f"{HOST}:{REMOTE_UP}/{remote}"],
        )
        elapsed = time.time() - t0
        if r.returncode != 0:
            print(f"SCP FAILED for {original}")
            sys.exit(r.returncode)
        print(f"SCP ok in {elapsed/60:.1f} min")

        print(f"Attaching {original} ...")
        r2 = attach_php(remote, original)
        print(r2.stdout)
        if r2.stderr:
            print(r2.stderr)
        if r2.returncode != 0:
            print(f"ATTACH FAILED for {original}")
            sys.exit(r2.returncode)

    subprocess.run(
        ["ssh", "-i", KEY, HOST, f"cd {APP} && php82 artisan view:clear"],
        check=True,
    )
    print("\nALL DONE — W6 videos attached.")


if __name__ == "__main__":
    main()
