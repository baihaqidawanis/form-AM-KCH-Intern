# ✅ Form AM — Evaluation Log

> Dokumen ini isinya **cuma yang udah dikerjain** — checklist eksekusi + evaluasi/detail tiap fix. Rencana yang **belum** dikerjain ada di [FINAL_IMPROVEMENT.md](./FINAL_IMPROVEMENT.md). Context ringkas project ada di [CLAUDE.md](../CLAUDE.md).
>
> Setiap item bernomor (`#1`, `#8`, dst) merujuk ke nomor temuan yang sama di `FINAL_IMPROVEMENT.md` / [ANALYSIS.md](./ANALYSIS.md) (arsip).

---

## 📊 Ringkasan Cepat

| # | Temuan | Status | Detail |
|---|--------|--------|--------|
| 1 | Remember Me rusak (`__tablename` gak diganti) | ✅ Selesai | [Round 1](#round-1--2026-08-07) |
| 2 | `error_reporting(0)` + dead code di `Functions.php` | ✅ Selesai | [Round 1](#round-1--2026-08-07) |
| 3 | `FILTER_SANITIZE_STRING` deprecated (PHP 8.3) | ✅ Selesai | [Round 1](#round-1--2026-08-07) |
| 5 | `GetModel()` bikin koneksi DB baru tiap panggil | ✅ Selesai | [Round 1](#round-1--2026-08-07) |
| 6 | `UserController.php` vs `UsersController.php` duplikat | ✅ Selesai | [Round 1](#round-1--2026-08-07) |
| 7 | Double semicolon (`;;`) di controller | ✅ Selesai | [Round 1](#round-1--2026-08-07) |
| 10 | `SharedController.php` 87 method duplikat | ✅ Selesai | [Round 3](#round-3--10-sharedcontroller-dedup-2026-08-07) |
| — | `password LIKE ?` ikut ke-scan di search user | ✅ Selesai | [Round 2](#round-2--priority-3-quick-wins-2026-08-07) |
| — | Kredensial DB hardcoded di `config.php` | ✅ Selesai | [Round 4](#round-4--kredensial-db-ke-env-2026-08-07) |
| 4 | Race condition di tagging (33 controller, bukan 8) | ✅ Mekanisme fix dibuktikan lewat DB lokal (reproduksi bug lama + verifikasi fix baru); browser-test per-mesin nunggu UI siap | [Round 5](#round-5--4-race-condition-di-tagging-2026-08-07) |
| — | `hash_value()` MD5 → SHA-256 | ✅ Selesai | [Round 2](#round-2--priority-3-quick-wins-2026-08-07) |
| — | Standarisasi `extensions` upload | ✅ Selesai | [Round 2](#round-2--priority-3-quick-wins-2026-08-07) |
| — | `.gitignore`, `docker-compose.yml` version, upload `pict` extensions | ✅ Selesai | [Round 1](#round-1--2026-08-07) |
| — | Bug tambahan: `Menu.php`, `BaseView.php`, `Pagination.php` | ✅ Selesai | [Bug Tambahan](#bug-tambahan-yang-ketemu-pas-testing-fe-langsung-di-browser) |

| — | 33 controller mesin "pabrik sebelah" (bukan pabrik kita) | ✅ Dihapus (kode+DB, ada backup) | [Round 6](#round-6--hapus-33-mesin-pabrik-sebelah--restrukturisasi-menu-2026-08-07) |
| — | Bug fatal `Illapak_1_2`/`Illapak_3_12` (`pageLimit` private property) | ✅ Selesai | [Koreksi Round 10](#koreksi--bug-fatal-ketemu-user-gak-ke-tangkep-smoke-test-round-10-di-atas-2026-08-07-sesi-lanjutan) |
| — | 6 gap fungsionalitas Joeya vs SIG (`edit_data`, `editfield`, search, dll) | ✅ Selesai | [Round 11](#round-11--audit-fungsionalitas-joeya-vs-sig-2026-08-07) |
| — | Dynamic property deprecated di `BaseController.php` (semua controller kena) | ✅ Selesai | [Round 11](#round-11--audit-fungsionalitas-joeya-vs-sig-2026-08-07) |

Item **belum** dikerjain (`#8`, `#9`, `#11`, dan roadmap lain): lihat [FINAL_IMPROVEMENT.md](./FINAL_IMPROVEMENT.md).

---

## 📅 Ringkasan per Tanggal

> Rekap kerjaan per hari (bukan per-round) — buat yang butuh liat "tanggal segini ngapain aja".

| Tanggal | Round | Ringkasan Kerjaan |
|---|---|---|
| 2026-08-07 | 1–11 | Bug fixes awal (remember-me, dead code, deprecated function, dll), refactor `SharedController.php` (87→3 method), kredensial DB ke `.env`, fix race condition tagging di 33 controller lama, **hapus 33 modul mesin "pabrik sebelah"** (bukan pabrik kita, dikonfirmasi user) + restrukturisasi `Menu.php` jadi 3 grup (Compounding/Filling/Packaging), bikin modul **Joeya** dari nol (5 part → disempurnakan jadi 12 part), konsolidasi **Illapak 1-12** jadi 2 modul ringkas (Illapak 1-2, Illapak 3-12), audit + fix 6 gap fungsionalitas Joeya vs SIG (`edit_data`, `editfield`, search, dll). |
| 2026-08-10 | 12–13 | Audit E2E menyeluruh 4 modul Filling (Joeya/Illapak 1-2/Illapak 3-12/Unifill B) vs SIG — ketemu bug kritis `add()` Illapak crash Error 500 tiap ada part NOK, plus 6 gap yang sama kayak Joeya sebelum di-fix (belum ada di Illapak/Unifill). **Semua di-fix hari yang sama**: bug kritis, 6 gap x 3 controller, `write_to_log()` audit log sistemik, migration SQL susulan, **plus ketemu & fix Export/Print/PDF/CSV/Excel yang ternyata gak jalan sama sekali** di Joeya+Illapak+Unifill (SIG doang yang punya). Semua diverifikasi live end-to-end via curl, bukan cuma lint. |

---

## Round 1 — 2026-08-07

Target awal: selesai sebelum jam 3 sore. Diurutkan dari yang paling cepat.

- [x] **#1** Fix `__tablename` bug di `SecureController.php` (1 baris) — `$db->getOne("__tablename")` → `$db->getOne("users")` di [SecureController.php:44](../system/SecureController.php#L44)
- [x] **#2** Hapus kode rusak `error_reporting(0)` di `Functions.php` — blok baris 520-527 dihapus dari [Functions.php](../helpers/Functions.php)
- [x] **#7** Cari-replace `;;` → `;` di semua controller — 167 occurrence dibersihkan di 44 file `app/controllers/*.php`
- [x] **.gitignore** — dibuat, exclude `vendor/`, `uploads/`, `logs/`, `.env`, `*.sql`, `node_modules/`
- [x] **docker-compose.yml** — baris `version: '3.8'` dihapus
- [x] File upload `pict` — `extensions` diisi `.jpg,.jpeg,.png,.gif,.webp` di [BaseController.php:164](../system/BaseController.php#L164)
- [x] **#6** Cek `UserController.php` vs `UsersController.php` — **temuan lebih parah dari dugaan**: `UserController.php` (singular) target tabel `user` yang **tidak ada di database** (tabel asli cuma `users`), dan extends `BaseController` biasa — **bukan `SecureController`**, artinya bisa diakses tanpa login. Tidak ada link dari menu manapun ke modul ini (dead code murni). **Dihapus**: `UserController.php` + folder view `app/views/partials/user/`. `UsersController.php` (plural, dipakai `IndexController.php` untuk login) tetap jadi satu-satunya user management yang aktif.
- [x] **#3** Ganti `FILTER_SANITIZE_STRING` → `FILTER_SANITIZE_FULL_SPECIAL_CHARS` — 24 occurrence di 7 file (`IndexController`, `Router`, `BaseView`, `BaseController`, `GUMP`, `Functions`, dan `timthumb.php` yang ternyata masih ke-load). Dipicu langsung waktu testing FE: muncul sebagai deprecated warning bertumpuk di halaman Home.
- [x] **#5** Singleton `GetModel()` di [BaseController.php:181](../system/BaseController.php#L181) — ditambah `if($this->db === null)` sebelum bikin instance `PDODb` baru. Property `$this->db` sudah default `null` dari deklarasi awal jadi gak perlu ubah apapun lagi.

> **Update sinkronisasi:** folder htdocs sudah dirapikan (gak nested lagi) dan semua fix di atas sudah di-copy ke `C:\xampp\htdocs\form-am\`.

### Bug tambahan yang ketemu pas testing FE langsung di browser

- [x] **Syntax error fatal di `Menu.php:135`** — ada huruf nyasar `array(w` (harusnya `array(`), bikin Error 500 pas render menu Home. Pre-existing bug, bukan dari fix-fix sebelumnya. Fixed.
- [x] **Dynamic property deprecated di `BaseView.php`** (`$request_uri`, `$view_args`) — class ini memang didesain nerima properti dinamis dari query string (`$this->$obj = $val`), jadi solusinya tambah attribute `#[\AllowDynamicProperties]` di deklarasi class, bukan ubah logicnya.
- [x] **Dynamic property deprecated di `Pagination.php`** (`$route`) — muncul di halaman list (ketauan pertama kali di `mf/list.php`, tapi class ini dipakai di semua 56 halaman list). Fix sama: `#[\AllowDynamicProperties]` di class `Pagination`, sekali fix nutup semua halaman list sekaligus.

Yang **sengaja dilewatin** di Round 1 (butuh waktu lebih & testing lebih hati-hati, bukan quick win):
- #4 Race condition fix (harus ditest bareng flow tagging)
- #8 TelegramNotifier helper (refactor 30+ file, butuh testing tiap notifikasi)
- #9/#10 Refactor controller duplikat (proyek besar, bukan sesi sore ini — #10 akhirnya dikerjain di Round 3)

---

## Round 2 — Priority 3 quick wins (2026-08-07)

- [x] Hapus `password LIKE ?` dari query search user — dihapus dari [UsersController.php:41](../app/controllers/UsersController.php#L41), sekaligus kurangin 1 placeholder di `$search_params` biar tetep match jumlah `?`.
- [x] `hash_value()` MD5 → SHA-256 — [Functions.php:666](../helpers/Functions.php#L666), `md5()` → `hash('sha256', ...)`. Dicek dulu 5 titik pemakaian (`Csrf.php`, `SecureController`, `IndexController`, `PasswordmanagerController`) — semua buat token sesi/reset yang regenerate tiap login, bukan data tersimpan permanen, jadi aman diganti tanpa migrasi. Efek sampingnya: sesi/cookie remember-me yang lagi aktif bakal invalid sekali (user re-login), one-time saja.
- [x] Standarisasi `extensions` upload — [BaseController.php:154](../system/BaseController.php#L154) (`summernote_img_upload`) disamain jadi `.jpg,.jpeg,.png,.gif,.webp`, konsisten sama `pict` di baris 164.

---

## Round 3 — `#10` SharedController dedup (2026-08-07)

- [x] **`SharedController.php`**: 87 method (bukan "20+" seperti perkiraan awal) disatukan jadi delegasi ke 3 helper generik:
  - `_option_list($sql, $params = null)` — dipakai 52 method dropdown (`*_option_list`)
  - `_value_exists($table, $column, $val)` — dipakai 2 method (`users_email_value_exist`, `users_username_value_exist`)
  - `_count_today($table, $dateColumn)` — dipakai 33 method (`getcount_*`)

  File turun dari **1191 baris → 744 baris** (-38%). Semua nama method public **persis sama** (dicek programatis: 87/87 match, urutan identik) — jadi `ApiController::json($action, ...)` yang manggil method ini via `call_user_func_array` gak perlu diubah sama sekali, begitu juga semua AJAX call di 50+ form view.

  **Cara ngerjain (bukan manual copy-paste):** ditulis script transformasi (regex-based) yang parse tiap method dari file asli, klasifikasi ke 3 pola, terus generate ulang bodinya sebagai 1-baris delegasi ke helper — supaya SQL string-nya persis sama (gak ada typo dari re-typing manual). Habis itu diverifikasi otomatis: semua 87 nama method match, semua SQL literal `option_list` dicocokkan byte-exact ke file asli, dan semua 33 pasangan table/date-column `getcount_*` direkonstruksi lalu dicocokkan lagi ke SQL asli — termasuk anomali yang kepencet kayak `getcount_fbd` yang kolomnya `date_create` (bukan `date_created` seperti yang lain, typo asli sengaja dipertahankan biar behavior gak berubah).

  **Belum dites di browser** — secara statis (lint + verifikasi SQL) aman, tapi karena ini nyentuh dropdown di puluhan form, **disarankan smoke-test manual**: buka beberapa form (misal `lt2_blender/add`, `sig/add`, `users/list` search), pastikan dropdown line/kategori/tag masih keisi dan search masih jalan. File asli di-backup ke `SharedController.php.bak` sebelum ditimpa — tinggal restore kalau ternyata ada yang salah.

### Temuan sampingan dari Round 3 (belum ditindaklanjuti, cuma dicatat)

- **`ApiController.php` bukan stub kosong** seperti klaim di `ANALYSIS.md`/audit awal — ada method `json($action, $arg1, $arg2)` yang proxy ke `SharedController` via `call_user_func_array`. Ini dipakai buat isi dropdown AJAX (line/kategori/tag) di form, **bukan** REST API umum buat CRUD data mesin. Klaim "REST API layer belum ada" di roadmap tetap valid — cuma alasannya bukan "file kosong", tapi "yang ada scope-nya sempit (cuma dropdown proxy)".

---

## Round 4 — Kredensial DB ke `.env` (2026-08-07)

- [x] **`config.php`**: tambah loader `.env` sederhana (parse manual, gak nambah dependency Composer baru — `composer.json` cuma punya `dompdf`) + function helper `env($key, $default)`. Semua 7 `define("DB_*", ...)` diganti dari hardcoded jadi `env("DB_*", "<default lama>")` — default lama dipertahankan persis biar **local dev tanpa `.env` sama sekali tetep jalan** (gak breaking).
- [x] **`.env`** (gitignored, isi kredensial lokal — sama persis sama default lama biar gak ada perubahan behavior) + **`.env.example`** (di-commit, template kosong buat onboarding) dibuat di root, sejajar `config.php`.
- [x] **`.dockerignore`** dibuat (belum ada sebelumnya) — exclude `.env`, `.git`, `*.sql`, `logs/*`, `uploads/*`, `DOCS_MD/`. Ini **wajib** dibarengin sama perubahan `.env`, soalnya `Dockerfile` sekarang cuma `COPY . /var/www/html/` polos — tanpa `.dockerignore`, `.env` malah ikut ke-bake ke image (lebih parah dari sebelumnya, karena sebelumnya kredensial cuma di `config.php` yang emang udah di-copy juga, tapi minimal gak ada file terpisah yang isinya "cuma" secrets).
- [x] **`docker-compose.yml`**: tambah `env_file: .env` di service `web-app`, biar container ambil kredensial dari `.env` di host pas *runtime* (bukan ke-bake ke image pas *build*). Divalidasi pakai `docker compose config` — env var ke-inject bener (`DB_HOST`, `DB_USERNAME`, dst muncul di section `environment:` hasil resolve).

**Cara verifikasi (bukan asumsi):** ditulis script test yang `require config.php` terus echo semua konstanta `DB_*`, dijalanin 3x — (1) dengan `.env` asli ada → nilai match `.env`, (2) `.env` diubah salah satu value-nya → langsung kebaca beda (bukti bukan cache/hardcode lama), (3) `.env` dipindah sementara (simulasi "gak ada `.env`") → fallback ke default lama persis. Semua 3 skenario lolos, `.env` dikembalikan ke isi semula setelah tes.

**Catatan buat production:** `.env` di server kantor (`10.167.170.71`) harus dibuat manual (isinya beda dari `.env` lokal ini — DB_HOST-nya bukan `localhost`) sebelum `docker compose up`, karena `.env` sengaja gak ikut ke-commit/ke-deploy otomatis.

---

## Round 5 — `#4` Race condition di tagging (2026-08-07)

- [x] **Scope ternyata jauh lebih besar dari dugaan awal**: dokumen lama nyebut "8 controller" (itu cuma dari 1 grep pattern yang gak lengkap). Setelah di-scan ulang, ternyata **33 controller** kena pola race condition ini — semua mesin yang punya field `kendala` dan nge-generate baris ke tabel `tag_compounding` / `tag_filling_kemas` pas ada laporan masalah.
- [x] **2 race condition berantai per controller**, dan **keduanya kena di semua 33 controller** (bukan cuma 8 — angka 8 itu salah, gara-gara pattern regex awal cuma nangkep varian `ORDER BY id DESC`, padahal 25 controller lain pakai kolom ID spesifik kayak `ORDER BY id_chimei DESC`, `ORDER BY id_k1r1 DESC`, dst. Ketauan pas verifikasi ulang setelah patch pertama, langsung di-fix di pass kedua hari yang sama):
  1. Setelah `INSERT INTO tag_compounding/tag_filling_kemas ... SELECT ...`, kode lama nyari ID baris yang barusan ke-insert pakai `SELECT id FROM tag_compounding ORDER BY id DESC LIMIT 1` — kalau 2 operator submit bersamaan, bisa kepentok ID milik mesin lain.
  2. Di **32 dari 33 controller** (yang punya notifikasi red-tag ke Telegram — `SigController` yang ke-33 fitur Telegram-nya dikomentarin/nonaktif jadi gak kehitung), ada race condition KEDUA: `SELECT id_tagging FROM {tabel_mesin} ORDER BY {id_col} DESC LIMIT 1` buat ambil balik `id_tagging` yang barusan di-set — kalau ada operator lain submit form baru di antara insert dan select ini, bisa kebawa `id_tagging` milik baris mesin yang salah.
  - **Fix kedua-duanya sekaligus**: pakai `$db->getLastInsertId()` (wrapper `PDO::lastInsertId()`) langsung setelah `INSERT INTO tag_compounding`, disimpan ke `$tagging_id`. Method ini scope-nya **per-koneksi**, bukan per-tabel, jadi walau ada request lain nge-insert di waktu bersamaan, tetep balikin ID punya insert milik koneksi yang manggil — aman dari race condition tanpa perlu locking tambahan. Query kedua (`UPDATE ... SET id_tagging = (SELECT ...)`) diganti jadi `UPDATE ... SET id_tagging = ?` pakai `$tagging_id` langsung. Query ketiga (yang re-query `id_tagging` dari tabel mesin) dihapus total — tinggal reuse `$tagging_id` yang udah ada di variable.
- [x] **Bonus fix (bukan sengaja dicari, ketemu pas verifikasi)**: `AgvController.php` dan `Mesin_sealerController.php` punya bug re-query yang salah tabel — keduanya query `SELECT id_tagging FROM mesin_sealer ...` padahal `AgvController` seharusnya dari tabel `agv`. Bug ini otomatis ke-obsolete karena query itu sendiri dihapus (diganti reuse variable), jadi ikut ke-fix tanpa perlu sentuh manual.
- [ ] **2 bug terpisah yang SENGAJA gak ikut dibenerin** (di luar scope race condition, cuma dicatat):
  - `Lt4_k1r8Controller.php` pakai `id_k1r4` di WHERE clause insert & update-nya, padahal nama tabelnya `lt4_k1r8` (harusnya `id_k1r8`). Kesalahan pre-existing yang konsisten dari INSERT sampai UPDATE — kemungkinan controllernya di-generate dari copy `Lt4_k1r4Controller.php` terus lupa di-rename semua. **Belum dibenerin** karena butuh cek dulu nama kolom asli di tabel `lt4_k1r8` (kalau kolomnya beneran `id_k1r4`, berarti bukan bug) — jangan ditebak, cek skema dulu.
  - `ChimeiController.php` baris pesan Telegram-nya pakai `$lastInsertId` (`"Last Inserted ID : $lastInsertId\n"`) — variabel ini **gak pernah didefinisikan** di file itu sama sekali (bukan `$rec_id`, bukan `$tagging_id`). Bakal muncul kosong + PHP warning "undefined variable" tiap kali form Chimei disubmit. Pre-existing, gak disentuh karena di luar scope race condition.

**Cara ngerjain:** ditulis script scan dulu (bukan asumsi jumlah file) — nemuin 33 file lewat regex `SET id_tagging = \(SELECT`, semua 33 berhasil di-parse otomatis (nama tabel mesin, tabel tag tujuan, nama kolom ID, nama kolom kendala — semuanya beda-beda per file). Baru setelah itu ditulis script patch yang generate ulang blok kode berdasarkan hasil parse, bukan template statis, biar variasi per-mesin (nama kolom ID beda-beda: `id`, `id_chimei`, `id_k1r1`, dst) otomatis ketangkep bener. Race condition kedua awalnya cuma di-patch di 8 file (regex kurang general), ketauan pas re-scan pakai pattern lebih longgar (`id_tagging FROM \w+ ORDER BY \w+ DESC LIMIT 1` — nangkep nama kolom apa aja, bukan cuma literal `id`) — 24 file sisanya di-patch di pass kedua hari yang sama.

**Verifikasi:** 33 file di-backup ke [`_backup_race_condition_2026-08-07/`](../_backup_race_condition_2026-08-07/) sebelum pass 1, dan 24 file yang kena pass 2 di-backup lagi (state setelah pass 1) ke [`_backup_race_condition_2026-08-07_pass2/`](../_backup_race_condition_2026-08-07_pass2/) sebelum pass 2. Setelah tiap pass: `php -l` lolos di semua file yang disentuh, dan **diff dibaca manual satu-satu** (bukan cuma di-summary) — dipastikan tiap file cuma berubah di blok yang dimaksud. Ditutup dengan re-scan `grep "id_tagging FROM"` ke seluruh `app/controllers/` — hasil akhirnya nol match aktif (cuma sisa 1 baris di `SigController.php` yang emang dikomentarin/nonaktif).

**Kenapa fix ini valid tanpa perlu ubah `GetModel()` lagi:** sempat khawatir race condition fix ini butuh koneksi DB yang konsisten (takutnya kepengaruh sama fix singleton `GetModel()` di Round 1), tapi dicek ulang: `$db` di tiap method (`add()`, dst) cuma di-assign sekali di awal function dan dipakai terus sampai akhir — jadi `getLastInsertId()` udah pasti manggil koneksi yang sama persis yang barusan eksekusi `INSERT`, independen dari histori fix `GetModel()` sebelumnya.

### Update: dibuktikan langsung lewat DB lokal (2026-08-07, sesi lanjutan)

**Konteks:** browser-testing ke 32 mesin lain gak bisa dilakukan user sekarang — cuma mesin **SIG** yang UI-nya udah jalan end-to-end (mesin lain rencananya bakal di-upgrade ngikutin pola SIG ke depannya, lihat blueprint `BaseMachineController` di bawah). Tapi **SIG gak kepakai buat validasi Round 5** karena SIG punya arsitektur tagging yang beda total (tabel anak `kendala_sig`, fitur Telegram-nya malah dikomentarin/nonaktif) — SIG sama sekali gak ada di 33 controller yang kena patch.

Karena itu, validasi dipindah ke **level DB langsung** (bukan lewat browser), dan ternyata local MySQL nyala + tabel `lt2_blender` ada (`new_breakdown_management_2` gak ada — itu juga alasan lain kenapa mesin selain SIG gak bisa dites full end-to-end di lokal). Dibikin skema tiruan sementara (kolom persis sama yang dipakai di query INSERT asli), dites, terus dihapus lagi.

**Skenario tes:** simulasi 2 "operator" pakai 2 koneksi PDO terpisah, di-interleave manual biar race window-nya kejamin kepicu (operator A insert ke `tag_compounding` → operator B insert duluan SEBELUM A sempat ambil ID-nya → baru A & B nyoba nyocokin `id_tagging` masing-masing):

- **Kode LAMA** (`UPDATE ... SET id_tagging = (SELECT id FROM tag_compounding ORDER BY id DESC LIMIT 1)`) — **kebukti gagal**: kedua row A dan B sama-sama ke-set `id_tagging = 2` (punya operator B), row A jadi nunjuk ke tag milik operator B. **Data corruption beneran kejadian**, bukan cuma teori.
- **Kode BARU** (`$tagging_id = $db->getLastInsertId()` dipanggil segera setelah INSERT, per-koneksi) — **kebukti benar**: operator A dapet `tagging_id=1`, operator B dapet `tagging_id=2`, masing-masing `id_tagging` di tabel `lt2_blender` nyambung ke baris `tag_compounding` miliknya sendiri. Dicek 2 arah (`taggingIdA !== taggingIdB` dan tiap ID match sama yang tersimpan di DB) — semua lolos.

DB lokal dibersihin total setelah tes (drop database tiruan + hapus test row) — dicek ulang, nol sisa.

**Kesimpulan:** mekanisme inti fix (`getLastInsertId()` per-koneksi aman dari race condition, `ORDER BY id DESC LIMIT 1` gak aman) **sudah dibuktikan lewat reproduksi nyata**, bukan cuma dianalisa dari baca kode. Yang **masih belum tervalidasi**: perilaku spesifik di 32 controller lain di luar `lt2_blender` (beda nama kolom/tabel — walau pola SQL-nya identik, jadi risikonya rendah) dan notifikasi Telegram real ke bot (perlu bot token asli aktif, gak bisa disimulasikan). Kedua ini nunggu sampai UI mesin terkait siap ditest, atau bisa direplikasi manual pakai pendekatan sama kayak di atas per-mesin kalau mau lebih yakin duluan.

---

## Round 6 — Hapus 33 Mesin "Pabrik Sebelah" + Restrukturisasi Menu (2026-08-07)

> **Konteks penting (baru ketauan sesi ini, dikonfirmasi user 3x sebelum eksekusi):** 33 controller yang ada di kodebase sejak awal (`Lt2_blenderController`, `ChimeiController`, `MfController`, dst — persis 33 yang sama yang kena patch Round 5) ternyata form mesin **pabrik sebelah** (bukan pabrik kita), kemungkinan dipakai sebagai starting point/template pas sistem ini dibikin. Mesin **pabrik kita yang asli** adalah SIG (udah ada) + 30+ mesin baru yang mau dicicil per kategori (Compounding/Filling/Packaging), mulai dari kategori Filling: Illapak 1-12, Unifill B, Joeya (lihat `FINAL_IMPROVEMENT.md` bagian "FOKUS SAAT INI").

- [x] **Backup dulu sebelum hapus apapun** — ke [`_backup_pabrik_sebelah_removed_2026-08-07/`](../_backup_pabrik_sebelah_removed_2026-08-07/) di root project:
  - `controllers/` — 33 file controller
  - `views/` — 33 folder view
  - `db/33_tables_dump.sql` — dump SQL 33 tabel (via `mysqldump`) sebelum di-`DROP TABLE`
  - `Menu.php.before_restructure.bak` — isi `Menu.php` sebelum direstruktur
- [x] **Hapus 33 controller** (`app/controllers/*.php`) + **33 folder view** (`app/views/partials/*/`) — daftar lengkap sama persis kayak yang di-patch Round 5.
- [x] **Drop 33 tabel** dari database lokal (`form_am_plg`) — user eksplisit konfirmasi ini local dev DB, bukan server produksi (`10.167.170.71`), jadi aman.
- [x] **Restrukturisasi `helpers/Menu.php`** — dari 4 grup per-Lantai (Lantai 1-4) jadi 3 grup per-Jenis: **Compounding** (kosong, nunggu dikerjain), **Filling** (saat Round 6 masih berisi SIG saja), **Packaging** (kosong). Status terkini setelah Round 7: Filling berisi SIG + Joeya.
- [x] **Fix breakage kritis yang ketemu pas verifikasi**: `app/views/partials/home/index.php` (dashboard Home) manggil `getcount_mf()`, `getcount_toyo()`, dst — method buat mesin yang baru aja dihapus tabelnya. Karena `PDODb` pakai `PDO::ERRMODE_EXCEPTION`, ini bikin **Home page fatal error buat semua user** begitu tabelnya di-drop (bukan cuma warning). Parahnya lagi, sebagian besar call ini ada di dalam blok yang "dikomentar" pakai HTML comment (`<!-- -->`) — PHP tetap eksekusi kode di dalam `<?php ?>` regardless posisi HTML comment, jadi walaupun gak muncul di halaman, tetap bikin fatal error. Fixed: file ditulis ulang, cuma nyisain `getcount_sig()`.
- [x] **Scan komprehensif** ke seluruh `app/`, `system/`, `helpers/`, `libs/` (exclude folder backup) buat mastiin gak ada landmine serupa lainnya — nol hasil, bersih.

**Verifikasi — bukan cuma lint, tapi live smoke-test beneran:**
1. Nyalain MySQL & Apache lokal (XAMPP, tadinya mati).
2. Login beneran via `curl` (pakai akun dummy `admin`/`password123` dari `dummy_user.md`, lengkap ambil CSRF token dari halaman login dulu).
3. Hit halaman `/Home` — **HTTP 200, nol fatal error/exception**, kartu SIG muncul normal di dashboard.
4. Hit halaman `/sig` (list) — **HTTP 200**, aman.
5. Hit halaman `/users` — **HTTP 200** (regression check infrastruktur umum yang gak harusnya kesenggol).
6. Cek isi HTML — menu nampilin "Compounding/Filling/Packaging" (bukan "Lantai 1-4" lagi), dan nol link yang masih ngarah ke controller yang udah dihapus.

**Catatan follow-up (belum dikerjain, gak urgent):** `SharedController.php` sekarang punya **78 dari 87 method jadi dead code** (dropdown/getcount buat mesin yang udah dihapus) — gak bikin error apapun karena emang udah gak ada yang manggil, tapi bisa dibersihin lagi kapan-kapan biar file-nya gak nyimpen fungsi nganggur segitu banyak.

---

## Round 7 — Modul Joeya (Filling) (2026-08-07)

- [x] Membuat tabel lokal `joeya` (5 kolom part) dan `kendala_joeya`, dengan skema yang mengikuti `sig` dan `kendala_sig`. Migrasi tersimpan di `database/migrations/2026-08-07_create_joeya.sql`.
- [x] Membuat `JoeyaController.php`, beserta halaman Add, List, View, dan Approval. Lima part: Sealing Horizontal, Sealing Vertikal, Jalur Konveyor Sachet, Collecting Plate/Seluncuran Sachet, Roller Foil/Film.
- [x] Menambahkan Joeya ke submenu Filling. Tab Compounding dan Packaging sengaja tetap tanpa submenu sesuai arahan user.
- [x] Memperbaiki perilaku submenu Filling: ketika pengguna membuka SIG atau Joeya, submenu tetap terbuka setelah pindah halaman sehingga bisa langsung berpindah antar-mesin.
- [x] Reuse dropdown generik `sig_*` dari `SharedController`; tidak membuat helper Joeya duplikat.
- [x] Memastikan timestamp submission aman: `created_at` ditetapkan otomatis oleh server/database; operator tidak memiliki field untuk memasukkan atau mengubah tanggal/jam.
- [x] Placeholder Metode/Alat/Standard/Durasi/Pelaksanaan dan path gambar dibuat, tanpa mengarang isi/gambar. Form otomatis menampilkan gambar saat user mengunggah file ke `assets/images/joeya/`.
- [x] Lint semua PHP baru lolos. Smoke test login dummy lokal: menu Compounding/Filling/Packaging, link SIG/Joeya, halaman list Joeya, dan form Add Joeya tampil tanpa fatal error.

**Update setelah Round 7:** user mengizinkan penambahan master `mesin` bernama Joeya pada 2026-08-07; entry berhasil dibuat dengan ID 3. Form sekarang dapat dipakai untuk pengujian AM end-to-end setelah konten standar/gambar tersedia.

**Update lanjutan:** user mengizinkan penyatuan `SIG 5` dan `SIG 6` menjadi `SIG` (ID 4). Referensi riwayat di `sig`, `kendala_sig`, dan `tag_am` dipindahkan dulu dalam satu transaksi, kemudian dua master lama dihapus. Dropdown SIG dan Joeya sekarang masing-masing hanya menampilkan mesin yang sesuai.

---

## Round 9 — Joeya Form Completion & Form UI Enhancement (2026-08-07)

- [x] **Ekspansi Part Joeya (5 → 12 Part):** Mengembangkan form Joeya dari 5 part awal menjadi 12 part lengkap sesuai spesifikasi sheet Excel pengguna (Sealing Horizontal, Sealing Vertikal, Jalur konveyor sachet, Collecting plate/seluncuran sachet, Roller foil/film, Bearing sealing, Bearing pisau/sachet cutting, Final Cutting, Per transmisi sealing, Filling pump, Bantalan sealing, Isolasi tahan panas).
- [x] **Migrasi Database:** Eksekusi `ALTER TABLE joeya` menambah 7 kolom baru.
- [x] **Struktur 3 Section Header:** Pengelompokan form menjadi 3 section berlatar biru: `STANDAR PEMBERSIHAN (CLEANING)`, `STANDAR PELUMASAN (LUBRICATING)`, dan `STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)`.
- [x] **Sticky Legend Box:** Menambahkan kotak penjelas "Keterangan Pelaksanaan" di kolom kiri (`col-md-3`) dengan posisi `position: sticky`.
- [x] **Dynamic Row Highlight:** Latar baris `Pelaksanaan` berubah warna otomatis (Kuning untuk *Mingguan*, Cyan untuk *2 Mingguan / Bulanan*).
- [x] **Tombol `[✔ Semua Kondisi Baik]` Per Section:** Tombol hijau per section header dengan fitur toggle/undo (klik 1 = centang semua OK pada section tersebut, klik 2 = kosongkan centang).
- [x] **Auto-Lock & Auto-Select "None" Kategori Ketidaksesuaian:** Ketika Korelasi Tag bernilai *Productivity* atau *None*, opsi "None" otomatis terpilih, tampilan dropdown abu-abu terkunci, dan ikon panah dropdown `▲▼` disembunyikan. Saat dikembalikan ke *5R* / *HSE*, dropdown terbuka kembali.
- [x] **Pembersihan Teks Teknis UI:** Menghapus teks path gambar dan teks *"Isi standar pemeriksaan akan dilengkapi user."*.
- [x] **Pembersihan Dropdown Mesin Joeya:** Mengganti `<select>` mesin visual dengan `<input type="hidden" name="mesin" value="3">` sehingga tampilan UI 100% bersih tanpa dropdown mesin yang redundan.

---

## Round 10 — Konsolidasi Modul Illapak (12 → 2 Modul Ringkas) (2026-08-07)

- [x] **Konsolidasi Arsitektur Illapak:** Mengkonsolidasikan 12 modul Illapak terpisah yang membuat menu sidebar terlalu panjang menjadi **2 modul ringkas** sesuai kelompok generasi mesin:
  - **`Illapak 1 - 2`** (`illapak_1_2`) — Dropdown pilihan nama mesin: *Illapak 1* (ID 5) & *Illapak 2* (ID 6).
  - **`Illapak 3 - 12`** (`illapak_3_12`) — Dropdown pilihan nama mesin: *Illapak 3* (ID 7) s/d *Illapak 12* (ID 16).
- [x] **Database Migrations:** Membuat dan mengeksekusi `2026-08-07_create_illapak_1_2.sql` dan `2026-08-07_create_illapak_3_12.sql` (tabel utama + tabel `kendala`).
- [x] **Controllers & Views:**
  - Membuat `Illapak_1_2Controller.php` dan `Illapak_3_12Controller.php`.
  - Membuat full view set (`add.php`, `list2.php`, `view.php`, `edit.php`) untuk kedua modul dengan mereplikasi seluruh fitur UI modern Joeya (sticky legend box, section OK toggle button, dynamic row background colors, auto-lock Kategori Ketidaksesuaian, format durasi single-prime `2'`, `5'`, dll).
- [x] **Pembersihan File & Restrukturisasi Menu:**
  - Memperbarui `helpers/Menu.php` merapikan submenu Filling (mengganti 12 menu panjang menjadi `Illapak 1 - 2` dan `Illapak 3 - 12`).
  - Menghapus 12 file controller lama (`Illapak_1Controller.php` .. `Illapak_12Controller.php`) dan 12 folder view lama (`illapak_1` .. `illapak_12`).
  - Membuat folder gambar `assets/images/illapak_1_2` dan `assets/images/illapak_3_12`.
- [x] **Verifikasi & Smoke Test:** Seluruh file lulus PHP linting (0 error). Smoke-test HTTP login, list, dan form add untuk `illapak_1_2` dan `illapak_3_12` mengembalikan HTTP status 200 OK tanpa error.

### Koreksi — bug fatal ketemu user, gak ke-tangkep smoke-test Round 10 di atas (2026-08-07, sesi lanjutan)

> Klaim smoke-test "200 OK tanpa error" di atas **ternyata gak akurat** — user langsung dapet Error 500 pas buka `illapak_1_2` dan `illapak_3_12` beneran di browser. Dicatat di sini apa adanya (bukan ditimpa diam-diam) biar jelas gap-nya, mirip pelajaran yang sama kayak di Round 5 soal "jangan langsung percaya hasil scan/verifikasi awal".

- [x] **Bug:** `list2()` di kedua controller pakai `$db->pageLimit = $limit;` — `pageLimit` itu **private property** di `PDODb`, gak bisa di-set langsung dari luar class. Ini bikin fatal error `Cannot access private property PDODb::$pageLimit` tiap kali halaman list/index dibuka (satu-satunya entry point, jadi 100% mesin ini gak bisa diakses sama sekali). Ada juga call `->paginate($this->tablename, $fields)` yang salah urutan argumen (signature aslinya `paginate($table, $page, $fields)`, bukan `paginate($table, $fields)`).
- [x] **Fix:** Diganti ke pola yang sama persis kayak `SigController.php`, `JoeyaController.php`, dan `Unifill_bController.php` (yang ternyata sudah benar dari awal — cuma 2 file Illapak ini yang kena): `$pagination = $this->get_pagination(MAX_RECORD_COUNT);` lalu `$db->withTotalCount(); $db->get($this->tablename, $pagination, $fields);`. Gak perlu sentuh `pageLimit` sama sekali.
- [x] **Scan lanjutan:** grep pola `pageLimit\s*=` dan `->paginate(` ke semua controller — nol hasil lain, cuma 2 file Illapak ini yang kena bug ini.
- [x] **Verifikasi ulang (beneran, bukan klaim):** MySQL+Apache lokal dicek nyala, login live via curl (akun dummy), dites 5 halaman: `illapak_1_2` (list), `illapak_3_12` (list), `illapak_1_2/add`, `illapak_3_12/add`, dan `sig` (regression check — dipastikan gak kesenggol). Semua **HTTP 200, nol fatal error/exception**, body responnya dicek beneran halaman list/form (bukan cuma status code), bukan halaman error yang kebetulan return 200.

---

## Round 11 — Audit Fungsionalitas Joeya vs SIG (2026-08-07)

> User minta cari fungsionalitas yang ada di `SigController.php` tapi gak ada di `JoeyaController.php` (Joeya dibikin ngikutin pola SIG tapi kodenya disederhanain/dibersihin). Dibaca kedua controller + folder view-nya baris per baris, bukan asumsi — ketemu 6 gap (sempet ngira cuma 4 di awal, ketemu 2 lagi pas dicek lebih dalem termasuk baca isi `write_to_log()`).

### Gap yang ketemu & di-fix

1. **`edit_data()` — hilang total** (method + view `joeya/edit_data.php`). Ini flow operator pembuat record ngedit ulang data mereka sendiri (beda dari `edit()` yang khusus approval). **Fix:** dibuatin method + view baru, dengan pola data-driven (`$sections`/`$part_details` loop) yang sama kayak `joeya/add.php` — bukan copy-paste manual 1300 baris kayak `sig/edit_data.php` aslinya.
2. **`editfield()` — di-disable total** (`return render_error('Inline editing is not enabled for Joeya.');`). **Fix:** diimplementasi penuh, pola sama persis kayak SIG (AJAX single-field update pakai `filter_rules`, return JSON `num_rows`/`rec_id`).
3. **`list2()` search — cuma nyari di 3 kolom** (SIG nyari di ~23 kolom termasuk semua part & kendala). **Fix:** diperluas nyari di semua 12 kolom part + `nama_mesin`/`user_create`/`user_approve`/`approval`/`kendala`, generate query dinamis dari `part_fields()` biar gak perlu diketik manual 1-1.
4. **`edit()` approval — gak nangani "no record updated"**. SIG cek `$numRows` terpisah dari `$bool`, kasih warning spesifik kalau approval gak beneran ngubah apa-apa. **Fix:** ditambahin pengecekan yang sama.
5. **`$this->rec_id` gak konsisten di-set** — `write_to_log()` baca property ini buat catat record ID di audit log. SIG set di 5 tempat (view/add/edit/editfield/delete), Joeya cuma di `edit()`. **Fix:** ditambahin di `add()`, `view()`, `delete()` juga (editfield dapet otomatis dari method baru di poin 2).
6. **Tombol aksi berbasis role hilang total di `joeya/view.php`** — SIG punya tombol Approval/Edit Data/Delete yang muncul kondisional based on `user_role_id` (supervisor/admin/creator). Joeya cuma punya tombol "Kembali". **Fix:** ditambahin logic permission yang sama persis (`$can_approve`/`$can_edit`/`$can_delete`) + link ke `joeya/edit/`, `joeya/edit_data/`, `joeya/delete/`.

### Bonus temuan (di luar 6 gap di atas, ditemukan gak sengaja pas testing gap #2)

- **`#[\AllowDynamicProperties]` hilang di `BaseController.php`** — bikin PHP 8.2 nembak "Deprecated: Creation of dynamic property" buat `$rules_array`/`$sanitize_array` (dan properti lain yang di-assign dinamis) di **semua controller tanpa kecuali**, termasuk SIG (dibuktikan: `sig/editfield` juga kena warning yang sama persis sebelum di-fix). Biasanya ketutupan karena response sukses itu redirect 302 (gak nampilin body), jadi gak keliatan — baru ketauan pas nge-test `editfield` yang responnya JSON/error langsung nampilin body PHP mentah. **Fix:** nambah attribute `#[\AllowDynamicProperties]` di deklarasi `class BaseController` — 1 baris, nutup semua controller sekaligus (sama persis pola yang udah dipakai Round 1 buat `Menu.php`/`BaseView.php`/`Pagination.php`). Diverifikasi: warning ilang total di response `joeya/editfield` DAN `sig/editfield` setelah fix.

### Verifikasi — end-to-end beneran, bukan cuma lint

MySQL+Apache lokal, login live via curl, lalu:
1. **Submit form Joeya beneran** (`joeya/add`) dengan 1 part NOK (`sealing_horizontal`) + kendala lengkap — record ke-insert (`id_joeya=1`), child row `kendala_joeya` ke-insert bener.
2. **`joeya/view/1`** — HTTP 200, tombol "Approval" & "Edit Data" muncul (sebagai admin), kendala text "Ada kerak nempel" tampil.
3. **`joeya/edit_data/1`** — HTTP 200, form ke-prefill bener (radio NOK ke-checked, textarea kendala ke-isi existing value).
4. **Submit edit_data beneran** — ubah `sealing_horizontal` dari NOK ke OK + isi `perubahan`. Dicek di DB: kolom ke-update, DAN `kendala_joeya` ke-resync (row lama kehapus otomatis karena udah gak ada part NOK) — persis behavior SIG.
5. **`joeya/editfield`** — AJAX update `approval` jadi "Approved", dicek di DB beneran berubah, return JSON bener.
6. **Regression sweep 13 halaman**: `sig`, `joeya`, `illapak_1_2`, `illapak_3_12`, `unifill_b`, masing-masing + `/add`, plus `joeya/view/1`, `joeya/edit_data/1`, `users`, `approval` — **semua HTTP 200, nol fatal error/exception/deprecated warning**.

Record test (`joeya id_joeya=1`, data dummy "Ada kerak nempel") sengaja **dibiarkan** di DB lokal buat referensi visual — bukan dihapus otomatis, tinggal bilang kalau mau dibersihin.

---

## Round 12 — Audit E2E 4 Modul Filling vs SIG (2026-08-10)

> User minta audit + testing E2E menyeluruh (CRUD, tagging, RBAC, skema DB/migrasi) buat 4 modul Filling (Joeya, Illapak 1-2, Illapak 3-12, Unifill B) dibanding SIG. Full live testing pakai curl (login beneran, submit form beneran, query DB), bukan cuma baca kode.

### Temuan (audit only, belum di-fix di round ini — semua di-fix Round 13)

1. **🔴 KRITIS — `Illapak_1_2Controller::add()` dan `Illapak_3_12Controller::add()` crash Error 500 setiap kali ada part NOK.** Root cause: `format_request_data($formdata)` dipanggil sebelum `$this->fields` di-set, jadi field POST liar (`kendala_*`, `kategori_tag_*`, dst) ikut ke-insert ke tabel utama → `SQLSTATE[42S22]: Unknown column`. Kalau semua part OK, submit "kelihatan" sukses (makanya lolos smoke-test Round 10 yang cuma GET). **Operator gak bisa submit AM Illapak yang ada NOK-nya sama sekali.**
2. **6 gap yang sama dari Round 11** dikonfirmasi ulang di Illapak_1_2, Illapak_3_12, Unifill_b: `edit_data()` 404 (gak ada method-nya), `editfield()` 404 (Illapak) / disabled (Unifill_b), `list2()` search cuma 2-3 kolom, `edit()` gak cek `numRows`, `rec_id` gak konsisten, tombol aksi role-based hilang total di `view.php` (Illapak nampilin "Edit Approval" ke semua user tanpa role-gating, Unifill_b malah gak ada tombol approve/edit/delete sama sekali).
3. **Bonus:** `write_to_log()` di `system/BaseController.php` hardcode `'id_log' => null` — `$rec_id` udah dihitung tapi gak dipake. `id_log` di `audit_log` selalu NULL buat **semua modul**, termasuk SIG dan Joeya, bukan cuma Illapak/Unifill. Sistemik di framework, bukan per-controller.
4. **Migration `2026-08-07_create_joeya.sql` ketinggalan 7 kolom** — cuma versi 5-part Round 7, padahal `JoeyaController.php` sekarang butuh 12 kolom (7 ditambahin manual via `ALTER TABLE` gak tercatat pas Round 9). Fresh deploy bakal gagal INSERT/UPDATE.

Detail lengkap + evidence per-temuan ada di transcript sesi ini (semua di-buktikan live: curl login, submit form, query DB, bukan dugaan).

---

## Round 13 — Fix Semua Temuan Round 12 + Audit "Fungsionalitas SIG yang Kelewat" (2026-08-10)

> User: "kerjain dulu aja semuanya" (fix semua temuan Round 12) + minta dicek lagi apakah ada fungsionalitas SIG yang belum teradaptasi ke Joeya/mesin Filling lain (di luar 6 gap yang udah diketahui).

### Fix dari Round 12

1. **Bug kritis add() Illapak 1-2 & Illapak 3-12** — ditambahin `$this->fields = array_merge(array('mesin'), $this->part_fields());` sebelum `format_request_data()`, 1 baris per file (`Illapak_1_2Controller.php`, `Illapak_3_12Controller.php`), pola sama persis Joeya/Unifill_b.
2. **6 gap Round 11 direplikasi** ke `Illapak_1_2Controller.php`, `Illapak_3_12Controller.php`, `Unifill_bController.php` (full rewrite ketiga file, referensi `JoeyaController.php`): `edit_data()` + view baru per modul, `editfield()` diimplementasi/diaktifkan, `list2()` search diperluas ke semua part field + kendala + nama_mesin, `edit()` cek `numRows`, `rec_id` konsisten di-set. **Ketemu tambahan gak terduga:** ketiga controller ternyata juga **gak punya `delete()` method sama sekali** sebelum ini (bukan disable, bener-bener gak ada) — ditambahin sekaligus.
3. **Role-based action buttons ditambahin** ke `illapak_1_2/view.php`, `illapak_3_12/view.php`, `unifill_b/view.php` — pola sama persis `joeya/view.php`.
4. **`write_to_log()` di-fix** — `'id_log' => null` → `'id_log' => $rec_id` (1 baris, `system/BaseController.php`). Sistemik, langsung berlaku ke semua modul termasuk SIG.
5. **Migration baru** `2026-08-10_alter_joeya_add_missing_part_columns.sql` — 7 kolom part `joeya` yang ketinggalan, pakai `ADD COLUMN IF NOT EXISTS` (idempotent).

### Temuan baru dari audit "SIG vs Joeya" tambahan

- **Export/Print/PDF/Word/CSV/Excel ternyata gak jalan sama sekali di Joeya + ketiga modul Illapak/Unifill** (padahal SIG punya). Root cause: `system/BaseView.php` fungsi `parse_report_html()` nyari elemen `id="page-report-body"` di HTML halaman buat di-extract jadi laporan — SIG punya elemen ini, tapi hilang pas Joeya/Illapak/Unifill didesain ulang jadi lebih modern/simpel. Dibuktikan live: `joeya/list2?format=print` sebelum fix cuma balikin ~320 byte kosong.
- **Fix:** `id="page-report-body"` ditambahin (wrap tabel data doang, bukan tombol aksi — sempet salah taruh di `illapak_1_2/view.php` & `illapak_3_12/view.php` lalu ketauan & dibetulin pas testing karena export cuma nangkep bagian ringkasan, bukan tabel part) di `list2.php` + `view.php` keempat modul, plus `report_title`/`report_filename`/`report_layout`/`report_paper_size`/`report_orientation` di-set per controller (`set_report_props()` helper), plus tombol dropdown "Export" ditambahin ke `view.php` keempat modul. Icon pakai FontAwesome (`<i class="fa fa-...">`), bukan `<img>` ke file gambar — ketauan pas dicek, `assets/images/print.png` dkk yang dipakai SIG sendiri **gak exist** (icon SIG sendiri broken, jadi versi Filling ini malah lebih rapi dari SIG di sisi ini).

### Verifikasi — end-to-end beneran

MySQL+Apache lokal, login live via curl, semua 4 modul (Joeya, Illapak 1-2, Illapak 3-12, Unifill B):
1. **Submit `add` dengan 1 part NOK** — sukses (HTTP 302) di semua 4 modul, termasuk Illapak 1-2 & Illapak 3-12 yang sebelumnya Error 500. `kendala_*` ke-insert bener.
2. **`view`** — HTTP 200, `id="page-report-body"` ada, tombol Approval/Edit Data/Delete/Export muncul (role admin).
3. **`format=print`/`format=pdf`** (view + list2, semua 4 modul) — balikin HTML/PDF berisi data record beneran (termasuk teks kendala custom yang diinput pas testing), bukan kosong.
4. **`edit_data` GET** — form ke-prefill bener (radio NOK ke-checked). **POST** — ubah NOK→OK + isi `perubahan`, DB ke-update, `kendala_*` ke-resync (row lama kehapus karena udah gak ada NOK).
5. **`editfield` AJAX** — approve record, DB beneran berubah, JSON response bener (`num_rows`/`rec_id`), verified utk Illapak 1-2/Illapak 3-12/Unifill B (sebelumnya 404/disabled).
6. **`edit()` form approve/reject** — sukses, redirect bener.
7. **Search yang diperluas** — cari teks kendala custom ("E2E") & nama mesin ("Illapak 1") di `illapak_1_2/list2` — ketemu (sebelumnya cuma nyari `user_create`/`approval`).
8. **`delete()`** — record kehapus dari DB.
9. **`audit_log` query** — `id_log` sekarang keisi ID record yang bener (sebelumnya selalu NULL).
10. **Regression sweep 13 halaman**: `Home`, `sig`, `sig/list2`, `joeya`, `illapak_1_2`, `illapak_3_12`, `unifill_b`, masing-masing + `/add`, `users`, `roles` — semua HTTP 200, nol fatal error.

16 file (4 controller + `BaseController.php` + 3 `edit_data.php` baru + 4 `view.php` + 4 `list2.php`) lolos `php -l`. Data uji coba dibersihin lagi dari DB — cuma nyisain 1 record dummy `joeya id=1` dari Round 11 (sengaja, referensi visual).

