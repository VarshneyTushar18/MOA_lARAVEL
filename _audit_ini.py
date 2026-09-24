import subprocess
import os

script = r"""#!/bin/bash
cat /home/sites/41b/b/ba690bc503/public_html/.user.ini
"""
base = os.path.dirname(__file__)
path = os.path.join(base, "_audit_ini.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)
key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
subprocess.run(["scp", "-i", key, path, f"{host}:/home/sites/41b/b/ba690bc503/moa_deploy_upload/audit_ini.sh"], check=True)
r = subprocess.run(["ssh", "-i", key, host, "bash /home/sites/41b/b/ba690bc503/moa_deploy_upload/audit_ini.sh"], capture_output=True, text=True, timeout=60)
print(r.stdout)
