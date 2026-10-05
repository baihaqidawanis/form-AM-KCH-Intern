@echo off
setlocal
"C:\xampp\php\php.exe" "%~dp0process_rtwt_sync_outbox.php"
exit /b %errorlevel%
