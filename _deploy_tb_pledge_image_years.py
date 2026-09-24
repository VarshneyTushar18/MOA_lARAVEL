import os
import subprocess

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUBLIC_HTML = "/home/sites/41b/b/ba690bc503/public_html"

files = [
    ("database/migrations/2026_09_24_120000_add_sort_order_to_page_section_images_table.php",
     "2026_09_24_120000_add_sort_order_to_page_section_images_table.php"),
    ("app/Support/MediaYearResolver.php", "MediaYearResolver.php"),
    ("app/Models/PageSectionImage.php", "PageSectionImage.php"),
    ("resources/views/partials/acsm-tb-pledge-year-tabs.blade.php", "acsm-tb-pledge-year-tabs.blade.php"),
    ("resources/views/layout/frontend.blade.php", "frontend_tb_images.php"),
    ("_assign_tb_pledge_image_years.php", "assign_tb_pledge_image_years.php"),
    ("_tb_pledge_image_manifest.json", "tb_pledge_image_manifest.json"),
    ("_check_tb_pledge_year_counts.php", "check_tb_pledge_year_counts.php"),
]

script = f"""#!/bin/bash
set -e
cp {REMOTE_UP}/2026_09_24_120000_add_sort_order_to_page_section_images_table.php {APP}/database/migrations/
cp {REMOTE_UP}/MediaYearResolver.php {APP}/app/Support/MediaYearResolver.php
cp {REMOTE_UP}/PageSectionImage.php {APP}/app/Models/PageSectionImage.php
cp {REMOTE_UP}/acsm-tb-pledge-year-tabs.blade.php {APP}/resources/views/partials/acsm-tb-pledge-year-tabs.blade.php
cp {REMOTE_UP}/frontend_tb_images.php {APP}/resources/views/layout/frontend.blade.php
cp {REMOTE_UP}/assign_tb_pledge_image_years.php {APP}/_assign_tb_pledge_image_years.php
cp {REMOTE_UP}/tb_pledge_image_manifest.json {APP}/_tb_pledge_image_manifest.json
cp {REMOTE_UP}/check_tb_pledge_year_counts.php {APP}/_check_tb_pledge_year_counts.php
cd {APP}
php82 artisan migrate --force
php82 _assign_tb_pledge_image_years.php _tb_pledge_image_manifest.json
echo "--- after assign ---"
php82 _check_tb_pledge_year_counts.php
php82 artisan view:clear
php82 artisan view:cache
echo DONE
"""

base = os.path.dirname(__file__)
with open(os.path.join(base, "_deploy_tb_pledge_image_years.sh"), "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

for local_rel, remote in files:
    local = os.path.join(base, local_rel.replace("/", os.sep))
    r = subprocess.run(
        ["scp", "-i", KEY, local, f"{HOST}:{REMOTE_UP}/{remote}"],
        capture_output=True, text=True, timeout=180,
    )
    print("scp", remote, r.returncode, r.stderr.strip() or "ok")
    if r.returncode != 0:
        raise SystemExit(r.returncode)

subprocess.run(
    ["scp", "-i", KEY, os.path.join(base, "_deploy_tb_pledge_image_years.sh"), f"{HOST}:{REMOTE_UP}/deploy_tb_pledge_image_years.sh"],
    check=True,
)
r2 = subprocess.run(
    ["ssh", "-i", KEY, HOST, f"bash {REMOTE_UP}/deploy_tb_pledge_image_years.sh"],
    capture_output=True, text=True, timeout=300000,
)
print(r2.stdout)
print(r2.stderr)
if r2.returncode != 0:
    raise SystemExit(r2.returncode)
