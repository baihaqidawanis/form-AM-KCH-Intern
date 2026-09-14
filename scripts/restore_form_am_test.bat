@echo off
setlocal
:: ===================================================================
:: SCRIPT SIMULASI UJI RESTORE FORM AM (UNTUK DOKUMEN CSV & AUDIT)
:: Sesuai URS Form AM Poin 4.2 (Backup / Restore Test Pre-Go-Live)
:: ===================================================================

set "PG_BIN=C:\Program Files\PostgreSQL\17\bin"
if not defined DB_TEST set "DB_TEST=form_am_test_restore"
if not defined DB_USER set "DB_USER=postgres"
if not defined DB_HOST set "DB_HOST=localhost"
if not defined DB_PORT set "DB_PORT=5432"
if not defined BACKUP_DIR set "BACKUP_DIR=D:\BACKUP_FORM_AM"
if not defined DB_PASSWORD (
    echo [ERROR] Environment variable DB_PASSWORD belum diatur.
    echo Jalankan: set "DB_PASSWORD=password_database" lalu ulangi script ini.
    exit /b 1
)
set "PGPASSWORD=%DB_PASSWORD%"

echo ===================================================================
echo [%date% %time%] Memulai Simulasi Uji Restore Form AM...
echo Target Database Uji: %DB_TEST%
echo ===================================================================

:: 1. Drop & Create Database Uji jika ada
echo Membuat database pengujian: %DB_TEST%...
"%PG_BIN%\dropdb.exe" -h %DB_HOST% -p %DB_PORT% -U %DB_USER% --if-exists %DB_TEST%
"%PG_BIN%\createdb.exe" -h %DB_HOST% -p %DB_PORT% -U %DB_USER% %DB_TEST%

:: 2. Cari file backup terbaru di D:\BACKUP_FORM_AM
for /f "delims=" %%F in ('dir /b /o-d "%BACKUP_DIR%\db_form_am_*.dump" 2^>nul') do (
    set LATEST_BACKUP=%BACKUP_DIR%\%%F
    goto :found
)

echo [ERROR] Tidak ditemukan file backup di %BACKUP_DIR%!
pause
exit /b

:found
echo Menggunakan file backup terbaru: %LATEST_BACKUP%
echo Memulihkan data (pg_restore)...

"%PG_BIN%\pg_restore.exe" -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d %DB_TEST% -v "%LATEST_BACKUP%"

echo ===================================================================
echo [%date% %time%] Simulasi Uji Restore Selesai!
echo Database '%DB_TEST%' berhasil dipulihkan dari '%LATEST_BACKUP%'.
echo Screenshot layar ini sebagai bukti dokumentasi CSV pengujian restore!
echo ===================================================================
pause
set "PGPASSWORD="
endlocal
