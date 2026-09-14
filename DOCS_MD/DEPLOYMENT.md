# Deployment Form AM Site Pulogadung

Dokumen ini adalah checklist deployment aplikasi Form AM terkini ke server baru. Arsitektur resmi menggunakan PHP 8.2+, Apache, dan PostgreSQL. Jangan gunakan skema MySQL lama untuk fresh install.

## Cakupan Sistem

- 21 modul mesin: SIG, JOYEA, Ilapak 1-2, Ilapak 3-12, Unifill B, Chimei, Temach, Check Weigher, Conveyor SIG, Jihcheng, Jinsung 1-4, Jinsung 5, Best Pack, Cosmec, FBD Jaw Chuan, FBD Glatt, Supermixer, Granulator, Storage Tank Silverson, Storage Tank Tetrapak, dan Mixing Tank.
- Lima role: `1=Administrator`, `2=Manager`, `3=Supervisor`, `4=Staff`, dan `5=Operator`.
- Fresh install memakai `database/postgres/01_schema.sql` lalu `database/postgres/02_seed.sql`.
- `02_seed.sql` hanya membuat satu akun initial setup, yaitu `superadmin`. Akun Manager, Supervisor, Staff, dan Operator dibuat melalui registrasi atau menu Users.

## 1. Prasyarat Server

- Apache 2.4 dengan `mod_rewrite` dan `mod_headers`.
- PHP 8.2 atau lebih baru dengan `pdo_pgsql`, `pgsql`, `mbstring`, `dom`, `gd`, `fileinfo`, `openssl`, dan `zip`.
- PostgreSQL 17 atau versi kompatibel yang sudah diuji tim.
- Composer 2.
- Node.js/npm hanya diperlukan untuk menjalankan Playwright E2E; tidak ada proses build frontend untuk runtime production.
- HTTPS wajib digunakan saat go-live agar cookie sesi memakai flag `Secure`.

## 2. Konfigurasi Environment

Salin `.env.example` menjadi `.env`, kemudian isi kredensial khusus server. `.env` tidak boleh masuk Git atau artefak publik.

```dotenv
DB_HOST=127.0.0.1
DB_USERNAME=form_am_app
DB_PASSWORD=<password-kuat-dari-secret-manager>
DB_NAME=form_am_plg
DB_TYPE=pgsql
DB_PORT=5432
DB_CHARSET=utf8
DEVELOPMENT_MODE=false
```

Gunakan SMTP perusahaan jika reset password melalui email diaktifkan. Jangan menyalin `.env` development/Ethereal ke production. Verifikasi kembali `DEVELOPMENT_MODE=false` setelah Apache direstart.

## 3. Fresh Install PostgreSQL

Buat role/database dengan user aplikasi sebagai owner. Ownership diperlukan karena fitur Master Data Part membuat kolom part baru secara transaksional menggunakan `ALTER TABLE`.

```bash
sudo -u postgres createuser --pwprompt form_am_app
sudo -u postgres createdb --owner=form_am_app --encoding=UTF8 form_am_plg
```

Import harus berhenti pada error pertama. Jangan membuka aplikasi ke jaringan sebelum password awal Super Admin diganti.

```bash
export PGPASSWORD='<password-form_am_app>'

psql -v ON_ERROR_STOP=1 -h <host> -U <user> -d <dbname> \
  -f database/postgres/01_schema.sql
psql -v ON_ERROR_STOP=1 -h <host> -U <user> -d <dbname> \
  -f database/postgres/02_seed.sql

unset PGPASSWORD
```

Contoh parameter: `<user>=form_am_app` dan `<dbname>=form_am_plg`. Untuk menghindari password tersimpan di shell history, gunakan prompt `psql` atau `.pgpass` berizin `0600`.

Verifikasi objek utama:

```bash
psql -h <host> -U <user> -d <dbname> -c "SELECT count(*) FROM information_schema.tables WHERE table_schema='public' AND table_name LIKE 'tb_mesin_%';"
psql -h <host> -U <user> -d <dbname> -c "SELECT role_id, role_name FROM roles ORDER BY role_id;"
psql -h <host> -U <user> -d <dbname> -c "SELECT username, user_role_id, is_super_admin FROM users;"
```

Hasil yang diharapkan: 21 tabel `tb_mesin_*`, lima role, dan hanya satu user `superadmin`.

## 4. Dependency dan File Aplikasi

Untuk staging/test yang menjalankan PHPUnit dan Playwright:

```bash
composer install --no-interaction
npm ci
npx playwright install --with-deps chromium
```

Setelah seluruh pengujian lulus, buat artefak runtime production tanpa dependency development:

```bash
composer install --no-dev --no-interaction --optimize-autoloader
```

Folder `vendor/` tidak boleh berasal dari instalasi parsial. Gunakan `composer.lock` dan `package-lock.json` yang tersimpan di repository.

## 5. Permission Folder dan Apache

Contoh untuk Debian/Ubuntu dengan user Apache `www-data`:

```bash
sudo install -d -o www-data -g www-data -m 0750 \
  uploads uploads/files uploads/photos uploads/cached logs
sudo chmod 0644 uploads/.htaccess
sudo chown root:www-data uploads/.htaccess

sudo a2enmod rewrite headers
sudo apachectl configtest
sudo systemctl restart apache2
```

VirtualHost aplikasi harus mengizinkan `.htaccess`:

```apache
<Directory /var/www/form-am>
    AllowOverride All
    Require all granted
</Directory>
```

Setelah restart, pastikan file script di dalam `uploads/` ditolak HTTP 403 dan file gambar valid tetap dapat dibaca. Folder `logs/` dan subfolder `uploads/` harus writable oleh proses Apache, tetapi file aplikasi lainnya tidak perlu writable.

## 6. Gate Testing Sebelum Go-Live

```bash
vendor/bin/phpunit
npm run test:e2e
```

Lakukan smoke test terautentikasi untuk seluruh kategori mesin: registrasi, aktivasi user, submit OK/NOK, approval Supervisor, laporan harian/periode, TTD Operator/SPV, pembatalan TTD, scan QR, PDF/export, tambah/takeout Master Part, dan multi-shift.

Checklist wajib:

- [ ] Fresh import `01_schema.sql` dan `02_seed.sql` selesai tanpa error.
- [ ] Hanya akun `superadmin` yang terbentuk dari seed dan password awal sudah diganti.
- [ ] `DEVELOPMENT_MODE=false`; `.env` development tidak ikut ter-deploy.
- [ ] PHPUnit dan Playwright E2E lulus pada staging.
- [ ] `uploads/.htaccess`, `mod_rewrite`, `mod_headers`, dan `AllowOverride All` terverifikasi.
- [ ] Backup database dan folder `uploads/` berhasil direstore pada database uji.
- [ ] Audit Trail, TTD, QR integrity re-hash, dan snapshot historis diverifikasi melalui browser.
- [ ] Backup/rollback dan downtime disetujui pemilik sistem/QA.

## 7. Backup dan Restore

Script Windows membaca password dari environment, bukan dari repository:

```bat
set "DB_PASSWORD=<password-database>"
scripts\backup_form_am.bat
scripts\restore_form_am_test.bat
set "DB_PASSWORD="
```

Backup production harus mencakup dump PostgreSQL dan folder `uploads/`. Jangan hapus sistem/database lama sebelum hasil migrasi, jumlah record, dan fungsi utama mendapat sign-off QA.
