@echo off
cd /d "%~dp0"
echo Starting Laravel dev server with 210M upload limits (same UI as php artisan serve)...
echo Stop with Ctrl+C.
php artisan serve:large
