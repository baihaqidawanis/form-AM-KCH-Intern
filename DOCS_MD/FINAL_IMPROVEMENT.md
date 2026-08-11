# 🗺️ Form AM — Final Improvement Plan

> Dokumen ini isinya **cuma rencana yang belum dikerjain**. Yang udah selesai (checklist + evaluasi detail) sudah dipindah ke [EVALUATION.md](./EVALUATION.md). Context ringkas project buat tiap sesi ada di [CLAUDE.md](../CLAUDE.md). Analisis awal (arsip, sebelum ada perbaikan apapun) ada di [ANALYSIS.md](./ANALYSIS.md).
>
> Nomor temuan (`#4`, `#8`, dst) tetap dipertahankan sama seperti versi awal dokumen ini, biar konsisten sama referensi di `EVALUATION.md` — makanya nomornya keliatan loncat (yang udah beres nomornya dicabut dari sini).

---

## 🏭 Konteks Project (Penting — Baca Dulu)

**Nama:** Form AM (Asset Maintenance) Site Pulogadung
**Stack:** PHP Native (PHPRad generated) + MySQL/MariaDB + Apache
**Database production:** `10.167.170.71` — server internal kantor

> ⚠️ **Ini aplikasi internal, cuma bisa diakses dari jaringan kantor (bukan internet-facing).** Ini mengubah prioritas: banyak temuan "security" di audit awal risikonya rendah karena penyerang harus sudah punya akses ke jaringan kantor dulu. Yang **tetap wajib diperbaiki** adalah temuan yang sifatnya **bug fungsional** atau **bikin kerjaan maintenance ke depan susah** — bukan soal exploit dari luar.

---

## 🔴 PRIORITAS 1 — Bug Nyata (bukan soal security, aplikasinya emang salah)

Kosong buat sekarang — **`#4`** (race condition di tagging, 33 controller) udah dikerjain kodenya, tapi **statusnya masih "menunggu smoke-test browser/DB"**, belum bisa dianggap 100% selesai. Detail lengkap + checklist testing yang wajib dijalanin ada di [EVALUATION.md Round 5](./EVALUATION.md#round-5--4-race-condition-di-tagging-2026-08-07).

---

## 🟠 PRIORITAS 2 — Maintainability (supaya perubahan kecil gak jadi mimpi buruk)

| # | Temuan | Kenapa Penting |
|---|--------|-----------------|
| 8 | **Telegram bot token & logic di-copy paste ke 30+ controller** | Bukan soal token bocor (internal network) — tapi kalau chat ID/format pesan berubah, harus edit 30+ file satu-satu. Bikin 1 helper class `TelegramNotifier.php`. Butuh testing tiap notifikasi manual, bukan quick win |
| 9 | **56 controller 95% identik** (Code Duplication Ekstrem) | Bug fix di 1 tempat harus di-copy ke 55 lainnya = pasti ada yang kelewat. Kerjaan besar multi-hari — lihat blueprint `BaseMachineController` di bawah |
| 11 | **Naming convention gak konsisten** (`user_approve` / `user_approved` / `user_approver` di tabel berbeda) | Bikin developer baru bingung, tapi ubahnya butuh migrasi data — jangka panjang |

---

## 🟡 PRIORITAS 3 — Nice to Have (risiko rendah di internal network)

- Standarisasi HTTP call pakai `curl` semua (saat ini campur `curl` & `file_get_contents`)

---

## ❌ SKIP — Over-engineering untuk Konteks Internal 1 Server

| Temuan | Kenapa Skip |
|--------|-------------|
| **MinIO object storage** | Worth it kalau ada 2+ server terpisah atau NAS. Di 1 server, MinIO ikut mati bareng app kalau server down — gak nyelametin apa-apa. Cukup pakai **Docker volume** untuk `uploads/` supaya file gak hilang pas container restart |
| **Rate limiting login / account lockout** | Serangan brute force dari jaringan kantor sendiri = 0% realistis |
| **CSRF token rotation** | Attack vector CSRF di internal network hampir nol |
| **Audit `timthumb.php` (RCE vuln 2012)** | Vulnerability-nya butuh akses internet-facing untuk dieksploitasi; di internal network attack surface-nya nol |

*(Kalau suatu saat app ini dibuka ke internet atau VPN eksternal, daftar SKIP ini harus di-review ulang — prioritasnya akan berubah total.)*

---

## 🟢 Pengembangan Baru (Roadmap Opsional, Bukan Prioritas Jangka Pendek)

Kalau ke depan ada waktu/resource untuk modernisasi (bukan sekadar fix bug):

1. **REST API layer buat CRUD data mesin** (`ApiController.php` saat ini cuma proxy AJAX sempit ke `SharedController` buat isi dropdown — **bukan** REST API umum, lihat catatan di `EVALUATION.md` Round 3) → perlu diperluas biar bisa di-consume mobile/dashboard lain
2. **Next.js dashboard** di atas API tersebut (PHP backend tetap jalan, gak perlu rebuild total)
3. **Structured logging** (Monolog) — biar debugging production gak cuma modal `error_log`
4. **README.md proper** — saat ini `readme.txt` cuma berisi "test234"
5. **`HomeController.php` dashboard**, **`panduan_pengisian_am`** — masih kosong/minimal, isi kalau ada kapasitas

Detail teknis lengkap (contoh kode, struktur folder Next.js, dll) ada di [Improvement.md](./Improvement.md) bagian "🚀 Saran Pengembangan Lebih Lanjut" — dokumen ini sengaja gak diulang di sini biar ringkas.

---

## 🎯 FOKUS SAAT INI: Bikin Mesin Baru Ngikutin Pola SIG (Core Task)

> **Ini core task-nya, bukan sekadar refactor.** Awalnya ada 14 mesin Filling baru yang belum ada sama sekali di kodebase. **Joeya sudah selesai diimplementasikan**; 13 mesin tersisa (Illapak 1-12 dan Unifill B) bukan migrasi controller lama, tetapi modul baru dari nol yang mengikuti pola SIG.

### Restrukturisasi Menu: per-Jenis Mesin (bukan per-Lantai) — ✅ SELESAI

> **Update:** 33 controller/view/tabel mesin lama udah **dihapus total** (ternyata itu form pabrik sebelah, bukan pabrik kita — dikonfirmasi user, detail lengkap di [EVALUATION.md Round 6](./EVALUATION.md#round-6--hapus-33-mesin-pabrik-sebelah--restrukturisasi-menu-2026-08-07)). Jadi bukan "dipetain ulang" kayak rencana awal, tapi dihapus bersih. Menu sidebar (`helpers/Menu.php`) udah direstruktur jadi 3 section per-jenis mesin:

1. **Compounding** — kosong, nunggu dikerjain
2. **Filling** — isi SIG + Joeya; Illapak 1-12 dan Unifill B menyusul ← **dikerjain duluan**
3. **Packaging** — kosong, nunggu dikerjain

### Kategori Filling — Daftar Mesin (fokus saat ini)

| Mesin | Status |
|---|---|
| SIG | ✅ Udah diimplementasi — ini **pola acuan/template** buat mesin lain |
| Illapak 1 | ✅ Implementasi awal selesai — part placeholder (sama dengan Joeya), konten standar menunggu user |
| Illapak 2 | ✅ Implementasi awal selesai — part placeholder, konten standar menunggu user |
| Illapak 3 | ✅ Implementasi awal selesai — part placeholder, konten standar menunggu user |
| Illapak 4 | ✅ Implementasi awal selesai — part placeholder, konten standar menunggu user |
| Illapak 5 | ✅ Implementasi awal selesai — part placeholder, konten standar menunggu user |
| Illapak 6 | ✅ Implementasi awal selesai — part placeholder, konten standar menunggu user |
| Illapak 7 | ✅ Implementasi awal selesai — part placeholder, konten standar menunggu user |
| Illapak 8 | ✅ Implementasi awal selesai — part placeholder, konten standar menunggu user |
| Illapak 9 | ✅ Implementasi awal selesai — part placeholder, konten standar menunggu user |
| Illapak 10 | ✅ Implementasi awal selesai — part placeholder, konten standar menunggu user |
| Illapak 11 | ✅ Implementasi awal selesai — part placeholder, konten standar menunggu user |
| Illapak 12 | ✅ Implementasi awal selesai — part placeholder, konten standar menunggu user |
| Unifill B | ✅ Implementasi awal selesai — part placeholder, konten standar menunggu user |
| Joeya | ✅ Implementasi awal selesai — konten standar/gambar masih menunggu user |

**Aturan konten per mesin baru:**
- **Judul kolom tabel (nomor, nama part/alat, dst) — SAMAIN PERSIS kayak SIG.** Ini yang bikin `BaseMachineController` + config generik (lihat blueprint di bawah) jadi masuk akal — 13 mesin tersisa ini strukturnya seragam dari awal, bukan hasil generate PHPRad yang beda-beda kayak 32 controller lama.
- **Yang beda per mesin:** gambar mesin, dan isian metode/substansi pengecekan (baris-baris part & kondisinya) — bukan strukturnya.

**Kategori Compounding & Packaging** — daftar mesinnya belum dibahas, nunggu Filling kelar dulu atau didiskusikan terpisah.

### Template SIG — Struktur yang Wajib Disamain per Mesin Baru

> Dibaca langsung dari [SigController.php](../app/controllers/SigController.php) (817 baris) dan [sig/add.php](../app/views/partials/sig/add.php) (2010 baris) — bukan asumsi. Pola ini berulang persis buat tiap "bagian/part" mesin (~12x di SIG), jadi cukup dibaca 1 kali buat ngerti semuanya.

**A. Struktur database (2 tabel):**

1. **Tabel utama** (`sig` → nanti jadi `illapak_1`, `unifill_b`, dst): 1 kolom per "bagian/part" mesin, isinya cuma status `OK`/`NOK` (varchar) — misal `sealing_cross_dan_vertikal`, `guarding_akrilik`, dst. Plus kolom standar: `id_sig` (PK), `Mesin` (FK ke tabel `mesin`), `created_at`, `updated_at`, `user_create`, `user_approve`, `approval`, `perubahan`, `user_perubah`, `tanggal_perubahan`.
2. **Tabel anak** (`kendala_sig` → nanti jadi `kendala_illapak_1`, dst **atau** 1 tabel generik — ini keputusan pending yang disebut di atas): isinya cuma keisi kalau ada bagian yang `NOK`. Kolom: `id_am` (FK ke tabel utama), `mesin`, `nama_bagian` (nama part yang NOK), `kendala` (teks keluhan), `kategori_tag`, `korelasi_tag`, `kategori_ketidaksesuaian`, `klasifikasi_tag`, `created_at`.

**B. Struktur form Add (per "bagian/part" mesin, berulang buat tiap part):**

1. **Label part** (misal "Sealing Cross dan Vertikal")
2. **Gambar mesin** — `assets/images/{nama_mesin}/{nama_mesin} {nama_part}.png`, klik buat lihat ukuran penuh. Convention nama file di SIG: huruf kecil semua, spasi antar kata (`sig sealing cross.png`, `sig akrilik.png`, dst) — bukan underscore.
3. **Tabel info di sebelah gambar** — 5 baris, **judul barisnya PERSIS SAMA buat semua part & semua mesin** (ini yang dimaksud user harus disamain):
   - `Metode` (misal "Disikat", "Dilap", "Dicek, disetting")
   - `Alat` (misal "Sikat Kawat", "Quiltec", "Air Gun")
   - `Standard` (kondisi yang diharapkan)
   - `Durasi` (misal "5'", "2'")
   - `Pelaksanaan` (frekuensi: "Harian(Setiap Awal Shift 1)" biasa, atau "Bulanan(Setiap W1 Senin Shift 1)" — beda warna background biar kebeda visual)
4. **Radio button kondisi** — OK / NOK (kadang ada varian ke-3 kayak "N/A" tergantung part, lihat `Menu::$Kondisi_Harian`, `$antistatic`, `$Vacuum_Pad`, dst di `helpers/Menu.php`)
5. **Blok "Kendala" (muncul cuma kalau NOK dipilih, disembunyiin pakai JS default)**: textarea keluhan + 4 dropdown berantai (Kategori Tag → Korelasi Tag → Klasifikasi Tag → Kategori Ketidaksesuaian, yang terakhir 2 di-load AJAX tergantung pilihan sebelumnya lewat `SharedController`/`ApiController`)
6. **Timestamp submission** — `created_at` diisi otomatis oleh server saat submit (`datetime_now()`), dengan default timestamp database sebagai fallback. Operator tidak diberi field untuk mengubah tanggal/jam input.
7. **Navigasi Filling** — submenu tetap terbuka pada halaman SIG maupun Joeya agar operator dapat berpindah langsung antar mesin tanpa membuka grup Filling kembali.

**Yang WAJIB sama persis di semua mesin baru:** judul kolom tabel info (`Metode`/`Alat`/`Standard`/`Durasi`/`Pelaksanaan`), struktur radio OK/NOK, struktur blok Kendala 4-dropdown, alur insert ke tabel anak.

**Yang beda per mesin/part:** nama & jumlah part (`daftar_bagian`), isi gambar, isi tabel info (nilai Metode/Alat/Standard/Durasi/Pelaksanaan-nya), nama tabel database.

---

## 🔵 KEPUTUSAN PENDING: Refactor SIG → Pola Compact (Joeya-kan SIG)

> **Belum diputusin** — ditaruh di sini biar gak lupa. Lanjutkan diskusi di sesi berikutnya.

### Konteks

SIG saat ini **satu-satunya controller Filling yang masih "fat"** (817 baris). 14 controller lainnya (Joeya + Illapak 1-12 + Unifill B) semua pakai pola compact (88 baris). Perbedaannya **bukan fungsional** — murni soal cara kode ditulis:

| | SIG (fat, legacy PHPRad) | Joeya dkk (compact) |
|---|---|---|
| **Ukuran** | 817 baris | 88 baris |
| **Parts** | 12 part di-hardcode satu-satu, tiap part = blok kode terpisah | `$parts` array + `foreach` loop |
| **Views** | ~2010 baris (add.php), 12 section copy-paste | ~42 baris, 1 loop |
| **Kenapa begini** | Di-generate oleh **PHPRad** (tool code generator otomatis) — verbose by design | Ditulis manual dari nol di Round 7 sebagai versi bersih dari pola SIG |
| **Fitur extra** | `edit_data` (edit data terpisah dari approval), search per-field detail | Belum ada — bisa ditambahkan kalau perlu |

### Risiko kalau dibiarkan (tech debt)

- Bug fix di flow (misal cara insert kendala, validasi, dll) harus fix di **2 tempat**: SIG (pola verbose) + 14 lainnya (pola compact)
- Developer baru yang baca kode akan bingung kenapa SIG beda sendiri

### Opsi

| Opsi | Effort | Risiko | Benefit |
|---|---|---|---|
| **A. Joeya-kan SIG** — rewrite SIG jadi compact 88 baris, samain pola sama 14 lainnya | Sedang (~1-2 jam) | Rendah-sedang. SIG udah ada data production, perlu pastiin semua fitur tetap jalan (termasuk `edit_data` dan search detail yang Joeya belum punya) | 15 controller seragam, maintenance 1 pola |
| **B. Langsung `BaseMachineController`** — bikin base class, semua 15 controller jadi ~5 baris | Besar (~1 hari+) | Sedang-tinggi. Perubahan arsitektur, testing menyeluruh | Solusi paling clean, tapi scope besar |
| **C. Biarin dulu** — SIG tetap fat | Zero | Zero (short-term), nambah tech debt (long-term) | — |

**Rekomendasi**: Opsi A dulu (low-effort, langsung bikin seragam), baru nanti lanjut ke Opsi B kalau ada waktu.

---

## 🗺️ Blueprint: Refactor 56 Controller → 1 `BaseMachineController`

> Ini rencana kerja, **belum dieksekusi**. Ditulis berdasarkan baca penuh [Lt2_blenderController.php](../app/controllers/Lt2_blenderController.php) (557 baris) sebagai contoh nyata, bukan asumsi. **Update:** dengan 13 mesin Filling tersisa yang didesain seragam dari awal mengikuti SIG, opsi "1 tabel generik" di bawah makin masuk akal — tidak ada data lama yang perlu dimigrasi, tinggal desain benar dari awal.

> ⚠️ **KOREKSI PENTING (belum final, lanjut sesi berikutnya):** Awalnya draft di bawah ini pakai `lt2_blender` sebagai pola acuan karena dianggap "paling standar". Ternyata **salah asumsi** — `SigController.php` (816 baris, baca full) justru **pola target masa depan**: 55 form lain rencananya bakal di-upgrade biar strukturnya persis kayak SIG (form per-bagian dengan kendala terpisah lewat tabel anak `kendala_sig`, plus flow `edit_data` yang misah dari `edit` approval). Jadi `BaseMachineController` sebaiknya dibikin dari pola SIG, bukan pola lama.
>
> **Yang bikin ini belum bisa langsung dieksekusi:** kalau 55 form lain mau ikutan pola SIG (kendala per-bagian), itu bukan cuma refactor controller — perlu **perubahan skema database** juga, karena sekarang mereka cuma punya 1 kolom `kendala` varchar (bukan tabel anak kayak `kendala_sig`). Ada 2 opsi yang perlu diputusin dulu sebelum lanjut:
> 1. **1 tabel generik** (`kendala_detail` + kolom `machine_key`) dipakai bareng semua 55 mesin — lebih gampang di-handle `BaseMachineController` karena strukturnya seragam.
> 2. **1 tabel per mesin** (`kendala_lt2_blender`, dst, persis pola SIG apa adanya) — konsisten 100% sama SIG, tapi jadi 55 tabel baru terpisah.
>
> **Belum diputusin** — lanjutkan diskusi ini di sesi berikutnya sebelum mulai coding apapun.

### Kenapa gak bisa langsung jadi 1 file doang

`Router.php` resolve controller pakai **konvensi nama class = nama file**: URL `lt2_blender/...` → butuh class `Lt2_blenderController` yang harus `class_exists()`, dan `index.php`'s `autoloadController()` cari file persis `app/controllers/Lt2_blenderController.php`. Jadi **56 file tetap harus ada** (satu per URL/mesin) — tapi isinya bisa dibikin nyaris kosong.

### Pola yang ketemu di `Lt2_blenderController.php` (berlaku sama persis di 55 controller lain)

Tiap controller cuma beda di 6 hal:
1. `$tablename` (nama tabel)
2. Daftar kolom untuk `list` / `view` / `add` / `edit` (field arrays)
3. Rules & sanitize array per field (`required`, `numeric`, `sanitize_string`)
4. Nama mesin & lokasi lantai (buat judul halaman & pesan Telegram)
5. Angka `area_id, line_id` yang di-hardcode di query `INSERT ... SELECT` ke `new_breakdown_management_2.tag_compounding` (baris 302-311 — ini beda-beda tiap mesin/lantai!)
6. Path view (`lt2_blender/list.php` dst — otomatis dari `$tablename`, gak perlu config terpisah)

Method-nya (`index`, `view`, `add`, `edit`, `editfield`, `delete`) **strukturnya 100% identik** di semua controller — cuma nama tabel & kolom yang beda.

### Rencana Konkret

**Step 1 — Buat `system/BaseMachineController.php`**
Extends `SecureController`, isi 6 method generik (`index`, `view`, `add`, `edit`, `editfield`, `delete`) yang baca konfigurasi dari property, bukan hardcode. Di-`require` langsung di `index.php` setelah `SecureController.php` (bukan lewat autoload folder controllers, biar selalu ke-load duluan).

**Step 2 — Buat 1 file config terpusat** `app/config/machine_config.php`, isi array asosiatif per mesin:
```php
return [
    'lt2_blender' => [
        'label' => 'Blender',
        'lantai' => 'Lantai 2',
        'list_fields' => ['id','date_created','no_blender', ...],
        'view_fields' => [...],
        'add_fields' => [...],
        'add_rules' => ['no_blender' => 'required', ...],
        'add_sanitize' => [...],
        'edit_fields' => [...],
        'edit_rules' => [...],
        'edit_sanitize' => [...],
        'tag_compounding_area_id' => 3,
        'tag_compounding_line_id' => 3,
    ],
    // ...55 entry lain, tinggal copy-paste dari controller lama
];
```

**Step 3 — Tiap 56 controller lama disusutkan jadi ~5 baris:**
```php
<?php
class Lt2_blenderController extends BaseMachineController {
    protected $machineKey = "lt2_blender"; // key ke machine_config.php
}
```

**Step 4 — Sekalian benerin `#4` (race condition) di `BaseMachineController::add()`** — pakai `$db->getInsertId()` bukan `ORDER BY id DESC LIMIT 1`, dan pindahin logic Telegram ke `TelegramNotifier` helper (item `#8`).

### Urutan Eksekusi yang Aman (jangan sekaligus 56!)

1. Bikin `BaseMachineController` + `machine_config.php`, tapi **cuma isi 1 entry dulu** (`lt2_blender`)
2. Ganti `Lt2_blenderController.php` jadi versi pendek, test SEMUA fungsinya manual (list, search, add, edit, editfield, delete, notif Telegram, red tag)
3. Kalau lolos, baru migrasi controller berikutnya satu-satu — **jangan migrasi 56 sekaligus lalu ditest belakangan**, karena kalau ada 1 config yang salah copy, susah lacak di mesin mana
4. Checklist per controller: cocokin field list, rules, sanitize, area_id/line_id dengan versi lama sebelum hapus versi lama

**Estimasi realistis:** ~1 hari buat `BaseMachineController` + config 1 mesin pertama + testing, lalu ~15-30 menit per mesin berikutnya (kalau lancar) = beberapa hari total untuk 56 mesin. Ini kerjaan multi-hari, bukan multi-jam.

---

## 🗺️ Blueprint: Struktur Folder BE/FE

> **Insight penting:** app PHP yang sekarang **gak perlu dipecah/dipindah foldernya sama sekali**. `Router.php` dan `index.php` pakai path constant dari `config.php` (`CONTROLLERS_DIR`, `MODELS_DIR`, `VIEWS_DIR`, dst) yang konvensinya kaku — mindahin folder `app/`, `system/`, `libs/` ke dalam folder `backend/` cuma nambah risiko rusak tanpa manfaat nyata, karena Next.js nantinya **cukup manggil API lewat HTTP**, gak peduli PHP-nya taruh dimana.

### Struktur yang Direkomendasikan (kalau udah mulai project Next.js)

```
form-am/                      ← project PHP existing, TIDAK DIUBAH strukturnya
├── app/
├── system/
├── config.php
├── ... (semua yang sekarang, apa adanya)
│
frontend/                     ← FOLDER BARU, sibling — bukan nested/pindahin PHP
├── (hasil npx create-next-app)
```

Cukup bikin folder `frontend/` di level yang sama dengan folder PHP (bukan di dalamnya), isi project Next.js baru di situ. PHP tetap jalan sebagai "backend" secara fungsi (setelah `ApiController.php` diperluas jadi REST API beneran), tanpa pindah 1 file pun.

### Kenapa ini urutannya harus setelah API layer jadi, bukan sebelum

Next.js gak ada gunanya dibikin sekarang karena `ApiController.php` masih scope-nya sempit (cuma dropdown proxy). Urutan yang benar:
1. Perluas `ApiController.php` jadi REST API CRUD (`GET /api/{machine}`, dst) — **butuh `BaseMachineController` dulu selesai**, karena API endpoint generik juga bisa reuse logic yang sama
2. Baru `npx create-next-app` di folder `frontend/`
3. Next.js consume API dari situ

**Kesimpulan:** folder `frontend/` **aman dibuat kapan aja** (zero-risk, gak nyentuh app existing), tapi baru ada gunanya setelah step 1 (API layer) beres. Jangan bikin folder `frontend/` duluan terus nganggur — mending prioritas ke `BaseMachineController` dulu.

---

*Awalnya digabung dari [ANALYSIS.md](./ANALYSIS.md) (2026-08-04) dan [Improvement.md](./Improvement.md) (2026-08-06) pada 2026-08-06. Dipisah jadi plan-only (item selesai pindah ke `EVALUATION.md`) pada 2026-08-07.*
