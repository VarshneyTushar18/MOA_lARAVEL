"""Bulk-upload section images via tar batches (e.g. factsheet W1 = section 45)."""
import argparse
import json
import os
import re
import subprocess
import sys
import tarfile
import time

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
IMAGE_EXTS = {".jpg", ".jpeg", ".png", ".gif", ".webp"}


def natural_key(name: str) -> list:
    return [int(part) if part.isdigit() else part.lower() for part in re.split(r"(\d+)", name)]


def collect_images(folder: str, exclude_dirs: set[str]) -> list[str]:
    folder = os.path.abspath(folder)
    files: list[str] = []
    for name in sorted(os.listdir(folder), key=natural_key):
        path = os.path.join(folder, name)
        if not os.path.isfile(path):
            continue
        if os.path.splitext(name)[1].lower() not in IMAGE_EXTS:
            continue
        files.append(path)
    return files


def make_batches(files: list[str], max_bytes: int) -> list[list[str]]:
    batches: list[list[str]] = []
    current: list[str] = []
    current_size = 0
    for path in files:
        size = os.path.getsize(path)
        if current and current_size + size > max_bytes:
            batches.append(current)
            current = []
            current_size = 0
        current.append(path)
        current_size += size
    if current:
        batches.append(current)
    return batches


def run(cmd: list[str]) -> int:
    print("+", " ".join(cmd[:4]), "..." if len(cmd) > 4 else "")
    result = subprocess.run(cmd)
    return result.returncode


def write_attach_php(path: str, section_id: int) -> None:
    content = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

$sectionId = __SECTION_ID__;
$batch = $argv[1] ?? '';
if ($batch === '') { fwrite(STDERR, "Batch id required\\n"); exit(1); }

$up = '/home/sites/41b/b/ba690bc503/moa_deploy_upload';
$manifestPath = "$up/w1_images_{$batch}.json";
$extractDir = "$up/w1_images_{$batch}";
$tarPath = "$up/w1_images_{$batch}.tar";

if (!file_exists($manifestPath)) { echo "Missing manifest $manifestPath\\n"; exit(1); }
if (!file_exists($tarPath)) { echo "Missing tar $tarPath\\n"; exit(1); }

if (!is_dir($extractDir)) mkdir($extractDir, 0755, true);
foreach (glob($extractDir . '/*') ?: [] as $old) {
    if (is_file($old)) unlink($old);
}
$cmd = 'tar -xf ' . escapeshellarg($tarPath) . ' -C ' . escapeshellarg($extractDir);
exec($cmd, $out, $code);
if ($code !== 0) { echo "tar extract failed ($code)\\n"; exit(1); }

$section = App\\Models\\PageSection::findOrFail($sectionId);
$names = json_decode(file_get_contents($manifestPath), true);
if (!is_array($names)) { echo "Bad manifest\\n"; exit(1); }

$destDir = storage_path('app/public/page_sections/images');
if (!is_dir($destDir)) mkdir($destDir, 0755, true);

$existing = $section->images()->pluck('image')->all();
$existingBase = array_map(static fn ($p) => strtolower(basename((string) $p)), $existing);

$added = 0;
$skipped = 0;
foreach ($names as $name) {
    $base = basename((string) $name);
    if (in_array(strtolower($base), $existingBase, true)) {
        $skipped++;
        continue;
    }
    $src = $extractDir . '/' . $base;
    if (!is_file($src)) {
        echo "Missing extracted file: $src\\n";
        continue;
    }
    $stored = 'page_sections/images/' . uniqid('w1_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $base);
    $full = storage_path('app/public/' . $stored);
    copy($src, $full);
    $section->images()->create(['image' => $stored]);
    $existingBase[] = strtolower($base);
    $added++;
    if ($added % 25 === 0) echo "Added $added...\\n";
}

echo "DONE batch=$batch section={$section->id} added=$added skipped=$skipped total=" . $section->images()->count() . "\\n";
"""
    content = content.replace("__SECTION_ID__", str(section_id))
    with open(path, "w", encoding="utf-8", newline="\n") as handle:
        handle.write(content)


def main() -> None:
    parser = argparse.ArgumentParser(description="Bulk upload images to a page section")
    parser.add_argument(
        "folder",
        default=r"F:\1. LTBI WEBSITE FOLDERS\4. FACT Sheet\1. Training and workshop Program\1. W-1",
        nargs="?",
    )
    parser.add_argument("--section-id", type=int, default=45)
    parser.add_argument("--max-batch-gb", type=float, default=10.0)
    parser.add_argument("--limit", type=int, default=0)
    parser.add_argument("--start-batch", type=int, default=1)
    args = parser.parse_args()

    folder = os.path.abspath(args.folder)
    if not os.path.isdir(folder):
        print(f"Folder not found: {folder}")
        sys.exit(1)

    files = collect_images(folder, {"importent"})
    if args.limit > 0:
        files = files[: args.limit]

    if not files:
        print(f"No images found in: {folder}")
        sys.exit(1)

    total_bytes = sum(os.path.getsize(f) for f in files)
    max_bytes = int(args.max_batch_gb * 1024 * 1024 * 1024)
    batches = make_batches(files, max_bytes)

    print(f"Images: {len(files)}")
    print(f"Total: {total_bytes / (1024**3):.2f} GB in {len(batches)} batch(es)")

    base_dir = os.path.dirname(__file__)
    attach_php = os.path.join(base_dir, "_attach_section_images.php")
    write_attach_php(attach_php, args.section_id)

    if run(["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", attach_php, f"{HOST}:{APP}/_attach_section_images.php"]) != 0:
        sys.exit(1)

    for index, batch_files in enumerate(batches, start=1):
        if index < args.start_batch:
            continue

        batch_id = f"batch{index}"
        manifest_names = [os.path.basename(f) for f in batch_files]
        batch_bytes = sum(os.path.getsize(f) for f in batch_files)
        local_tar = os.path.join(base_dir, f"_w1_images_{batch_id}.tar")
        local_manifest = os.path.join(base_dir, f"_w1_images_{batch_id}.json")

        print(f"\n=== Batch {index}/{len(batches)}: {len(batch_files)} files, {batch_bytes / (1024**3):.2f} GB ===")

        with open(local_manifest, "w", encoding="utf-8") as handle:
            json.dump(manifest_names, handle)

        if os.path.exists(local_tar):
            os.remove(local_tar)

        started = time.time()
        with tarfile.open(local_tar, "w") as tar:
            for path in batch_files:
                tar.add(path, arcname=os.path.basename(path))
        print(f"Tar built in {time.time() - started:.1f}s -> {local_tar}")

        remote_tar = f"{REMOTE_UP}/w1_images_{batch_id}.tar"
        remote_manifest = f"{REMOTE_UP}/w1_images_{batch_id}.json"

        if run(["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local_tar, f"{HOST}:{remote_tar}"]) != 0:
            sys.exit(1)
        if run(["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local_manifest, f"{HOST}:{remote_manifest}"]) != 0:
            sys.exit(1)

        attach = subprocess.run(
            ["ssh", "-i", KEY, HOST, f"cd {APP} && php82 _attach_section_images.php {batch_id}"],
            capture_output=True,
            text=True,
        )
        print(attach.stdout)
        print(attach.stderr)
        if attach.returncode != 0:
            sys.exit(attach.returncode)

        if os.path.exists(local_tar):
            os.remove(local_tar)

    print("\nAll batches complete.")


if __name__ == "__main__":
    main()
