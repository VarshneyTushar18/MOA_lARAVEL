"""Upload TB Pledge 2023 videos sorted by date (appears first in carousel)."""
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
FOLDER = r"F:\1. LTBI WEBSITE FOLDERS\5. ACSM and IEC\8. Tb Pledge and Awareness Videos\1. All Videos_2023"
BASE_SORT = 0
VIDEO_EXTS = {".mp4", ".mov", ".avi", ".webm", ".mkv"}
LOG = os.path.join(os.path.dirname(__file__), "_upload_tb_pledge_2023.log")
STATUS = os.path.join(os.path.dirname(__file__), "_upload_tb_pledge_2023_status.txt")


def parse_date_from_name(name: str) -> datetime:
    match = re.search(r"VID_(\d{4})(\d{2})(\d{2})_(\d{6})", name)
    if not match:
        return datetime.max
    try:
        return datetime(
            int(match.group(1)), int(match.group(2)), int(match.group(3)),
            int(match.group(4)[:2]), int(match.group(4)[2:4]), int(match.group(4)[4:6]),
        )
    except ValueError:
        return datetime.max


def log(msg: str) -> None:
    line = f"[{time.strftime('%H:%M:%S')}] {msg}"
    print(line, flush=True)
    with open(LOG, "a", encoding="utf-8") as f:
        f.write(line + "\n")


def write_status(done: int, total: int, current: str, pct: float, note: str = "") -> None:
    with open(STATUS, "w", encoding="utf-8") as f:
        f.write(
            f"TB Pledge 2023 Videos Upload\n"
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
        log(f"SCP attempt {attempt}/{retries} failed — retrying...")
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
$stored = 'page_sections/videos/' . uniqid('tb2023_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
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
    local_php = os.path.join(os.path.dirname(__file__), "_attach_tb_pledge_2023_one.php")
    with open(local_php, "w", encoding="utf-8", newline="\n") as f:
        f.write(php)
    subprocess.run(
        ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local_php, f"{HOST}:{APP}/_attach_tb_pledge_2023_one.php"],
        check=True,
    )
    return subprocess.run(
        ["ssh", "-i", KEY, HOST, f"cd {APP} && php82 _attach_tb_pledge_2023_one.php"],
        capture_output=True,
        text=True,
    )


def bump_existing_sort_orders() -> None:
    bump_php = os.path.join(os.path.dirname(__file__), "_bump_tb_pledge_sort.php")
    subprocess.run(
        ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", bump_php, f"{HOST}:{APP}/_bump_tb_pledge_sort.php"],
        check=True,
    )
    result = subprocess.run(
        ["ssh", "-i", KEY, HOST, f"cd {APP} && php82 _bump_tb_pledge_sort.php"],
        capture_output=True,
        text=True,
    )
    log(result.stdout.strip() or "Sort bump done")
    if result.returncode != 0:
        log(result.stderr.strip())
        sys.exit(result.returncode)


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
    parser.add_argument("--skip-bump", action="store_true")
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
        log("No videos found.")
        sys.exit(1)

    if not args.skip_bump and args.from_index == 1:
        log("Shifting existing videos +100 sort so 2023 appears first...")
        bump_existing_sort_orders()

    total_bytes = sum(os.path.getsize(path) for path in files)
    log(f"TB Pledge 2023 — {len(files)} videos, {total_bytes/1024**2:.1f} MB")
    write_status(0, len(files), "Starting...", 0, "Year 2023 first in carousel")

    uploaded_bytes = sum(os.path.getsize(p) for p in files[: args.from_index - 1])
    for index, local in enumerate(files, start=1):
        if index < args.from_index:
            continue
        original = os.path.basename(local)
        sort_order = BASE_SORT + index
        size_mb = os.path.getsize(local) / (1024**2)
        date_label = parse_date_from_name(original).strftime("%Y-%m-%d")
        log(f"[{index}/{len(files)}] {date_label} | {original} ({size_mb:.1f} MB) sort={sort_order}")

        if not scp_with_retry(local, f"tb_pledge_2023_{index}.mp4"):
            sys.exit(1)
        uploaded_bytes += os.path.getsize(local)

        result = attach_php(f"tb_pledge_2023_{index}.mp4", original, sort_order)
        if result.stdout:
            log(result.stdout.strip())
        if result.returncode != 0:
            sys.exit(1)

        pct = (uploaded_bytes / total_bytes) * 100
        write_status(index, len(files), original, pct, f"Year 2023 | {index}/{len(files)}")
        log(f"[{index}/{len(files)}] COMPLETE — {pct:.0f}%")

    subprocess.run(["ssh", "-i", KEY, HOST, f"cd {APP} && php82 artisan view:clear"], check=True)
    log("ALL DONE — 2023 videos uploaded (carousel: 2023 → 2024 → 2025 → 2026).")


if __name__ == "__main__":
    main()
