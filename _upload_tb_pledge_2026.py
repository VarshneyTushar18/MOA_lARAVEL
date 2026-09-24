"""Upload TB Pledge 2026 videos (root only, no subfolders) sorted by date."""
import argparse
import os
import re
import subprocess
import sys
import time
from datetime import datetime

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
SECTION_ID = 53
FOLDER = r"F:\1. LTBI WEBSITE FOLDERS\5. ACSM and IEC\8. Tb Pledge and Awareness Videos\4. All Videos_2026"
BASE_SORT = 2000
VIDEO_EXTS = {".mp4", ".mov", ".avi", ".webm", ".mkv"}
LOG = os.path.join(os.path.dirname(__file__), "_upload_tb_pledge_2026.log")
STATUS = os.path.join(os.path.dirname(__file__), "_upload_tb_pledge_2026_status.txt")


def parse_date_from_name(name: str) -> datetime:
    patterns = [
        (r"(\d{4})(\d{2})(\d{2})_(\d{6})", lambda m: datetime(
            int(m.group(1)), int(m.group(2)), int(m.group(3)),
            int(m.group(4)[:2]), int(m.group(4)[2:4]), int(m.group(4)[4:6]),
        )),
        (r"VID-(\d{4})(\d{2})(\d{2})", lambda m: datetime(int(m.group(1)), int(m.group(2)), int(m.group(3)))),
        (r"(\d{4})-(\d{2})-(\d{2})", lambda m: datetime(int(m.group(1)), int(m.group(2)), int(m.group(3)))),
        (r"(\d{2})\.(\d{2})\.(\d{4})", lambda m: datetime(int(m.group(3)), int(m.group(2)), int(m.group(1)))),
    ]
    for pattern, builder in patterns:
        match = re.search(pattern, name)
        if not match:
            continue
        try:
            return builder(match)
        except ValueError:
            continue
    return datetime.max


def log(msg: str) -> None:
    line = f"[{time.strftime('%H:%M:%S')}] {msg}"
    print(line, flush=True)
    with open(LOG, "a", encoding="utf-8") as f:
        f.write(line + "\n")


def write_status(done: int, total: int, current: str, pct: float, note: str = "") -> None:
    with open(STATUS, "w", encoding="utf-8") as f:
        f.write(
            f"TB Pledge 2026 Videos Upload\n"
            f"Progress: {done}/{total} videos ({pct:.0f}%)\n"
            f"Current: {current}\n"
            f"{note}\n"
            f"Updated: {time.strftime('%Y-%m-%d %H:%M:%S')}\n"
        )


def scp_with_retry(local: str, remote: str, retries: int = 3) -> bool:
    for attempt in range(1, retries + 1):
        result = subprocess.run(
            ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local, f"{HOST}:{REMOTE_UP}/{remote}"],
        )
        if result.returncode == 0:
            return True
        log(f"SCP attempt {attempt}/{retries} failed — retrying in 10s...")
        time.sleep(10)
    return False


def attach_php(remote_name: str, original: str, sort_order: int) -> subprocess.CompletedProcess:
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
$stored = 'page_sections/videos/' . uniqid('tb2026_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
$full = storage_path('app/public/' . $stored);
copy($src, $full);
$media = $section->media()->create([
    'type' => 'video',
    'file_path' => $stored,
    'title' => $name,
    'sort_order' => {sort_order},
]);
echo "Attached id={{$media->id}} sort={sort_order} size=" . filesize($full) . "\\n";
echo "DONE videos=" . $section->media()->where('type', 'video')->count() . "\\n";
"""
    local_php = os.path.join(os.path.dirname(__file__), "_attach_tb_pledge_2026_one.php")
    with open(local_php, "w", encoding="utf-8", newline="\n") as f:
        f.write(php)
    subprocess.run(
        ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local_php, f"{HOST}:{APP}/_attach_tb_pledge_2026_one.php"],
        check=True,
    )
    return subprocess.run(
        ["ssh", "-i", KEY, HOST, f"cd {APP} && php82 _attach_tb_pledge_2026_one.php"],
        capture_output=True,
        text=True,
    )


def collect_files():
    names = [
        name for name in os.listdir(FOLDER)
        if os.path.isfile(os.path.join(FOLDER, name)) and os.path.splitext(name)[1].lower() in VIDEO_EXTS
    ]
    names.sort(key=lambda name: (parse_date_from_name(name), name.lower()))
    return [os.path.join(FOLDER, name) for name in names]


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

    files = collect_files()
    if not files:
        log("No video files found in folder root.")
        sys.exit(1)

    total_bytes = sum(os.path.getsize(path) for path in files)
    total_mb = total_bytes / (1024**2)
    log(f"TB Pledge 2026 — {len(files)} videos, {total_mb:.1f} MB, sort base {BASE_SORT}")
    write_status(0, len(files), "Starting...", 0, f"Year 2026 | {total_mb/1024:.2f} GB")

    uploaded_bytes = sum(os.path.getsize(p) for p in files[: args.from_index - 1])
    for index, local in enumerate(files, start=1):
        if index < args.from_index:
            log(f"SKIP [{index}/{len(files)}]: {os.path.basename(local)}")
            continue

        original = os.path.basename(local)
        remote = f"tb_pledge_2026_{index}.mp4"
        sort_order = BASE_SORT + index
        file_date = parse_date_from_name(original)
        size_mb = os.path.getsize(local) / (1024**2)
        date_label = file_date.strftime("%Y-%m-%d %H:%M") if file_date != datetime.max else "unknown"
        log(f"[{index}/{len(files)}] {date_label} | {original} ({size_mb:.1f} MB) sort={sort_order}")

        if not scp_with_retry(local, remote):
            sys.exit(1)
        uploaded_bytes += os.path.getsize(local)

        result = attach_php(remote, original, sort_order)
        if result.stdout:
            log(result.stdout.strip())
        if result.returncode != 0:
            if result.stderr:
                log(result.stderr.strip())
            sys.exit(1)

        pct = (uploaded_bytes / total_bytes) * 100
        log(f"[{index}/{len(files)}] COMPLETE — {pct:.0f}%")
        write_status(index, len(files), original, pct, f"Year 2026 | {index}/{len(files)}")

    subprocess.run(["ssh", "-i", KEY, HOST, f"cd {APP} && php82 artisan view:clear"], check=True)
    log("ALL DONE — TB Pledge 2026 videos uploaded (after 2024 + 2025).")


if __name__ == "__main__":
    main()
