@echo off
setlocal
set "APP_ENV=testing"
set "DB_NAME=form_am_plg_test"
set "APP_UPLOAD_DIR=uploads-test/"
set "APP_BASE_URL=http://127.0.0.1:8099"
if not exist tests\runtime\sessions mkdir tests\runtime\sessions
php -d session.save_path=tests\runtime\sessions -S 127.0.0.1:8099 tests\server-router.php
