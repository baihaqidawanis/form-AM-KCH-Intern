# 🏗️ Dokumentasi Teknis — Form AM

> Arsitektur aplikasi apa adanya sekarang (14 Agustus 2026). Buat histori "kenapa jadi begini" per keputusan, lihat `EVALUATION.md`. Buat cara deploy, lihat `DEPLOYMENT.md`. Buat cara testing, lihat `TESTING.md`.

## Stack

- **Backend:** PHP Native (PHPRad-generated scaffold awal, banyak bagian sudah ditulis ulang manual) — **bukan Laravel/Symfony/framework modern apapun**. Router, ORM (`PDODb`), autoloader semuanya custom, ada di `system/` & `libs/`.
- **Database:** PostgreSQL 17 (lokal/dev). ⚠️ **Server production (`10.167.170.71`) masih MySQL/MariaDB, belum dimigrasi** — lihat `DEPLOYMENT.md`.
- **Web server:** Apache (XAMPP di lokal).
- **PHP OPcache:** **MATI** (default XAMPP). Sempat dicoba diaktifkan 14 Agustus 2026 buat performa, tapi ternyata bikin Apache gak stabil di setup Windows ini — muncul error `VirtualProtect() failed` berulang di log Apache (masalah dikenal OPcache+Windows, biasanya terkait `opcache.protect_memory`/interaksi antivirus) yang berujung ke request gagal random. **Dimatikan lagi**, stabilitas lebih penting daripada gain performa kecil di skala aplikasi ini. Kalau mau dicoba lagi di server production (OS beda, mungkin Linux), riset dulu setting yang aman (`opcache.protect_memory=0`, dst) dan tes stabilitasnya dulu sebelum diaktifin permanen — jangan asumsikan otomatis aman cuma karena "biasanya zero-downside".
- **Dependency PHP:** `dompdf/dompdf` (export PDF) dan `phpmailer/phpmailer` (kirim email reset-password lewat SMTP — pengganti bundel lama `libs/PHPMailer/` v5.2.7 tahun 2013 yang pakai fungsi `each()` yang sudah dihapus dari PHP 8, jadi selalu crash tiap kali beneran nyoba kirim email) (`composer.json`). Dev-dependency: `phpunit/phpunit`, `guzzlehttp/guzzle` (testing).
- **Dependency JS:** `@playwright/test` (dev-only, testing e2e). Gak ada framework frontend — semua view server-rendered PHP + jQuery/Bootstrap.
- **Internal-only**: aplikasi cuma bisa diakses dari jaringan kantor, bukan internet-facing. Ini mengubah prioritas beberapa temuan "security" klasik (lihat bagian "Sengaja Tidak Dikerjakan" di bawah) — tapi TIDAK mengecualikan bug yang bisa dipicu user internal (misal XSS dari form isian, sudah difix, lihat `KEPATUHAN_URS.md`).

## Struktur Aplikasi: 17 Modul Mesin, 1 Kerangka Kode

Aplikasi ini pada intinya adalah **CRUD form pencatatan maintenance harian**, diulang buat 17 mesin, dikelompokkan 3 kategori:

| Kategori | Mesin |
|---|---|
| **Filling** | SIG, Joeya, Illapak 1-2, Illapak 3-12, Unifill B |
| **Packaging** | Chimei, Temach, Jihcheng, Jinsung 1-4, Jinsung 5, Best Pack |
| **Compounding** | Cosmec, FBD Jaw Chuan, FBD Glatt, Supermixer, Storage Tank, Mixing Tank |

Semua 17 modul strukturnya **100% identik** — form per-"part"/titik pemeriksaan mesin, tiap part punya kondisi OK/NOK, kalau NOK wajib isi kendala + 4 kategori tag. Bedanya cuma nama tabel, nama part, dan isi instruksi kerja (Metode/Alat/Standard/Durasi/Pelaksanaan).

### `BaseMachineController` — 1 Kelas Basis buat 17 Controller

`system/BaseMachineController.php` (abstract class, extends `SecureController`) isi semua 8 method generik (`index`/`list2`/`add`/`view`/`edit`/`edit_data`/`editfield`/`delete`). Tiap controller mesin cukup declare 3 property:

```php
<?php
class ChimeiController extends BaseMachineController
{
    protected $machineKey = 'chimei';       // nama tabel
    protected $displayName = 'Chimei';       // judul halaman/laporan
    protected $parts = array(                // field_name => label, urutan = urutan tampil
        'conveyor_produk' => 'Conveyor Produk',
        // ...
    );
}
```

`$extraFields` (opsional) buat field tambahan di luar part OK/NOK — dipakai SIG buat `value_tekanan_angin`.

**Skema database per mesin (2 tabel):**
1. **Tabel utama** (`{mesin}`): 1 kolom varchar per part (isi `OK`/`NOK`), plus `id_{mesin}` (PK), `mesin` (FK ke tabel `mesin`), `created_at`, `updated_at`, `user_create`, `user_approve`, `approval`, `perubahan`, `user_perubah`, `tanggal_perubahan`.
2. **Tabel anak** (`kendala_{mesin}`): cuma keisi kalau ada part NOK. Kolom: `id_am` (FK ke tabel utama), `mesin`, `nama_bagian`, `kendala` (text), `kategori_tag`, `korelasi_tag`, `klasifikasi_tag`, `kategori_ketidaksesuaian`, `created_at`. **1 tabel per mesin** (bukan tabel generik) — keputusan final, konsisten sama pola SIG dari awal.

Tabel master shared (dipakai semua 17 modul, jangan bikin duplikat per-mesin): `mesin`, `tag`, `korelasi`, `klasifikasi`, `kategori`, `roles`. Dropdown-nya dilayani lewat fungsi generik di `app/controllers/SharedController.php` (`sig_kategori_tag_option_list()` dkk — nama fungsi masih prefix `sig_` karena historis, tapi dipakai semua mesin).

`$router.php`'s `autoloadController()` cuma cari file di `app/controllers/` — karena `BaseMachineController` bukan controller yang di-routing langsung, dia di-`require` manual di `index.php`.

### RBAC (4 Role, sesuai URS 2.1)

`libs/ACL.php` — matrix akses per `role_id` (1=Administrator, 2=Manager, 3=Supervisor, 4=Staff/Operator), dicek di `SecureController::authenticate_user()` tiap request (proteksi rute beneran, bukan cuma sembunyiin tombol UI). Detail matrix lengkap ada di `KEPATUHAN_URS.md`.

Pembatasan "cuma boleh edit data submission sendiri" (URS 3.1) gak bisa diekspresikan di ACL (yang cuma tau ACTION, bukan kepemilikan record) — diimplementasi sebagai pengecekan eksplisit di awal tiap `edit_data()`: bandingkan `user_create` record vs user yang login, Administrator bebas dari batasan ini.

## Cara Nambah Mesin Baru

1. **Migration SQL** — `database/migrations/{tanggal}_create_{mesin}.sql`. Insert nama mesin ke tabel `mesin` pakai pola idempotent (`INSERT ... WHERE NOT EXISTS`), **cek dulu** nama mesinnya belum ada.
2. **Controller** — `app/controllers/{Mesin}Controller.php`, extends `BaseMachineController`, cukup 3 property (`$machineKey`/`$displayName`/`$parts`). **Cek dulu** nama fungsi `getcount_{mesin}()` belum ada duluan di `SharedController.php` (ada beberapa nama mesin lama yang kadang collision).
3. **5 file view** di `app/views/partials/{mesin}/` — `add.php`, `edit_data.php`, `edit.php`, `list2.php`, `view.php`. Cara tercepat: copy dari modul existing yang paling mirip (single-mesin: `chimei/`, multi-mesin dropdown: `illapak_1_2/`), ganti nama field/section/isi part (Metode/Alat/Standard/Durasi/Pelaksanaan **persis sesuai sumber data**, jangan placeholder).
4. **Wiring ke 4 tempat:** `helpers/Menu.php` (submenu), `SharedController.php` (`getcount_{mesin}()`), `home/index.php` (card dashboard), `approval/list.php` (tab approval).
5. **Kalau modul buat 1 mesin fisik**: `<input type="hidden" name="mesin" value="{ID}">` di `add.php`. **Kalau buat beberapa mesin fisik sekaligus** (kayak Illapak 1-2): `<select name="mesin">` dropdown visible.

**Pitfall yang pernah kejadian — jangan diulang:**
- `novalidate` wajib ada di tag `<form>` (barengan class `needs-validation`), kalau kelewat validasi custom Bootstrap gak jalan.
- Jangan hardcode opsi approval sendiri — selalu `foreach (Menu::$approval as $option)` (`Approved`/`Not Approved`, bukan value custom lain).
- Path gambar part: `assets/images/{mesin}/{mesin} {nama part lowercase spasi}.png`, harus ada fallback `onerror` di `<img>`.
- Field nama part pakai snake_case, konsisten antara kolom DB, key `$parts`, dan `name` attribute HTML.
- **Verifikasi wajib sebelum bilang selesai**: lint (`php -l`), submit form beneran (campuran OK + minimal 1 NOK), cek langsung ke DB, export PDF harus beneran `%PDF-1.7`, hapus lagi data uji coba, regression check modul lain yang udah ada.

## Master Data Part (SEMUA 17 mesin) — 19-20 Agustus 2026

Administrator sekarang bisa CRUD detail part mesin (foto, Metode, Alat, Standard, Durasi, Pelaksanaan, Section, Urutan, highlight warna Mingguan/Bulanan) lewat menu **Master Data Part** (`Master_partController` — perhatikan nama file/class `Master_partController.php`, BUKAN `MasterPartController`, karena `Router.php` cuma `ucfirst()` segmen URL apa adanya, gak convert underscore ke CamelCase — pola yang sama kayak `Jinsung_1_4Controller`), tanpa perlu edit kode.

- **Tabel `master_part`** — 1 tabel master lintas-mesin (bukan per-mesin), kolom `machine_key`/`field_name`/`label`/`section`/`metode`/`alat`/`standard`/`durasi`/`pelaksanaan`/`highlight`/`image_path`/`urutan`. Unique constraint `(machine_key, field_name)`.
- **`BaseMachineController::loadDynamicParts()`** (dipanggil di constructor) — override `$this->parts` dari `master_part` kalau mesin itu udah punya row di sana; kalau belum ada row sama sekali, fallback ke `$parts` hardcoded di subclass (jadi 16 mesin yang belum dimigrasikan gak kena dampak).
- **Nambah part baru = otomatis `ALTER TABLE ... ADD COLUMN IF NOT EXISTS`** ke tabel fisik mesinnya (dibungkus transaction bareng insert row `master_part`, jadi kalau ALTER gagal, insert-nya ikut rollback — gak ada state nyangkut). `machine_key` divalidasi whitelist, `field_name` divalidasi regex `^[a-z][a-z0-9_]*$` sebelum dipakai jadi identifier SQL mentah.
- **Hapus part TIDAK drop kolom** (non-destruktif) — cuma hapus row `master_part`, kolom fisik & data historis tetap aman.
- **⚠️ Operasional**: user database aplikasi (`DB_USERNAME` di `.env`) **wajib punya privilege `ALTER TABLE`** di server production, bukan cuma SELECT/INSERT/UPDATE/DELETE — kalau DBA kasih akses terbatas, fitur tambah-part-baru bakal gagal (tapi gagalnya "bersih" berkat transaction, bukan bikin data master_part nyangkut tanpa kolom fisik).

### Rollout ke 17 mesin — SELESAI (20 Agustus 2026)

Semua 17 mesin sudah baca dari `master_part`; array `$image_names`/`$part_details`/`$sections` yang dulu hardcoded di tiap `add.php`/`edit_data.php` **sudah dihapus total** (166 part: sig 11, joeya 12, illapak_1_2 15, illapak_3_12 16, unifill_b 13, chimei 9, temach 9, jihcheng 11, jinsung_1_4 8, jinsung_5 8, best_pack 6, cosmec 7, fbd_jaw_chuan 11, fbd_glatt 11, supermixer 7, storage_tank 7, mixing_tank 5).

- **Migrasi data**: `database/migrations/2026-08-20_migrate_master_part_16_mesin.sql` — **digenerate otomatis** dari array asli di view + `$parts` controller (bukan transkripsi manual), jadi isinya dijamin identik. Ikut ditambahkan ke `02_seed.sql` buat fresh install.
- **Kolom `highlight`** diisi dengan mereplikasi inference lama atas teks Pelaksanaan (38 `mingguan` + 7 `bulanan`, cocok persis dengan hitungan di source lama). Sekarang jadi kolom eksplisit — admin pilih lewat dropdown, gak lagi nebak dari string.
- **`image_path`** disimpan utuh per part karena pola foldernya beda-beda tiap mesin (mis. `unifill_b` pakai folder `unifill/unifill `, `jinsung_1_4` pakai `jinsung 1-4 `, `best_pack` pakai `best pack `) — jangan diasumsikan seragam `{mesin}/{mesin} `.
- **Bukti tidak ada regresi**: snapshot DOM ke-17 halaman `add` (section, label, metode/alat/standard/durasi/pelaksanaan, warna highlight, src gambar — 166 part) dibandingkan sebelum vs sesudah refactor → **identik 100%**.
- **`loadDynamicParts()` fallback** ke `$parts` hardcoded di controller masih dipertahankan sebagai jaring pengaman kalau tabel `master_part` kosong buat suatu mesin (mis. lupa jalanin migration di server baru) — form tetap jalan, cuma tanpa detail foto/metode.

## Proteksi Super Admin — 20 Agustus 2026 (disetujui mentor)

Cuma boleh ada **1** akun Super Admin di seluruh sistem, dan gak ada Administrator/Supervisor lain yang bisa ubah role/status/hapus akun itu — tapi sesama Administrator biasa (non-super) tetap bebas saling kelola.

- **`users.is_super_admin`** (boolean) + **partial unique index** (`WHERE is_super_admin = true`) — dijamin di level Postgres, bukan cuma diasumsikan di kode: gak mungkin ada 2 baris `true` sekaligus, walau di-insert manual lewat SQL.
- **`is_super_admin_user($id_user)`** (`helpers/Functions.php`) — helper query, dipanggil di `UsersController::edit()`/`editfield()`/`delete()` buat nolak (403) kalau target row Super Admin dan yang minta bukan dia sendiri.
- Delete Super Admin ditolak total, siapapun yang minta (termasuk dirinya sendiri) — akun ini permanen.
- Reset password Super Admin **tetap lewat email** (`PasswordmanagerController`, sesuai URS baris ~306 & Gambar 22 "View Profile – Reset Password") — **bukan** fitur ganti-password manual, itu di luar scope URS. Kalau SMTP server belum dikonfigurasi, workaround: `UPDATE users SET password = <bcrypt hash> WHERE ...` manual lewat psql/pgAdmin.

## UX Master Data Part: per-mesin + drag-and-drop urutan — 20 Agustus 2026

- **Filter "Semua" dihapus** — list SELALU 1 mesin. Alasannya: part antar mesin gak ada hubungannya (nyampur bikin bingung) dan urutan drag-drop cuma masuk akal dalam 1 mesin. `index()` tanpa parameter default ke mesin pertama **yang sudah ada datanya** (bukan sekadar key pertama), biar gak mendarat di halaman kosong.
- **Semua redirect jadi per-mesin**: simpan/batal/hapus balik ke `master_part/index/{machine_key}` asal, bukan ke mesin default. Tombol "Tambah Part" bawa machine_key lewat URL (`master_part/add/{key}`) buat preselect dropdown — `add()` punya guard kalau POST datang tanpa segmen mesin (Router naruh `$_POST` di argumen pertama, jadi digeser manual).
- **Drag-and-drop urutan** pakai **HTML5 Drag & Drop API bawaan browser** — sengaja TANPA jQuery UI/library baru (project gak punya bundler, dan dipakai di jaringan internal yang belum tentu bisa ambil CDN). Endpoint `Master_partController::reorder()` nerima `ids` (dipisah koma, posisi array = urutan baru), CSRF check, validasi semua id ADA & satu `machine_key` yang sama (nolak reorder lintas mesin), dibungkus transaction, balikin JSON.
- Urutan hasil drag langsung kepakai di form Add AM (`ORDER BY urutan`), sudah diverifikasi persist setelah reload.

## Kolom "Tanggal Approval" di list2 — 20 Agustus 2026

Semua 17 `list2.php` sekarang nampilin kolom **Tanggal Approval** (setelah "Approval Oleh", `colspan` empty-state naik dari 8 → 9). Datanya dari `tanggal_perubahan` yang sebenarnya **udah lama diisi** di `BaseMachineController::add()` (auto-approve) & `edit()` (approval manual) — cuma belum pernah ditampilkan di list. Kalau bikin mesin baru dan copy dari modul existing, kolom ini otomatis ikut.

## Re-evaluate approval abis edit_data() — 20 Agustus 2026

Gap yang ketemu user pas cek Cosmec: `edit_data()` (operator koreksi data submission sendiri) dulu **gak pernah nyentuh status `approval` sama sekali** — record yang tadinya auto-approved (semua OK) terus dikoreksi jadi ada NOK, tetap nampilin "Approved" di UI walau ada part bermasalah. Sebaliknya, record NOK yang dikoreksi jadi OK semua ya tetap nyangkut status lama, gak auto-approve ulang.

**Fix di `BaseMachineController::edit_data()`**: abis `$modeldata` final (part-part udah kena update dari form), re-cek semua `part_fields()`:
- **Semua OK** → `approval='Approved'`, `user_approve='System'`, `tanggal_perubahan=now()` (persis kayak logic auto-approve di `add()`).
- **Ada yang NOK** → `approval`/`user_approve`/`tanggal_perubahan` di-reset ke `null` (BUKAN langsung "Not Approved") — record balik masuk antrian review manual supervisor/manager, konsisten sama gimana submission NOK baru diperlakukan.

Ada juga kolom **"User Update"** baru di semua 17 `list2.php` (dari `user_perubah`) — biar keliatan langsung di overview siapa yang terakhir koreksi data, gak perlu buka View satu-satu. Test regresi: `ApprovalFlowTest::test_edit_data_ubah_ke_nok_reset_approval_ke_pending`.

## Field approval/update tracking di halaman View — 20 Agustus 2026

Ketemu gap: mockup UI resmi di URS (Gambar 9/10, "View Mesin – Edit Data") nunjukin halaman View seharusnya nampilin `User Approve`, `Approval`, `Tanggal Approve`, `User Update`, `Tanggal Update`, `Perubahan` (log catatan edit) -- tapi `view.php` di semua 17 mesin cuma nampilin Nama Mesin/Tanggal/Pembuat + tabel part, field lainnya kelewat sejak awal.

- **`BaseMachineController::view()`** — query `$fields` ditambah `user_perubah`, `updated_at`, `perubahan` (sebelumnya cuma select `user_approve`, `approval`, `tanggal_perubahan` yang datanya ada tapi gak pernah ditampilkan).
- **17 `view.php`** — 6 baris baru ditambahin ke tabel info atas, persis setelah baris "Pembuat": User Approve, Approval, Tanggal Approve (`tanggal_perubahan`), User Update (`user_perubah`), Tanggal Update (`updated_at`), Perubahan (`perubahan`, di-`nl2br`+`htmlspecialchars` karena isinya free-text catatan operator).
- `updated_at`/`user_perubah`/`perubahan` cuma keisi kalau record pernah di-`edit_data()` (operator koreksi data sendiri) -- record yang belum pernah diedit nampilin `-` di 3 field itu, ini normal bukan bug.

## Rename Tabel Mesin (`tb_mesin_*`) — 19 Agustus 2026

17 tabel utama mesin (bukan `kendala_*`) di-rename dari nama polos (`chimei`, `sig`, dst) jadi prefix `tb_mesin_` (`tb_mesin_chimei`, `tb_mesin_sig`, dst). URL/routing/nama folder view/`machineKey` **TIDAK** ikut berubah — cuma nama tabel fisik. Dipisah lewat `BaseMachineController::sqlTable()` (nama tabel fisik) vs `$machineKey` (tetap dipakai buat URL/view/idColumn). Gak ada FK constraint di schema ini, jadi rename aman tanpa migrasi relasi.

## Known Gaps / Belum Selesai

- **Illapak 1-12 & Unifill B** masih pakai part placeholder (generic, sama kayak Joeya) — user perlu ganti nama part sesuai spesifikasi mesin sebenarnya + upload gambar + isi Metode/Alat/Standard/Durasi/Pelaksanaan.
- **Naming convention gak konsisten** antar beberapa tabel lama (`user_approve` vs varian lain) — kosmetik, butuh migrasi data kalau mau dibenerin, prioritas rendah.
- **`Panduan Pengisian AM`** masih 1 gambar statis, bukan panduan interaktif langkah-per-langkah (Rank D di URS, prioritas rendah).

## Sengaja Tidak Dikerjakan (dan Kenapa)

Karena app internal-only (bukan internet-facing), beberapa "temuan security" klasik risikonya rendah — penyerang harus sudah punya akses jaringan kantor dulu:

| Temuan | Kenapa Skip |
|---|---|
| MinIO / object storage terpisah | Worth it kalau ada 2+ server/NAS. Di 1 server, cukup Docker volume buat `uploads/` |
| Rate limiting login tambahan (di luar lockout 3x yang sudah ada) | Brute force dari jaringan kantor sendiri risikonya sangat rendah |
| CSRF token rotation | Attack vector CSRF di internal network hampir nol |

*(Kalau app ini suatu saat dibuka ke internet/VPN eksternal, daftar ini harus di-review ulang.)*

**Catatan penting**: exception ke prinsip "internal = lebih aman" adalah bug yang bisa dipicu **user internal legit** (misal stored-XSS lewat form isian normal) — itu tetap dianggap prioritas tinggi dan sudah difix, lihat `KEPATUHAN_URS.md`.

## Roadmap Opsional (Bukan Prioritas)

Kalau ke depan ada waktu/resource untuk modernisasi (bukan sekadar maintenance):
1. **REST API layer** buat CRUD data mesin (`ApiController.php` sekarang cuma proxy AJAX sempit buat dropdown, bukan REST API umum) → perluas biar bisa di-consume mobile/dashboard lain.
2. **Next.js dashboard** di atas API tersebut — PHP tetap jalan sebagai backend, `frontend/` sebagai folder sibling baru, gak perlu rebuild total. Urutannya harus REST API dulu, baru Next.js (Next.js gak ada gunanya kalau API-nya masih sempit).
3. **Structured logging** (Monolog) — debugging production sekarang cuma modal `error_log`.
