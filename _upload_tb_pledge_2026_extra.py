"""Upload 4 additional 2026 TB Pledge videos."""
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
FILES = [
    "4. VID-20260321-WA0010.mp4",
    "5. VID-20260321-WA0011.mp4",
    "3. VID-20260321-WA0012.mp4",
    "2.(b)_WhatsApp Video 2026-03-24 at 11.03.48.mp4",
]
BASE_SORT = 2017


def parse_date(name: str) -> datetime:
    for pat, fn in [
        (r"(\d{4})-(\d{2})-(\d{2})", lambda m: datetime(int(m.group(1)), int(m.group(2)), int(m.group(3)))),
        (r"VID-(\d{4})(\d{2})(\d{2})", lambda m: datetime(int(m.group(1)), int(m.group(2)), int(m.group(3)))),
    ]:
        m = re.search(pat, name)
        if m:
            try:
                return fn(m)
            except ValueError:
                pass
    return datetime.max


def main() -> None:
    ordered = sorted(FILES, key=lambda n: (parse_date(n), n.lower()))
    print(f"Uploading {len(ordered)} videos to 2026 batch (sort {BASE_SORT + 1}+)...", flush=True)

    for i, name in enumerate(ordered, start=1):
        local = os.path.join(FOLDER, name)
        if not os.path.isfile(local):
            print(f"Missing: {local}")
            sys.exit(1)
        sort_order = BASE_SORT + i
        remote = f"tb_pledge_2026_extra_{i}.mp4"
        size_mb = os.path.getsize(local) / (1024**2)
        print(f"[{i}/{len(ordered)}] sort={sort_order} {name} ({size_mb:.1f} MB)", flush=True)

        r = subprocess.run(["scp", "-i", KEY, local, f"{HOST}:{REMOTE_UP}/{remote}"])
        if r.returncode != 0:
            sys.exit(r.returncode)

        safe = name.replace("'", "\\'")
        php = f"""<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
$section = App\\Models\\PageSection::findOrFail({SECTION_ID});
$src = '{REMOTE_UP}/{remote}';
$stored = 'page_sections/videos/' . uniqid('tb2026x_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', '{safe}');
copy($src, storage_path('app/public/' . $stored));
$media = $section->media()->create(['type'=>'video','file_path'=>$stored,'title'=>'{safe}','sort_order'=>{sort_order}]);
echo "Attached id={{$media->id}} sort={sort_order}\\n";
echo "DONE videos=".$section->media()->where('type','video')->count()."\\n";
"""
        path = os.path.join(os.path.dirname(__file__), "_attach_2026_extra.php")
        with open(path, "w", encoding="utf-8", newline="\n") as f:
            f.write(php)
        subprocess.run(["scp", "-i", KEY, path, f"{HOST}:{APP}/_attach_2026_extra.php"], check=True)
        r2 = subprocess.run(["ssh", "-i", KEY, HOST, f"cd {APP} && php82 _attach_2026_extra.php"], capture_output=True, text=True)
        print(r2.stdout)
        if r2.returncode != 0:
            print(r2.stderr)
            sys.exit(1)

    subprocess.run(["ssh", "-i", KEY, HOST, f"cd {APP} && php82 artisan view:clear"], check=True)
    print("ALL DONE", flush=True)


if __name__ == "__main__":
    main()
