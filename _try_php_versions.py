import subprocess
import os
import urllib.request

versions = ["php81", "php82", "php83", "php80", "php74"]
key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
home = "/home/sites/41b/b/ba690bc503"

for v in versions:
    ht = f"""#+PHPVersion
#={v}
AddHandler x-httpd-{v} .php
#-PHPVersion
"""
    local = os.path.join(os.path.dirname(__file__), "_ht.txt")
    with open(local, "w", encoding="utf-8", newline="\n") as f:
        f.write(ht)
    subprocess.run(["scp", "-i", key, local, f"{host}:{home}/.htaccess"], check=True)
    try:
        req = urllib.request.urlopen("https://phi-ltbi-aiia.in/ok.php", timeout=15)
        body = req.read().decode()[:80]
        print(v, req.status, body)
        if req.status == 200 and "ok-php" in body:
            print("WORKING VERSION:", v)
            break
    except Exception as e:
        print(v, "FAIL", e)
