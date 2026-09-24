"""Upload W-5 videos 9, 10, 11 to section 49."""
import os
import subprocess
import sys
import time

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
SECTION_ID = 49
FOLDER = r"F:\1. LTBI WEBSITE FOLDERS\4. FACT Sheet\1. Training and workshop Program\5. W-5_END LTB"
FILES = [
    "9. 20250407_152005.mp4",
    "10. 20250407_153924.mp4",
    "11. 20250407_160927.mp4",
]
LOG = os.path.join(os.path.dirname(__file__), "_upload_w5_videos_9_11.log")


def log(msg: str) -> None:
    line = f"[{time.strftime('%H:%M:%S')}] {msg}"
    print(line, flush=True)
    with open(LOG, "a", encoding="utf-8") as f:
        f.write(line + "\n")


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
$stored = 'page_sections/videos/' . uniqid('w5_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
$full = storage_path('app/public/' . $stored);
copy($src, $full);
$media = $section->media()->create(['type' => 'video', 'file_path' => $stored, 'title' => $name]);
echo "Attached media id={{$media->id}} path=$stored size=" . filesize($full) . "\\n";
echo "DONE videos=" . $section->media()->where('type', 'video')->count() . "\\n";
"""
    local_php = os.path.join(os.path.dirname(__file__), "_attach_w5_one.php")
    with open(local_php, "w", encoding="utf-8", newline="\n") as f:
        f.write(php)
    subprocess.run(
        ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local_php, f"{HOST}:{APP}/_attach_w5_one.php"],
        check=True,
    )
    return subprocess.run(
        ["ssh", "-i", KEY, HOST, f"cd {APP} && php82 _attach_w5_one.php"],
        capture_output=True,
        text=True,
    )


def main() -> None:
    if os.path.exists(LOG):
        os.remove(LOG)

    if not os.path.isdir(FOLDER):
        log(f"Folder not found: {FOLDER}")
        sys.exit(1)

    paths = []
    for name in FILES:
        path = os.path.join(FOLDER, name)
        if not os.path.isfile(path):
            log(f"Missing file: {path}")
            sys.exit(1)
        paths.append(path)

    total_mb = sum(os.path.getsize(p) for p in paths) / (1024**2)
    log(f"W5 section {SECTION_ID} — uploading {len(paths)} videos ({total_mb:.1f} MB total)")

    for i, local in enumerate(paths, start=1):
        original = os.path.basename(local)
        remote = f"w5_video_{i}.mp4"
        size_mb = os.path.getsize(local) / (1024**2)
        log(f"[{i}/{len(paths)}] SCP {original} ({size_mb:.1f} MB) — starting...")
        t0 = time.time()
        r = subprocess.run(
            ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local, f"{HOST}:{REMOTE_UP}/{remote}"],
        )
        elapsed = time.time() - t0
        if r.returncode != 0:
            log(f"SCP FAILED for {original}")
            sys.exit(r.returncode)
        log(f"[{i}/{len(paths)}] SCP done in {elapsed/60:.1f} min — attaching...")

        r2 = attach_php(remote, original)
        if r2.stdout:
            log(r2.stdout.strip())
        if r2.stderr:
            log(r2.stderr.strip())
        if r2.returncode != 0:
            log(f"ATTACH FAILED for {original}")
            sys.exit(r2.returncode)
        log(f"[{i}/{len(paths)}] COMPLETE — progress {i}/{len(paths)} videos")

    subprocess.run(["ssh", "-i", KEY, HOST, f"cd {APP} && php82 artisan view:clear"], check=True)
    log("ALL DONE — W5 videos 9, 10, 11 attached.")


if __name__ == "__main__":
    main()
