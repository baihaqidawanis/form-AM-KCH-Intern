# 🚀 Deployment ke Production

> **Status saat ini (14 Agustus 2026): BELUM di-deploy.** Semua kerjaan sejak migrasi Postgres (RBAC 4-role, `BaseMachineController`, 16 mesin baru, dst) baru ada di **Postgres lokal** di komputer development. Server production (`10.167.170.71`) masih di kondisi **sebelum migrasi** — MySQL/MariaDB, kemungkinan besar masih skema lama (13 role, mungkin masih ada sisa struktur mesin lama dari sebelum restrukturisasi kategori). Dokumen ini rencana buat nyambungin dua dunia itu.

## Kenapa Ini BUKAN "Tinggal Upload File"

Kalau cuma nyalin kode PHP ke server production terus jalanin, **aplikasinya bakal langsung rusak total**, karena:

1. **Skema database beda total.** Kode sekarang expect skema Postgres yang sudah direstruktur (RBAC 4-role, `BaseMachineController`, kolom lowercase konsisten). Production masih MySQL skema lama.
2. **Data production itu REAL**, bukan data dummy. Puluhan mesin, histori submission operator sepanjang waktu app ini jalan — gak boleh hilang.
3. **Akun user production itu REAL orang**, dengan role lama — perlu dipetakan ulang ke 4 role baru (Administrator/Manager/Supervisor/Staff-Operator), ini keputusan bisnis, bukan keputusan teknis.
4. **Server komputasi beda** — production kemungkinan belum punya PHP 8.2/`pdo_pgsql` extension/Postgres, perlu setup infra dulu.

## Keputusan yang Harus Diambil Tim/Pemilik Sistem Dulu

1. **Production ikut pindah ke Postgres, atau tetap MySQL?**
   **Rekomendasi: pindah ke Postgres.** Semua kerjaan yang sudah ditest berbulan-bulan ini pakai Postgres. Kalau production tetap MySQL, semua fix Postgres-specific jadi gak relevan, TAPI kode sekarang **konsisten lowercase** (gak ada capitalized column lagi seperti skema MySQL lama) — jadi walaupun tetap MySQL, skema tabelnya **tetap harus di-rename ulang**. Baik pindah Postgres maupun tetap MySQL, skema tetap harus dimigrasi; kalau sudah harus migrasi skema, sekalian pindah ke Postgres jauh lebih masuk akal (lebih modern, sudah full-tested).
2. **Mesin lama yang bukan bagian dari 17 mesin sekarang** (kalau ada di data production lama) — datanya mau diapakan? Kalau memang bukan mesin pabrik ini, kemungkinan besar gak perlu dimigrasi ke sistem baru (arsip terpisah, gak muncul lagi di app). Perlu konfirmasi eksplisit sebelum data itu "ditinggal".
3. **Mapping role lama → 4 role baru** — siapa masuk role apa. Keputusan bisnis, bukan hal yang bisa ditebak dari data.
4. **Jendela downtime** — migrasi data + testing butuh waktu app gak bisa dipakai operator. Kapan window yang paling gak mengganggu (shift kosong/weekend)?

## Rencana Kerja (Kalau Keputusan #1 = Pindah ke Postgres)

### Fase 0 — Persiapan (bisa dikerjakan sebelum window downtime)
- [ ] **Backup penuh** database production MySQL saat ini (dump lengkap + snapshot file server kalau bisa) — sebelum menyentuh apapun.
- [ ] Setup PostgreSQL 17 di server production (atau server baru khusus DB) — install, buat user/db, `pdo_pgsql` extension di PHP server.
- [ ] Setup PHP 8.2 + extension yang dibutuhkan (`pdo_pgsql`, `pgsql`, `zip` — Excel export butuh ini, lihat `TECHNICAL_OVERVIEW.md`), kalau versi PHP production lebih lama.
- [ ] **Aktifkan OPcache** di `php.ini` server production (`zend_extension=opcache` + `opcache.enable=1`) — zero-cost, langsung kerasa dampaknya ke performa. Cek `php -m | grep -i opcache` buat konfirmasi aktif.
- [ ] **Composer & npm install di server production** — cek dulu server itu bisa akses `packagist.org`/`github.com`/`registry.npmjs.org`. Kalau server production gak ada akses internet sama sekali, `vendor/`+`node_modules/` harus ditransfer manual dari komputer yang bisa akses.
- [ ] Siapkan `.env` production (`DB_HOST`/`DB_USERNAME`/`DB_PASSWORD`/`DB_NAME`/`DB_TYPE=pgsql`/`DB_PORT=5432`, **`DEVELOPMENT_MODE=false`** — WAJIB, jangan sampai kelupaan, ini pernah kejadian dan bocorin stack trace + path server ke request yang belum login sekalipun).

### Fase 1 — Migrasi Skema & Data
- [ ] Generate skema Postgres dari skema production yang sebenarnya (bukan cuma dari lokal — production mungkin punya kolom/tabel yang lokal sudah gak punya) + seluruh `database/migrations/*.sql`.
- [ ] **Data SIG** (kemungkinan satu-satunya mesin yang punya histori REAL dari production lama, karena 16 mesin lain baru dibuat) — export dari MySQL production, convert ke skema Postgres baru (field lowercase, kolom `value_tekanan_angin` dst harus ada).
- [ ] **16 mesin lainnya** — TIDAK ADA data lama untuk dimigrasi. Mulai dari 0 record, itu normal.
- [ ] **Data user** — export akun production, mapping ke 4 role baru sesuai keputusan tim, import ke skema `users` baru.
- [ ] **Data master** (`tag`, `korelasi`, `klasifikasi`, `kategori`, `mesin`) — cek isi production, sesuaikan.

### Fase 2 — Deploy Kode
- [ ] Deploy kode dari repo (branch/commit yang sudah final & ditest) ke server production.
- [ ] Copy `.env` production yang sudah disiapkan (Fase 0), **JANGAN** pakai `.env` development.
- [ ] Restart Apache/PHP-FPM di server production biar `.env` kebaca fresh.

### Fase 3 — Testing di Production (SEBELUM operator mulai pakai)
- [ ] Jalankan `vendor/bin/phpunit --testdox` di server production (arahkan `APP_BASE_URL` ke domain production lewat `phpunit.xml`).
- [ ] Jalankan `npm run test:e2e` (session-timeout & draft auto-save) — pastikan Chromium sudah ter-install (`npx playwright install chromium`).
- [ ] Jalankan `TESTING.md` bagian manual checklist — login 4 role real (bukan dummy), submit form per kategori mesin, approval, export.
- [ ] Cross-check jumlah record SIG yang termigrasi = jumlah record SIG di MySQL lama (sanity check tidak ada data hilang).
- [ ] Cek `DEVELOPMENT_MODE=false` SEKALI LAGI sebelum membuka akses ke operator.

### Fase 4 — Go-Live
- [ ] Umumkan ke operator: sistem baru, akun lama otomatis terpakai (username sama), kalau ada masalah lapor ke siapa.
- [ ] Monitor error log (`error.log`, atau Audit Trail di app) lebih ketat beberapa hari pertama.
- [ ] **Simpan backup MySQL lama minimal beberapa bulan** — jangan langsung dihapus walau sudah go-live, untuk jaga-jaga kalau ternyata ada data yang terlewat saat migrasi.

## Rollback Plan

Backup MySQL lama (Fase 0) tetap disimpan — worst case bisa kembali ke sistem lama sementara sambil investigasi masalahnya. Karena `DB_TYPE`/kredensial semua di `.env` (bukan hardcode), secara teori bisa switch balik cukup ganti `.env` + restart web server — **TAPI** ini cuma valid kalau skema/data di sisi MySQL lama masih terpelihara utuh (jangan di-drop saat migrasi, minimal sampai beberapa minggu pasca go-live terbukti stabil).

## Di Luar Scope Dokumen Ini

- Jadwal window downtime — keputusan operasional, koordinasi dengan pihak pabrik.
- Approval/sign-off perubahan sistem produksi (biasanya butuh proses change-management internal untuk sistem GMP).
- Training ulang operator kalau ada perubahan UI/UX signifikan dari sistem lama (kemungkinan banyak, karena 16 dari 17 mesin baru sama sekali).
