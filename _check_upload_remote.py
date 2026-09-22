import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
lines = [
    "cd /home/sites/41b/b/ba690bc503/MOA_lARAVEL",
    "php82 -r 'echo \"upload=\".ini_get(\"upload_max_filesize\").\" post=\".ini_get(\"post_max_size\").\"\\n\";'",
    "ls -la public/storage",
    "ls -la /home/sites/41b/b/ba690bc503/public_html/storage",
    "df -h /home/sites/41b/b/ba690bc503",
    "grep -a 'local.ERROR' storage/logs/laravel.log | tail -5",
    "grep -a 'PostTooLarge\\|ValidationException\\|Permission denied\\|failed to open stream' storage/logs/laravel.log | tail -10",
]
cmd = "\n".join(lines) + "\n"
r = subprocess.run(
    ["ssh", "-i", key, "-o", "StrictHostKeyChecking=accept-new", host, "bash -s"],
    input=cmd,
    capture_output=True,
    text=True,
    timeout=120,
)
print(r.stdout)
print(r.stderr)
