#!/bin/bash
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
cd "$APP"
grep -a "PostTooLarge\|Upload too large\|exceeds the PHP\|The image\|images\.\*\|ValidationException\|local.ERROR" storage/logs/laravel.log | tail -20
echo "---"
grep -a -B0 -A2 "local.ERROR" storage/logs/laravel.log | tail -30
