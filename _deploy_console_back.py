import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
app = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"

uploads = [
    ("resources/views/layout/console.blade.php", "console.blade.php"),
    ("resources/views/pages_console/sections/list.blade.php", "sections_list.blade.php"),
    ("resources/views/projects/edit.blade.php", "projects_edit.blade.php"),
    ("resources/views/projects/add.blade.php", "projects_add.blade.php"),
    ("resources/views/projects/image.blade.php", "projects_image.blade.php"),
    ("resources/views/users/edit.blade.php", "users_edit.blade.php"),
    ("resources/views/users/add.blade.php", "users_add.blade.php"),
    ("resources/views/types/edit.blade.php", "types_edit.blade.php"),
    ("resources/views/types/add.blade.php", "types_add.blade.php"),
]

copies = [
    ("console.blade.php", "resources/views/layout/console.blade.php"),
    ("sections_list.blade.php", "resources/views/pages_console/sections/list.blade.php"),
    ("projects_edit.blade.php", "resources/views/projects/edit.blade.php"),
    ("projects_add.blade.php", "resources/views/projects/add.blade.php"),
    ("projects_image.blade.php", "resources/views/projects/image.blade.php"),
    ("users_edit.blade.php", "resources/views/users/edit.blade.php"),
    ("users_add.blade.php", "resources/views/users/add.blade.php"),
    ("types_edit.blade.php", "resources/views/types/edit.blade.php"),
    ("types_add.blade.php", "resources/views/types/add.blade.php"),
]

script_lines = ["#!/bin/bash", "set -e", f'APP="{app}"', f'UP="{remote_up}"']
for remote_name, dest in copies:
    script_lines.append(f'mkdir -p "$APP/$(dirname "{dest}")"')
    script_lines.append(f'cp "$UP/{remote_name}" "$APP/{dest}"')
script_lines += [
    'cd "$APP"',
    'php82 artisan view:clear',
    'php82 -r "if (function_exists(\'opcache_reset\')) opcache_reset();"',
    'echo DONE',
]

script_path = os.path.join(base, "_deploy_console_back.sh")
with open(script_path, "w", encoding="utf-8", newline="\n") as f:
    f.write("\n".join(script_lines) + "\n")

for local_rel, remote_name in uploads:
    local = os.path.join(base, local_rel.replace("/", os.sep))
    r = subprocess.run(
        ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", local, f"{host}:{remote_up}/{remote_name}"],
        capture_output=True,
        text=True,
        timeout=180,
    )
    print("scp", remote_name, r.returncode, r.stderr.strip() or "ok")
    if r.returncode != 0:
        raise SystemExit(1)

subprocess.run(["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{host}:{remote_up}/deploy_console_back.sh"], check=True)
r2 = subprocess.run(["ssh", "-i", key, host, f"bash {remote_up}/deploy_console_back.sh"], capture_output=True, text=True, timeout=180)
print(r2.stdout)
print(r2.stderr)
