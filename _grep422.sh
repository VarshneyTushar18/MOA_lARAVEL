#!/bin/bash
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
cd "$APP"
grep -a "local.ERROR\|ValidationException\|422" storage/logs/laravel.log 2>/dev/null | tail -15
echo "---"
tail -5 storage/logs/laravel.log 2>/dev/null
