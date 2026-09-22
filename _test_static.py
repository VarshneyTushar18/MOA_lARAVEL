import subprocess
import os
import urllib.request

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
home = "/home/sites/41b/b/ba690bc503"

# restore original WP-style php74 marker
ht74 = """#+PHPVersion
#=php74
AddHandler x-httpd-php74 .php
#-PHPVersion
"""
with open("_ht74.txt", "w", newline="\n") as f:
    f.write(ht74)
subprocess.run(["scp", "-i", key, "_ht74.txt", f"{host}:{home}/.htaccess"], check=True)

# remove public_html htaccess temporarily
subprocess.run(["ssh", "-i", key, host, f"mv {home}/public_html/.htaccess {home}/public_html/.htaccess.bak 2>/dev/null; echo static-ok > {home}/public_html/static.txt"], check=True)

try:
    r = urllib.request.urlopen("https://phi-ltbi-aiia.in/static.txt", timeout=20)
    print("static", r.status, r.read().decode())
except Exception as e:
    print("static fail", e)

try:
    r = urllib.request.urlopen("https://phi-ltbi-aiia.in/ok.php", timeout=20)
    print("php", r.status, r.read().decode())
except Exception as e:
    print("php fail", e)
