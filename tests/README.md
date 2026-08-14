# 🧪 Cara Jalankan Test — Form AM

> Tutorial singkat menjalankan test di folder ini. Detail lengkap (kenapa didesain begini, coverage, catatan teknis) ada di [`../DOCS_MD/TESTING.md`](../DOCS_MD/TESTING.md) — baca ini dulu kalau cuma mau **menjalankan** test, baca yang di `DOCS_MD/` kalau mau **menambah/mengubah** test.
>
> Mau lihat **daftar case & status lolos/gagal**-nya langsung (tanpa perlu run sendiri)? Lihat [`TEST_CASES.md`](./TEST_CASES.md).

## 1. Pastikan Prasyarat Ini Sudah Terpenuhi

| Yang dibutuhkan | Cara cek | Cara nyalain kalau mati |
|---|---|---|
| Apache | `curl http://localhost/form-am/` harus balikin HTTP 200 | `C:\xampp\apache\bin\httpd.exe` |
| PostgreSQL 17 | `psql -U formam -h 127.0.0.1 -d form_am_plg -c "SELECT 1;"` | Service `postgresql-x64-17`, atau `pg_ctl start` manual |
| File `.env` | Ada di root project, isi `DB_TYPE=pgsql` dkk | Copy dari `.env.example`, isi kredensial lokal |
| 4 akun dummy testing masih `Active` (bukan `Blocked`) | Login manual lewat browser | Kalau ke-lock, unblock lewat halaman `users` pakai akun `superadmin` |

Lihat `requirements.txt` di folder ini untuk daftar lengkap versi software yang dibutuhkan.

## 2. Install Dependency (Sekali Saja)

```bash
# dari root project (c:\xampp\htdocs\form-am)
composer install
npm install
npx playwright install chromium
```

> Kalau `composer install`/`npm install` gagal karena masalah jaringan (`registry.npmjs.org`/`codeload.github.com` timeout) — coba ganti jaringan lalu retry, atau untuk npm pakai mirror: `npm install --registry https://registry.npmmirror.com`.

## 3. Jalankan Test

```bash
# Semua test PHPUnit (backend/RBAC/DB — cepat, ~30-60 detik)
vendor/bin/phpunit --testdox

# 1 file test PHPUnit saja
vendor/bin/phpunit tests/Feature/RbacTest.php

# Test Playwright (browser beneran — session timeout & draft auto-save)
npm run test:e2e
```

Semua perintah dijalankan dari **root project**, bukan dari dalam folder `tests/`.

## 4. Baca Hasilnya

- **Hijau semua (`OK`)** → aman, lanjut kerja/deploy.
- **Ada yang merah** → baca pesan errornya, itu regresi nyata yang perlu diperbaiki sebelum lanjut. Jangan diabaikan/dilewati.
- Kalau ragu apakah test-nya sendiri yang salah atau kode aplikasinya yang salah: cek dulu manual lewat browser/curl skenario yang sama — test di sini murni memanggil endpoint aplikasi beneran, gak ada mock.

## 5. Setelah Selesai

Test yang bikin data (submit form, bikin akun) **otomatis membersihkan data yang dia buat sendiri**. Kalau ada test yang terputus paksa (Ctrl+C di tengah run) dan meninggalkan sampah data (teks kendala ada "Test PHPUnit", atau username `PU*`/`LK*`), boleh dihapus manual — tidak berbahaya, cuma bikin database tidak rapi.

---

*Isi folder ini: `Support/` (helper class test), `Feature/` (test PHPUnit), `e2e/` (test Playwright). Detail struktur & alasan desain ada di `../DOCS_MD/TESTING.md`.*
