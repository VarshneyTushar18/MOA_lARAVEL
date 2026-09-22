import urllib.request
import re

html = urllib.request.urlopen("https://phi-ltbi-aiia-in.stackstaging.com/", timeout=30).read().decode("utf-8", "replace")
# extract carousel inner chunk
start = html.find('carousel-inner')
chunk = html[start:start+15000]
for i, m in enumerate(re.finditer(r'carousel-item[^>]*active|banner-carousel-video|<img src=\"([^\"]+)\"', chunk)):
    if i > 25: break
    print(m.group(0)[:120])
