# 🧪 Testing — Form AM

> Dua lapis: **otomatis** (PHPUnit + Playwright, `tests/`) buat regresi cepat & repeatable, dan **manual** (checklist di bawah) buat skenario yang gak worth/gak bisa diotomasi. Jalankan otomatis dulu tiap kali ada perubahan kode; jalankan manual sebelum deploy production atau setelah perubahan besar.
>
> **Cuma mau jalanin test?** Langsung ke [`tests/README.md`](../tests/README.md) (tutorial singkat) + [`tests/requirements.txt`](../tests/requirements.txt) (daftar software yang dibutuhin). Dokumen ini buat yang mau paham desain/nambah test.

## Kenapa Integration Test Level HTTP, Bukan Unit Test PHPUnit Biasa

Aplikasi ini PHP Native (PHPRad-generated), bukan framework modern — controller `new`-in `PDODb`/session/dependency lain langsung di `__construct()`, gak ada dependency injection. Konsekuensinya: gak mungkin `new ChimeiController()` lalu test method-nya satu-satu secara terisolasi. Test di sini jalan di **level HTTP**: login beneran, ambil CSRF token dari halaman, submit form, cek response & efek sampingnya — persis pola manual via `curl` yang jadi kebiasaan sepanjang project ini, cuma sekarang otomatis & bisa diulang kapan saja.

## Akun Dummy Testing (Lokal Saja — JANGAN Dipakai di Production)

| Username | Password | Role | Akses |
|---|---|---|---|
| `superadmin` | `Admin@123` | Administrator | Semua fitur, semua menu (username sengaja bukan format NIK — pengecualian khusus) |
| `MANAGE01` | `Test@1234` | Manager | Home, AM (view + approve, tidak bisa isi form baru), Panduan |
| `SUPERV01` | `Test@1234` | Supervisor | Home, AM (full: view/isi/edit/delete), Users, Approval, Panduan |
| `STAFOP01` | `Test@1234` | Staff/Operator | Home, AM (view + isi form), Panduan — paling terbatas |

`tests/Feature/RegistrationTest.php` & `LockoutTest.php` bikin akun **throwaway sendiri** tiap run (username acak) — gak pernah menyentuh 4 akun di atas.

---

## Bagian A — Otomatis

### Prasyarat

1. Apache nyala (`/c/xampp/apache/bin/httpd.exe` kalau belum jalan — kalau proses ke-reap gara-gara tool sandbox, start ulang pakai `nohup ./httpd.exe & disown` bukan `Start-Process` biasa).
2. PostgreSQL nyala (service `postgresql-x64-17`, atau `pg_ctl start` manual kalau service-nya gak bisa di-start).
3. 4 akun dummy di atas masih ada & gak lagi `Blocked` — kalau ke-lock gara-gara testing lockout manual, unlock dulu lewat `users` sebelum jalanin automated test.

### Install & Jalankan

```bash
composer install                    # sekali saja, install PHPUnit + Guzzle (dev)
vendor/bin/phpunit --testdox        # jalankan semua test PHPUnit
vendor/bin/phpunit tests/Feature/RbacTest.php   # 1 file saja

npm install                         # sekali saja, install Playwright (dev)
npx playwright install chromium     # sekali saja, download browser binary
npm run test:e2e                    # jalankan test Playwright (session-timeout & draft auto-save)
```

> **Kalau `npm install`/`composer install` gagal network** (`registry.npmjs.org`/`codeload.github.com` timeout) — jaringan kantor kadang flap, coba ganti jaringan atau retry. Kalau `npm install` gagal spesifik tapi `github.com` sudah bisa diakses, coba `--registry https://registry.npmmirror.com` (mirror publik).

### Struktur

```
tests/
├── Support/
│   ├── ApiClient.php     — wrapper Guzzle: login, ambil CSRF token, GET/POST/DELETE
│   └── FormScraper.php   — parse HTML add.php untuk daftar field part & mesin,
│                            biar test TIDAK hardcode field per modul
├── Feature/                          (PHPUnit, level HTTP)
│   ├── AuthTest.php          — login 4 role, login gagal
│   ├── SmokeTest.php         — list2+add semua 17 modul + halaman infra, cek 200 & nol error marker
│   ├── MachineCrudTest.php   — full lifecycle (submit→view→edit_data→PDF→delete)
│   │                            untuk Chimei (single-mesin), Illapak 1-2 (dropdown-mesin), SIG (extraFields)
│   ├── RbacTest.php          — Operator cuma edit_data punya sendiri, Manager gak bisa add tapi bisa delete
│   ├── XssEscapingTest.php   — teks kendala harus ke-escape, bukan dieksekusi
│   ├── ExportFormatsTest.php — export CSV/Excel gak crash walau list kosong
│   ├── ApprovalFlowTest.php  — approve/reject manual record NOK
│   ├── AuditTrailTest.php    — add ke-log, view TIDAK ke-log
│   ├── RegistrationTest.php  — registrasi akun baru, status Pending
│   └── LockoutTest.php       — lockout 3x salah password, pakai akun throwaway
└── e2e/                               (Playwright, browser beneran)
    ├── session-timeout.spec.js   — idle warning + auto-logout + draft auto-save/restore
    └── run-with-short-timeout.js — wrapper: pendekin SESSION_TIMEOUT_SECONDS lewat .env
                                     sementara, SELALU dibalikin lagi di finally
```

### Kenapa Tidak Ada Test CRUD Penuh untuk Semua 17 Modul

`MachineCrudTest` cuma mencakup 3 modul representatif (Chimei/Illapak 1-2/SIG), masing-masing mewakili 1 pola arsitektur berbeda (single-mesin, multi-mesin dropdown, extraFields). Karena semua 17 modul extend `BaseMachineController` yang sama, bug di logic CRUD akan ketahuan dari 3 modul ini juga. `SmokeTest` tetap loop ke semua 17 modul untuk menutup risiko config per-modul yang salah (`$parts`/`$machineKey` typo, dst).

### Catatan Teknis Session-Timeout & Lockout (buat yang mau extend test-nya)

- **Lockout** aman diotomasi karena pakai akun throwaway sendiri, bukan salah satu dari 4 akun dummy utama.
- **Session timeout**: `SESSION_TIMEOUT_SECONDS` (30 menit) dibikin overridable lewat `.env` khusus testing (`config.php`, fallback tetap 30 menit). `WARNING_MS` (modal peringatan, 5 menit sebelum timeout) itu **hardcode di JS**, tidak proporsional ke override — kalau di-override pendek (10 detik), modal peringatan nongol praktis instan dan terus reopen tiap direset, jadi interaksi form saat modal menutupi layar harus lewat `page.evaluate()` (manipulasi DOM + dispatch event langsung), bukan klik/fill normal Playwright.
- **`baseURL` Playwright wajib trailing slash**, path navigasi (`page.goto(...)`) **jangan pakai leading slash** — app punya subpath (`/form-am`), leading slash membuat URL resolve balik ke root domain (standar WHATWG URL resolution, bukan bug Playwright).

### Menjaga Test Tetap "Bersih"

Tiap test yang bikin data **selalu menghapus lagi apa yang dia buat** lewat `tearDown()`/`finally` — kalau test gagal di tengah pun, cleanup tetap jalan. Kalau ada test yang terputus paksa dan meninggalkan sampah data test (biasanya teks kendala jelas ada "Test PHPUnit"/payload XSS, atau username `PU*`/`LK*`), cari & hapus manual — tidak berbahaya, cuma bikin DB tidak rapi.

---

## Bagian B — Manual (Checklist)

> Estimasi waktu jalankan full checklist: ~30-40 menit (sudah dipangkas karena banyak item dari checklist awal sekarang otomatis — lihat tanda ⚙️). Centang tiap item, catat tanggal + siapa yang tes di bagian bawah.

### 1. Login & Registrasi

- [ ] ⚙️ *(otomatis: `AuthTest`, `RegistrationTest`)* Registrasi akun baru, login normal 4 role, login gagal
- [ ] ⚙️ *(otomatis: `LockoutTest`)* Lockout 3x salah password + tetap ditolak walau password benar setelahnya
- [ ] Admin unblock akun lewat `users` — cek manual visual UI-nya (form/dropdown-nya jelas)
- [ ] Reset password lewat email — cek link terkirim & bisa reset (butuh SMTP beneran, gak bisa full-otomatis)

### 2. Session & Idle Timeout (⚙️ sebagian besar otomatis via `npm run test:e2e`, tapi tetap cek manual sesekali di device produksi asli)

- [ ] Buka aplikasi di **tablet/device produksi asli** (bukan cuma browser desktop), biarkan idle ~28 menit — modal peringatan muncul
- [ ] Cek console browser (F12) — nol JS error
- [ ] Draft auto-save & restore — sudah otomatis via Playwright, tapi worth dicek sesekali secara visual biar yakin UX-nya enak dipakai beneran

### 3. RBAC per Role (⚙️ sebagian besar otomatis via `RbacTest`)

- [ ] Tombol yang bakal ke-403 **gak kelihatan sama sekali** di UI (bukan cuma ditolak saat diklik) — cek visual `list2` dan `view` tiap role
- [ ] Menu sidebar ter-filter sesuai role (Staff/Operator tidak lihat menu Users/Audit Trail, dst)

### 4. CRUD Form AM (⚙️ sebagian besar otomatis via `MachineCrudTest`, `ApprovalFlowTest`, `XssEscapingTest`)

- [ ] Ulangi minimal 1x per KATEGORI (Filling/Packaging/Compounding) secara visual, bukan cuma yang 3 modul representatif di test otomatis
- [ ] Filter dropdown mesin (khusus modul multi-mesin: Illapak 1-2, Illapak 3-12, Jinsung 1-4/5) — submit filter, cek dropdown TETAP menunjukkan pilihan aktif (tidak "reset" visual)

### 5. Export & Report (⚙️ sebagian otomatis via `ExportFormatsTest`)

- [ ] Export PDF/Word/CSV/Excel — buka filenya beneran (bukan cuma cek status HTTP), pastikan kebuka & isinya sesuai
- [ ] Cek "Printed by" muncul di footer PDF sesuai akun yang export

### 6. Audit Trail (⚙️ sebagian otomatis via `AuditTrailTest`)

- [ ] Klik UserID di Audit Trail — modal lookup user muncul, data benar, tidak ada tombol "Add New Users"
- [ ] Filter Audit Trail per modul/action/tanggal — cek hasil sesuai (visual)

### 7. Approval Page

- [ ] Buka halaman Approval — semua tab mesin (sesuai kategori) muncul tanpa error
- [ ] Cek tombol "Add New [Mesin]" TIDAK ada di halaman Approval (harus cuma ada di list2 masing-masing mesin)
- [ ] Cek tombol aksi (Approve/Edit Data/Delete) di tiap tab sesuai role yang login

### 8. Regresi Cepat (kalau waktu terbatas, minimal ini)

- [ ] `vendor/bin/phpunit --testdox` semua hijau
- [ ] `npm run test:e2e` hijau
- [ ] Home dashboard — semua card mesin tampil dengan angka yang benar
- [ ] Cek `DEVELOPMENT_MODE=false` di `.env` sebelum deploy (JANGAN sampai bocor stack trace ke user)

---

## Log Testing

| Tanggal | Siapa | Scope | Hasil | Catatan |
|---|---|---|---|---|
| | | | | |
