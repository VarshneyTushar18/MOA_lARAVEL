"""Upload OPD videos to Best Practices section (opd_videos_and_photographs)."""
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
SECTION_ID = 0  # resolved on server before upload
FOLDER = r"F:\1. LTBI WEBSITE FOLDERS\7. Best Practises\4.OPD Videos and Photographs\1. Videos"
VIDEO_EXTS = {".mp4", ".mov", ".avi", ".webm", ".mkv"}
LOG = os.path.join(os.path.dirname(__file__), "_upload_opd_videos.log")
STATUS = os.path.join(os.path.dirname(__file__), "_upload_opd_videos_status.txt")


def natural_key(name: str):
    return [int(part) if part.isdigit() else part.lower() for part in re.split(r"(\d+)", name)]


def should_skip(name: str) -> bool:
    low = name.lower()
    return any(x in low for x in ("dont use", "don't use", "dont upload", "don't upload"))


def log(msg: str) -> None:
    line = f"[{time.strftime('%H:%M:%S')}] {msg}"
    print(line, flush=True)
    with open(LOG, "a", encoding="utf-8") as f:
        f.write(line + "\n")


def write_status(done: int, total: int, current: str, pct: float, note: str = "") -> None:
    text = (
        f"OPD Videos Upload\n"
        f"Progress: {done}/{total} videos ({pct:.0f}%)\n"
        f"Current: {current}\n"
        f"{note}\n"
        f"Updated: {time.strftime('%Y-%m-%d %H:%M:%S')}\n"
    )
    with open(STATUS, "w", encoding="utf-8") as f:
        f.write(text)


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


def ssh_with_retry(args: list[str], retries: int = 5) -> subprocess.CompletedProcess:
    for attempt in range(1, retries + 1):
        r = subprocess.run(args, capture_output=True, text=True)
        if r.returncode == 0:
            return r
        log(f"SSH attempt {attempt}/{retries} failed (exit {r.returncode}) — retrying in 15s...")
        time.sleep(15)
    return r


def resolve_section_id() -> int:
    local_php = os.path.join(os.path.dirname(__file__), "_ensure_opd_section.php")
    if not scp_with_retry(local_php, "_ensure_opd_section.php"):
        raise RuntimeError("Could not upload ensure_opd_section.php")
    r = ssh_with_retry(["ssh", "-i", KEY, HOST, f"cd {APP} && php82 _ensure_opd_section.php"])
    print(r.stdout)
    if r.returncode != 0:
        print(r.stderr)
        raise SystemExit(r.returncode)
    for line in r.stdout.splitlines():
        if line.startswith("existing section id=") or line.startswith("created section id="):
            return int(line.split("=")[1])
    raise RuntimeError("Could not resolve OPD section id")


def attach_php(section_id: int, remote_name: str, original: str, sort_order: int) -> subprocess.CompletedProcess:
    safe_original = original.replace("'", "\\'")
    php = f"""<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
$section = App\\Models\\PageSection::findOrFail({section_id});
$up = '{REMOTE_UP}';
$src = $up . '/{remote_name}';
if (!file_exists($src)) {{ echo "Missing $src\\n"; exit(1); }}
$destDir = storage_path('app/public/page_sections/videos');
if (!is_dir($destDir)) mkdir($destDir, 0755, true);
$name = '{safe_original}';
$stored = 'page_sections/videos/' . uniqid('opd_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
$full = storage_path('app/public/' . $stored);
copy($src, $full);
$media = $section->media()->create([
    'type' => 'video',
    'file_path' => $stored,
    'title' => pathinfo($name, PATHINFO_FILENAME),
    'sort_order' => {sort_order},
]);
echo "Attached media id={{$media->id}} path=$stored size=" . filesize($full) . "\\n";
echo "DONE videos=" . $section->media()->where('type', 'video')->count() . "\\n";
"""
    local_php = os.path.join(os.path.dirname(__file__), "_attach_opd_one.php")
    with open(local_php, "w", encoding="utf-8", newline="\n") as f:
        f.write(php)
    if not scp_with_retry(local_php, "_attach_opd_one.php"):
        raise RuntimeError(f"Could not upload attach script for {original}")
    return ssh_with_retry(["ssh", "-i", KEY, HOST, f"cd {APP} && php82 _attach_opd_one.php"])


def collect_files():
    names = []
    for name in os.listdir(FOLDER):
        path = os.path.join(FOLDER, name)
        if not os.path.isfile(path):
            continue
        if os.path.splitext(name)[1].lower() not in VIDEO_EXTS:
            continue
        if should_skip(name):
            log(f"SKIP excluded file: {name}")
            continue
        names.append(name)
    names.sort(key=natural_key)
    return [os.path.join(FOLDER, n) for n in names]


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--from-index", type=int, default=1)
    parser.add_argument("--append-log", action="store_true")
    args = parser.parse_args()

    if not args.append_log:
        for path in (LOG, STATUS):
            if os.path.exists(path):
                os.remove(path)

    if not os.path.isdir(FOLDER):
        log(f"Folder not found: {FOLDER}")
        sys.exit(1)

    section_id = resolve_section_id()
    files = collect_files()
    if not files:
        log("No video files found.")
        sys.exit(1)

    total_bytes = sum(os.path.getsize(p) for p in files)
    total_mb = total_bytes / (1024**2)
    log(f"OPD section {section_id} — {len(files)} videos, {total_mb:.1f} MB ({total_mb/1024:.2f} GB) total")
    write_status(0, len(files), "Starting...", 0, f"Total size: {total_mb/1024:.2f} GB")

    uploaded_bytes = sum(os.path.getsize(p) for p in files[: args.from_index - 1])
    for i, local in enumerate(files, start=1):
        if i < args.from_index:
            log(f"SKIP [{i}/{len(files)}] already done: {os.path.basename(local)}")
            continue

        original = os.path.basename(local)
        remote = f"opd_video_{i}.mp4"
        size_mb = os.path.getsize(local) / (1024**2)
        pct = ((i - 1) / len(files)) * 100
        write_status(i - 1, len(files), f"Uploading: {original}", pct, f"File size: {size_mb:.1f} MB")
        log(f"[{i}/{len(files)}] SCP {original} ({size_mb:.1f} MB) — starting...")

        t0 = time.time()
        if not scp_with_retry(local, remote):
            log(f"SCP FAILED for {original} after retries")
            write_status(i - 1, len(files), f"FAILED: {original}", pct, "SCP error — retry with --from-index")
            sys.exit(1)
        elapsed = time.time() - t0
        speed = size_mb / elapsed if elapsed > 0 else 0
        uploaded_bytes += os.path.getsize(local)
        log(f"[{i}/{len(files)}] SCP done in {elapsed/60:.1f} min ({speed:.1f} MB/s) — attaching...")

        r2 = attach_php(section_id, remote, original, i)
        if r2.stdout:
            log(r2.stdout.strip())
        if r2.stderr:
            log(r2.stderr.strip())
        if r2.returncode != 0:
            log(f"ATTACH FAILED for {original}")
            write_status(i - 1, len(files), f"FAILED attach: {original}", (i / len(files)) * 100, "Attach error — retry with --from-index")
            sys.exit(1)

        overall_pct = (uploaded_bytes / total_bytes) * 100
        log(f"[{i}/{len(files)}] COMPLETE — overall {overall_pct:.0f}% ({uploaded_bytes/1024**2:.0f}/{total_mb:.0f} MB)")
        write_status(i, len(files), f"Done: {original}", overall_pct, f"Completed {i} of {len(files)} videos")

    subprocess.run(["ssh", "-i", KEY, HOST, f"cd {APP} && php82 artisan view:clear"], check=True)
    log("ALL DONE — OPD videos uploaded.")
    write_status(len(files), len(files), "All videos uploaded", 100, f"Total: {len(files)} videos on section {section_id}")


if __name__ == "__main__":
    main()
