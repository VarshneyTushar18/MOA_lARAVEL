#!/bin/bash
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
cd "$APP"
echo "=== marqueeValidationRules on server ==="
grep -n "marqueeValidationRules\|marquee_links\.\*\.url\|section_key !== 'home_marquee'" app/Http/Controllers/PageSectionsController.php | head -20
echo "=== edit blade marquee block ==="
grep -n "section_form_marquee\|marquee_links\|home_marquee" resources/views/pages_console/sections/edit.blade.php | head -20
echo "=== setBlockInputsEnabled ==="
grep -n "setBlockInputsEnabled" resources/views/pages_console/sections/edit.blade.php
