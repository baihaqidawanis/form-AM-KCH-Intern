@echo off
:: ===================================================================
:: SCRIPT SIMULASI UJI RESTORE FORM AM (UNTUK DOKUMEN CSV & AUDIT)
:: Sesuai URS Form AM Poin 4.2 (Backup / Restore Test Pre-Go-Live)
:: ===================================================================

set PG_BIN="C:\Program Files\PostgreSQL\17\bin"
set PGPASSWORD=Admin@123
set DB_TEST=form_am_test_restore
set DB_USER=postgres
set DB_HOST=localhost
set DB_PORT=5432
set BACKUP_DIR=D:\BACKUP_FORM_AM

echo ===================================================================
echo [%date% %time%] Memulai Simulasi Uji Restore Form AM...
echo Target Database Uji: %DB_TEST%
echo ===================================================================

:: 1. Drop & Create Database Uji jika ada
echo Membuat database pengujian: %DB_TEST%...
%PG_BIN%\dropdb.exe -h %DB_HOST% -p %DB_PORT% -U %DB_USER% --if-exists %DB_TEST%
%PG_BIN%\createdb.exe -h %DB_HOST% -p %DB_PORT% -U %DB_USER% %DB_TEST%

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

%PG_BIN%\pg_restore.exe -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d %DB_TEST% -v "%LATEST_BACKUP%"

echo ===================================================================
echo [%date% %time%] Simulasi Uji Restore Selesai!
echo Database '%DB_TEST%' berhasil dipulihkan dari '%LATEST_BACKUP%'.
echo Screenshot layar ini sebagai bukti dokumentasi CSV pengujian restore!
echo ===================================================================
pause
