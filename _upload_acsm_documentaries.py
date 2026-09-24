"""Upload ACSM documentaries section (id 69) — all MP4s with live progress log."""
import argparse
import os
import re
import subprocess
import sys
import time

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
SECTION_ID = 69
FOLDER = r"F:\1. LTBI WEBSITE FOLDERS\5. ACSM and IEC\6. Documentaries and audio Video Clips_to be edited By Dr. Divya Kajaria"
VIDEO_EXTS = {".mp4", ".mov", ".avi", ".webm", ".mkv"}
LOG = os.path.join(os.path.dirname(__file__), "_upload_acsm_documentaries.log")
STATUS = os.path.join(os.path.dirname(__file__), "_upload_acsm_documentaries_status.txt")


def natural_key(name: str):
    return [int(part) if part.isdigit() else part.lower() for part in re.split(r"(\d+)", name)]


def log(msg: str) -> None:
    line = f"[{time.strftime('%H:%M:%S')}] {msg}"
    print(line, flush=True)
    with open(LOG, "a", encoding="utf-8") as f:
        f.write(line + "\n")


def write_status(done: int, total: int, current: str, pct: float, note: str = "") -> None:
    text = (
        f"ACSM Documentaries Upload\n"
        f"Progress: {done}/{total} videos ({pct:.0f}%)\n"
        f"Current: {current}\n"
        f"{note}\n"
        f"Updated: {time.strftime('%Y-%m-%d %H:%M:%S')}\n"
    )
    with open(STATUS, "w", encoding="utf-8") as f:
        f.write(text)


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
$stored = 'page_sections/videos/' . uniqid('acsm_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
$full = storage_path('app/public/' . $stored);
copy($src, $full);
$media = $section->media()->create(['type' => 'video', 'file_path' => $stored, 'title' => $name]);
echo "Attached media id={{$media->id}} path=$stored size=" . filesize($full) . "\\n";
echo "DONE videos=" . $section->media()->where('type', 'video')->count() . "\\n";
"""
    local_php = os.path.join(os.path.dirname(__file__), "_attach_acsm_one.php")
    with open(local_php, "w", encoding="utf-8", newline="\n") as f:
        f.write(php)
    subprocess.run(
        ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local_php, f"{HOST}:{APP}/_attach_acsm_one.php"],
        check=True,
    )
    return subprocess.run(
        ["ssh", "-i", KEY, HOST, f"cd {APP} && php82 _attach_acsm_one.php"],
        capture_output=True,
        text=True,
    )


def collect_files():
    names = [
        n for n in os.listdir(FOLDER)
        if os.path.splitext(n)[1].lower() in VIDEO_EXTS and os.path.isfile(os.path.join(FOLDER, n))
    ]
    names.sort(key=natural_key)
    return [os.path.join(FOLDER, n) for n in names]


def scp_with_retry(local: str, remote: str, retries: int = 3) -> bool:
    for attempt in range(1, retries + 1):
        r = subprocess.run(
            ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local, f"{HOST}:{REMOTE_UP}/{remote}"],
        )
        if r.returncode == 0:
            return True
        log(f"SCP attempt {attempt}/{retries} failed (exit {r.returncode}) — retrying in 10s...")
        time.sleep(10)
    return False


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--from-index", type=int, default=1, help="1-based index to resume from")
    parser.add_argument("--append-log", action="store_true", help="Do not clear existing log")
    args = parser.parse_args()

    if not args.append_log:
        for path in (LOG, STATUS):
            if os.path.exists(path):
                os.remove(path)

    if not os.path.isdir(FOLDER):
        log(f"Folder not found: {FOLDER}")
        sys.exit(1)

    files = collect_files()
    if not files:
        log("No video files found.")
        sys.exit(1)

    total_bytes = sum(os.path.getsize(p) for p in files)
    total_mb = total_bytes / (1024**2)
    log(f"ACSM section {SECTION_ID} — {len(files)} videos, {total_mb:.1f} MB ({total_mb/1024:.2f} GB) total")
    write_status(0, len(files), "Starting...", 0, f"Total size: {total_mb/1024:.2f} GB")

    uploaded_bytes = sum(os.path.getsize(p) for p in files[: args.from_index - 1])
    for i, local in enumerate(files, start=1):
        if i < args.from_index:
            log(f"SKIP [{i}/{len(files)}] already done: {os.path.basename(local)}")
            continue
        original = os.path.basename(local)
        remote = f"acsm_doc_{i}.mp4"
        size_mb = os.path.getsize(local) / (1024**2)
        pct = ((i - 1) / len(files)) * 100
        write_status(i - 1, len(files), f"Uploading: {original}", pct, f"File size: {size_mb:.1f} MB")
        log(f"[{i}/{len(files)}] SCP {original} ({size_mb:.1f} MB) — starting...")

        t0 = time.time()
        if not scp_with_retry(local, remote):
            log(f"SCP FAILED for {original} after retries")
            write_status(i - 1, len(files), f"FAILED: {original}", pct, "SCP error — retry needed")
            sys.exit(1)
        elapsed = time.time() - t0

        speed = size_mb / elapsed if elapsed > 0 else 0
        uploaded_bytes += os.path.getsize(local)
        log(f"[{i}/{len(files)}] SCP done in {elapsed/60:.1f} min ({speed:.1f} MB/s) — attaching...")

        r2 = attach_php(remote, original)
        if r2.stdout:
            log(r2.stdout.strip())
        if r2.stderr:
            log(r2.stderr.strip())
        if r2.returncode != 0:
            log(f"ATTACH FAILED for {original}")
            write_status(i - 1, len(files), f"FAILED attach: {original}", (i / len(files)) * 100, "Attach error")
            sys.exit(r2.returncode)

        overall_pct = (uploaded_bytes / total_bytes) * 100
        log(f"[{i}/{len(files)}] COMPLETE — overall {overall_pct:.0f}% ({uploaded_bytes/1024**2:.0f}/{total_mb:.0f} MB)")
        write_status(i, len(files), f"Done: {original}", overall_pct, f"Completed {i} of {len(files)} videos")

    subprocess.run(["ssh", "-i", KEY, HOST, f"cd {APP} && php82 artisan view:clear"], check=True)
    log("ALL DONE — ACSM documentaries uploaded.")
    write_status(len(files), len(files), "All videos uploaded", 100, f"Total: {len(files)} videos on section {SECTION_ID}")


if __name__ == "__main__":
    main()
