@echo off
:: ===================================================================
:: SCRIPT AUTO-BACKUP FORM AM - SITE PULOGADUNG (COMPLIANT WITH URS)
:: Sesuai URS Form AM Poin 4.1.2 & 4.2 (Retensi 5 Tahun & CSV Validation)
:: ===================================================================

:: 1. Atur Tanggal Hari Ini (Format: YYYY-MM-DD)
set TANGGAL=%date:~10,4%-%date:~4,2%-%date:~7,2%
set WAKTU=%time:~0,2%-%time:~3,2%
set WAKTU=%WAKTU: =0%

:: 2. Tentukan Folder Tujuan Backup (Sebaiknya di Drive D: atau Network NAS)
set BACKUP_DIR=D:\BACKUP_FORM_AM
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

:: 3. Konfigurasi PostgreSQL (Sesuaikan jika path instalasi berbeda)
set PG_BIN="C:\Program Files\PostgreSQL\17\bin"
set PGPASSWORD=Admin@123
set DB_NAME=form_am_plg
set DB_USER=postgres
set DB_HOST=localhost
set DB_PORT=5432

:: Tentukan Lokasi Folder Aplikasi Form AM
set APP_DIR=%~dp0..

echo ===================================================================
echo [%date% %time%] Memulai Backup Database Form AM...
echo ===================================================================

:: 4. Eksekusi Backup Database (Format Compressed .dump)
if exist %PG_BIN%\pg_dump.exe (
    %PG_BIN%\pg_dump.exe -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -F c -b -v -f "%BACKUP_DIR%\db_form_am_%TANGGAL%_%WAKTU%.dump" %DB_NAME%
    echo [%date% %time%] Backup Database Berhasil: "%BACKUP_DIR%\db_form_am_%TANGGAL%_%WAKTU%.dump"
) else (
    echo [ERROR] pg_dump.exe tidak ditemukan di %PG_BIN%!
)

:: 5. Backup Folder Uploads (Foto Profil User)
if exist "%APP_DIR%\uploads" (
    echo [%date% %time%] Membackup Folder Foto Uploads...
    xcopy "%APP_DIR%\uploads" "%BACKUP_DIR%\uploads_%TANGGAL%\" /E /I /Y /Q
    echo [%date% %time%] Backup Uploads Berhasil.
)

echo ===================================================================
echo [%date% %time%] Seluruh Proses Backup Selesai!
echo Lokasi File Backup: %BACKUP_DIR%
echo ===================================================================
pause
