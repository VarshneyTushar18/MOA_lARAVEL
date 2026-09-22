import urllib.request
import re

html = urllib.request.urlopen("https://phi-ltbi-aiia-in.stackstaging.com/", timeout=30).read().decode("utf-8", "replace")
print("slides", html.count('class="carousel-item'))
print("videos", html.count("banner-carousel-video"))
print("video sources", re.findall(r'<source src="([^"]+)"', html))
