#!/bin/bash
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
cd "$APP"
echo "=== log size ==="
wc -l storage/logs/laravel.log 2>/dev/null || echo no log
echo "=== last 3 ERROR blocks ==="
grep -a -n "local.ERROR" storage/logs/laravel.log | tail -3
echo "=== tail context ==="
tail -80 storage/logs/laravel.log 2>/dev/null
