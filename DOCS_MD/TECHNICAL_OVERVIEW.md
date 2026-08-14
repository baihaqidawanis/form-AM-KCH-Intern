# 🏗️ Dokumentasi Teknis — Form AM

> Arsitektur aplikasi apa adanya sekarang (14 Agustus 2026). Buat histori "kenapa jadi begini" per keputusan, lihat `EVALUATION.md`. Buat cara deploy, lihat `DEPLOYMENT.md`. Buat cara testing, lihat `TESTING.md`.

## Stack

- **Backend:** PHP Native (PHPRad-generated scaffold awal, banyak bagian sudah ditulis ulang manual) — **bukan Laravel/Symfony/framework modern apapun**. Router, ORM (`PDODb`), autoloader semuanya custom, ada di `system/` & `libs/`.
- **Database:** PostgreSQL 17 (lokal/dev). ⚠️ **Server production (`10.167.170.71`) masih MySQL/MariaDB, belum dimigrasi** — lihat `DEPLOYMENT.md`.
- **Web server:** Apache (XAMPP di lokal).
- **PHP OPcache:** aktif (`zend_extension=opcache` + `opcache.enable=1` di `php.ini`) — sebelumnya mati (default XAMPP), diaktifkan 14 Agustus 2026 buat performa (PHP gak compile ulang source tiap request). Zero-cost, zero-downside, gak butuh perubahan kode. **Pastikan ini juga aktif di server production** saat deploy (bukan sesuatu yang otomatis ke-bawa dari kode, murni setting `php.ini` server).
- **Dependency PHP:** cuma 1 — `dompdf/dompdf` (`composer.json`). Dev-dependency: `phpunit/phpunit`, `guzzlehttp/guzzle` (testing).
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
