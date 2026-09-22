#!/bin/bash
set -e
PUB="/home/sites/41b/b/ba690bc503/public_html"
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
mkdir -p "$PUB/assets/images" "$APP/public/assets/images"
cp "$UP/ministry-ayush-logo.png" "$PUB/assets/images/ministry-ayush-logo.png"
cp "$UP/ministry-ayush-logo.png" "$APP/public/assets/images/ministry-ayush-logo.png"
cp "$UP/Main-logo.png" "$PUB/assets/images/Main-logo.png"
cp "$UP/Main-logo.png" "$APP/public/assets/images/Main-logo.png"
echo DONE
