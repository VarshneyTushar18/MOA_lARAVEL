#!/bin/bash
PUB="/home/sites/41b/b/ba690bc503/public_html"
echo "=== files ==="
ls -la "$PUB/assets/images/ministry-ayush-logo.png" "$PUB/assets/images/aiia-header-brand.png" 2>&1
echo "=== file type ==="
file "$PUB/assets/images/aiia-header-brand.png"
echo "=== ministry HTTP ==="
curl -sI "https://phi-ltbi-aiia-in.stackstaging.com/assets/images/ministry-ayush-logo.png" | head -3
echo "=== brand HTTP ==="
curl -sI "https://phi-ltbi-aiia-in.stackstaging.com/assets/images/aiia-header-brand.png?v=20260922brandfix" | head -3
