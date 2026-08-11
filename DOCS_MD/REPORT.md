# 📊 Laporan Progress — Digitalisasi Form AM

> Dokumen ini buat dipresentasiin di weekly meeting (tiap Selasa). Ditulis biar bisa dibaca 2 audiens sekaligus: **user/atasan** cukup baca bagian "Dampak" & "Yang Kita Lakuin", **mentor teknis** bisa buka bagian "Detail Teknis" (klik buat expand) kalau mau lihat kode/query aslinya.
>
> Beda sama [EVALUATION.md](./EVALUATION.md): itu changelog teknis per-sesi kerja (buat continuity antar sesi Claude), ini laporan per-masalah buat presentasi progress. Isinya di-refresh tiap minggu, bukan ditumpuk kronologis.

---

## 📅 Update Minggu Ini (Minggu ke-2 — 11 Agustus 2026)

Minggu ini fokus **eksekusi**: nerusin pola yang udah divalidasi minggu lalu (SIG → Joeya) ke seluruh sisa mesin Filling, terus lanjut bangun Packaging dari nol. Total **10 mesin baru** jadi dalam 1 minggu, plus serangkaian bug UX & rendering yang ketemu pas dipakai beneran.

- ✅ **Kategori Filling 100% selesai** — SIG, Joeya, Illapak 1-2, Illapak 3-12, Unifill B. Ketemu & dibenerin bug fatal (Illapak crash tiap ada part NOK), 6 gap fungsionalitas (edit data sendiri, tombol aksi hilang, dll) direplikasi merata ke semua modul, plus export Print/PDF/Word/CSV/Excel yang ternyata dari awal gak jalan sama sekali di 4 modul non-SIG — semua udah dibenerin & ditest end-to-end.
- ✅ **Kategori Packaging 100% selesai (mesin baru, dari nol)** — Chimei, Temach, Jihcheng, Jinsung 1-4, Jinsung 5, Best Pack. Tiap mesin dibangun dari screenshot Excel standar kerja asli (bukan placeholder), lengkap dengan foto part, section Cleaning/Lubricating/Inspection & Tightening, dan smart toggle "Semua Kondisi Baik".
- ✅ **Bug PDF export ditemukan & di-diagnosa sampai akar masalahnya** — bukan cuma di mesin baru, ternyata **SIG juga kena** (dibuktikan generate PDF asli & dibandingin byte-per-byte). 2 dari 3 penyebab udah dibenerin (logo gambar yang gak pernah ada, footer yang numpuk konten); 1 sisanya kebukti murni bug di *rendering engine* library PDF lama yang butuh upgrade — **keblokir jaringan kantor yang gak bisa akses GitHub** (dibuktikan, bukan alesan).
- ✅ **Bug UX filter ditemukan & dibenerin di 4 modul** — dropdown "Nama Mesin" kelihatan "reset" abis difilter (padahal filter-nya jalan), gara-gara logic `selected` yang kelewat pas dibangun. Sekalian dirapiin: label per-field, tombol Reset, validasi form custom yang sebelumnya ke-block validasi bawaan browser.
- ✅ **Approval & Home disinkronin ulang** — Approval ternyata masih nunjuk ke 32 controller mesin pabrik sebelah yang udah dihapus dari Round 6 (nongol 32 kotak merah error), Home cuma nampilin SIG doang padahal Joeya dkk udah lama jadi. Dua-duanya sekarang otomatis ikut nambah tiap ada mesin baru.
- ✅ **1 modul (Best Pack) dibangun user pakai AI lain (Gemini), sempet Error 500** — dicek, ketemu bug (manggil class database yang gak ada), dibenerin 1 baris. Sekalian UI-nya disamain ke pola 9 mesin lain biar konsisten (awalnya beda desain sendiri).
- ⏳ **Compounding masih kosong** — jadi prioritas berikutnya, lihat bagian "Rencana ke Depan" di bawah.

---

## 📊 Ringkasan Status

| # | Masalah | Dampak Kalau Dibiarkan | Status |
|---|---------|------------------------|--------|
| 1 | Race condition tagging (33 controller lama) | Data laporan kendala operator bisa ketuker antar mesin | ✅ Fixed minggu lalu — **catatan:** 33 controller ini termasuk yang di-drop total di Round 6 (pabrik sebelah), fix-nya tetap valid buat sisa controller yang masih dipakai |
| 2 | Kategori **Filling** (5 mesin) | Operator gak punya form digital buat mesin Filling selain SIG | ✅ **100% selesai**, teruji end-to-end |
| 3 | Kategori **Packaging** (6 mesin) | Operator gak punya form digital sama sekali buat Packaging | ✅ **100% selesai**, teruji end-to-end |
| 4 | Kategori **Compounding** | Operator gak punya form digital sama sekali buat Compounding | ⏳ **Belum mulai** — prioritas berikutnya, nunggu daftar mesin & standar kerja |
| 5 | Export PDF rusak (semua mesin, termasuk SIG) | Laporan hasil export PDF gak kebuka bener / isinya berantakan | 🟡 2/3 penyebab fixed, 1/3 keblokir jaringan (bukan bug yang bisa kita benerin dari sisi kode) |
| 6 | Filter dropdown "reset" visual | User bingung apa filternya jalan apa kagak | ✅ Fixed di 4 modul |
| 7 | Approval nunjuk controller yang udah dihapus | 32 kotak error merah muncul di halaman Approval | ✅ Fixed, direstruktur total |
| 8 | Home dashboard gak lengkap | Cuma nampilin 1 dari 11 mesin yang udah ada | ✅ Fixed, otomatis nambah tiap mesin baru |
| 9 | Kredensial database di source code | Password DB kebawa kalau source ke-share | ✅ Fixed minggu lalu, di `.env` |
| 10 | Duplikasi kode 87 fungsi | Bug fix harus diulang manual puluhan kali | ✅ Fixed minggu lalu |
| 11 | `TelegramNotifier` tercecer di 30+ file | *(update)* Sisa controller yang punya kode ini tinggal **1** (SIG) — 32 lainnya udah ikut kehapus di Round 6. Prioritas ini turun drastis. | 🟢 Nyaris gak relevan lagi, cek detail di bawah |
| 12 | 56 controller mesin duplikat | Maintenance jangka panjang berat | 🟡 *(update)* Sekarang tinggal ~15 controller (bukan 56) dan **udah konsisten** compact (~200 baris tiap file, bukan lagi PHPRad verbose) — masih bisa disatukan ke `BaseMachineController`, tapi urgensinya turun karena kodenya udah seragam & gak "fat" |

---

## 🟢 Pencapaian Utama: Filling & Packaging Selesai

### Kategori Filling — 5 Mesin

| Mesin | Part | Status |
|---|---|---|
| SIG | 12 part | ✅ Pola acuan (legacy, masih format lama) |
| Joeya | 12 part | ✅ |
| Illapak 1 - 2 | 15 part | ✅ |
| Illapak 3 - 12 | 16 part | ✅ |
| Unifill B | — | ✅ |

<details>
<summary><b>Detail Teknis — bug yang ketemu & dibenerin di kategori Filling</b></summary>

- **Bug fatal**: `Illapak_1_2Controller::add()` dan `Illapak_3_12Controller::add()` **crash Error 500 setiap ada part NOK** — operator gak bisa submit laporan sama sekali kalau ada masalah di mesin. Root cause: urutan pemanggilan fungsi yang salah (`format_request_data()` dipanggil sebelum daftar field di-set), bikin data POST liar ikut ke-proses dan nabrak kolom database yang gak ada. Fix 1 baris kode per file, tapi dampaknya kritis — ini baru ketauan karena ditest submit form beneran dengan data NOK, bukan cuma buka halaman.
- **6 gap fungsionalitas** ditemuin lewat audit "apa yang ada di SIG tapi kelewat pas Joeya dkk dibikin": operator gak bisa edit data sendiri kalau salah isi, tombol approval/edit/hapus hilang dari halaman detail di 3 dari 4 modul, kolom pencarian cuma nyari 2-3 field (harusnya 20+), dan audit log (`id_log`) selalu kosong di **semua** modul termasuk SIG (bug sistemik di file inti, bukan per-modul).
- **Export total gak jalan** di 4 modul (Print/PDF/Word/CSV/Excel) — kelihatannya UI-nya lengkap ada tombol Export, tapi klik gak menghasilkan apa-apa. Penyebab: elemen HTML yang jadi "penanda" konten laporan (`id="page-report-body"`) ketinggalan pas modul-modul ini didesain ulang jadi lebih modern.

Detail lengkap tiap round ada di [CLAUDE.md](../CLAUDE.md) Round 12-16.
</details>

### Kategori Packaging — 6 Mesin (Baru, dari Nol)

| Mesin | Part | Status |
|---|---|---|
| Chimei | 9 part | ✅ |
| Temach | 9 part | ✅ |
| Jihcheng | 11 part | ✅ |
| Jinsung 1 - 4 | 8 part | ✅ |
| Jinsung 5 | 8 part (beda dikit dari 1-4) | ✅ |
| Best Pack | 6 part | ✅ |

<details>
<summary><b>Detail Teknis — cara kerja & temuan menarik</b></summary>

Tiap mesin dibangun dari **screenshot Excel standar kerja asli** yang dikasih user (bukan data placeholder) — nama part, alat, metode, standar, durasi, dan frekuensi pelaksanaan semua diisi sesuai dokumen resmi. Sebelum mulai build, selalu dikonfirmasi dulu ke user kalau ada bagian tabel yang keliatan terpotong di screenshot, biar gak salah bangun dengan data gak lengkap.

**Temuan menarik:** Jinsung ternyata punya 5 mesin fisik yang perlu dipecah jadi 2 modul (mirip pola Illapak 1-2 / Illapak 3-12) karena template standarnya beda dikit antara mesin 1-4 dan mesin 5 (alat beda, ada 1 part ekstra "Timing Belt Conveyor" di mesin 5). Ketauan dari screenshot kedua yang dikasih user, bukan asumsi.

**Setiap modul baru divalidasi end-to-end beneran** sebelum dianggap selesai: submit form dengan campuran kondisi OK & NOK (termasuk teks kendala custom), cek data masuk database dengan benar, buka halaman detail & daftar (badge hijau/merah harus sesuai), export PDF harus jadi file valid, baru dianggap kelar. Bukan cuma "halaman kebuka = selesai".
</details>

---

## 🟡 Bug Ditemukan & Diperbaiki Minggu Ini

### 1. Export PDF Rusak — Bukan Cuma Mesin Baru, SIG Juga Kena

**Dampak:** User laporan PDF hasil export "berantakan" — tulisan header numpuk sama judul, kadang teks acak-acakan. Awalnya diduga masalah khusus mesin hasil replikasi.

**Yang kita lakuin:** Generate PDF asli dari SIG dan mesin lain, dibaca isinya byte-per-byte buat dibandingin — **ternyata SIG kena bug yang sama persis**. Ini bukan soal "mesin baru kurang bagus", tapi bug di file shared (`report_layout.php`) yang dipakai SEMUA modul.

Ditemuin 3 penyebab:
1. **File logo perusahaan gak pernah ada di server** — dompdf (library pembuat PDF) nyisipin teks error "Image not found" di posisinya, yang bikin layout header collapse dan judul numpuk. **✅ Fixed** — gambar rusak dihapus, sekalian bersihin info kontak placeholder yang emang gak pernah diisi bener (nomor telepon fiktif, email format salah).
2. **Footer "ngambang" nutupin konten** — CSS-nya salah setting posisi, numpuk di atas tabel alih-alih di bawah. Paling kelihatan di mesin dengan part sedikit. **✅ Fixed**.
3. **Bug murni di rendering engine library PDF** (`dompdf` versi 2020, dari sebelum PHP versi sekarang ada) — dibuktikan dengan cara ekstrak HTML mentah sebelum masuk proses PDF: hasilnya **bersih total**, jadi kerusakannya genuinely dari library-nya, bukan kode kita. 🟡 **Butuh upgrade library buat kelar total** — keblokir karena jaringan kantor gak bisa akses GitHub (dicoba langsung, semua host GitHub timeout, cuma metadata doang yang bisa diakses).

**PDF tetap bisa dipakai sekarang** (bukan rusak total) — cuma bagian header kadang kurang rapi, isi datanya tetap kebaca jelas.

### 2. Filter Dropdown Kelihatan "Reset" Padahal Filternya Jalan

**Dampak:** User pilih mesin di dropdown filter, submit, hasilnya kefilter dengan benar — tapi dropdown-nya kelihatan balik ke "Semua Mesin". Bikin ragu apa filternya beneran jalan.

**Kenapa:** Dropdown-nya emang gak pernah ditulis buat "mengingat" pilihan terakhir (SIG punya logic ini, 4 modul lain kelewat pas dibangun).

**Fix:** Disamain ke pola SIG di 4 modul, sekalian ditambahin label per-field dan tombol Reset yang tadinya gak ada.

### 3. Approval & Home Nunjuk ke Mesin yang Udah Gak Ada

**Dampak:** Halaman Approval nampilin 32 kotak merah "Controller Not Found" — sisa peninggalan sebelum Round 6 (pas 33 mesin pabrik sebelah dihapus, halaman ini kelewat gak ikut diupdate). Halaman Home cuma nampilin 1 dari 11 mesin yang udah ada.

**Fix:** Kedua halaman ditulis ulang biar otomatis sinkron sama mesin yang beneran ada sekarang.

<details>
<summary><b>Detail Teknis lengkap semua bug minggu ini</b></summary>

Termasuk bug deprecation warning yang bocor ke tampilan (`trim(): Passing null...`), typo di kode modul Best Pack yang dibangun pakai AI lain (manggil class database yang gak exist), dan UI Best Pack yang awalnya beda desain sendiri (disamain ke pola 9 mesin lain). Detail lengkap termasuk cara pembuktian tiap bug ada di [CLAUDE.md](../CLAUDE.md) Round 14-22.
</details>

---

## 🔮 Rencana ke Depan

### 1. Compounding — Prioritas Berikutnya

Sama kayak Packaging, kategori Compounding **masih kosong total**. Pola pengerjaannya udah terbukti jalan cepat dan konsisten (10 mesin selesai minggu ini): kasih screenshot standar kerja Excel per mesin (nama part, alat, metode, standar, durasi, pelaksanaan) → dikonfirmasi kelengkapannya → dibangun & ditest end-to-end.

**Estimasi:** dengan pola yang udah settle, 1 mesin baru (kalau standarnya mirip Chimei/Temach — single machine, 6-12 part) makan waktu kerja aktif **±30-60 menit** termasuk testing end-to-end. Kalau ada mesin yang perlu dipecah jadi beberapa modul (kayak kasus Jinsung), sedikit lebih lama. **Yang perlu disiapin dari sisi user:** daftar mesin Compounding + screenshot/data standar kerja masing-masing (part, alat, metode, standar, durasi, frekuensi) — belum ada progress lebih lanjut sampai data ini siap.

### 2. Migrasi Frontend ke Next.js

Ini proyek besar, bukan quick task — realistis butuh **1,5-3 bulan** kerja (tergantung jumlah orang & seberapa persis harus sama kayak sekarang), karena:

1. **Belum ada REST API layer.** `ApiController.php` yang ada sekarang cuma proxy sempit buat isi dropdown, bukan API CRUD beneran. Harus dibangun dulu dari nol buat semua ~15+ mesin (dan Compounding kalau udah jadi) — estimasi **1-2 minggu**.
2. **Frontend Next.js-nya sendiri** — nge-replicate semua fitur yang ada sekarang (list + filter + export, form add dengan section/toggle/badge dinamis, view detail, approval flow, edit data) buat tiap mesin — estimasi **3-6 minggu** tergantung berapa developer yang ngerjain bareng.
3. **Testing & parity check** — mastiin gak ada fitur yang keilang pas pindah — estimasi **1-2 minggu**.

**Rekomendasi urutan:** jangan mulai Next.js sebelum API layer beres — kalau dipaksa mulai duluan, bakal banyak kerjaan double pas API-nya jadi belakangan. Detail teknis struktur folder (gak perlu pindahin project PHP yang sekarang, cukup folder baru sibling) ada di [FINAL_IMPROVEMENT.md](./FINAL_IMPROVEMENT.md).

### 3. Refactor `BaseMachineController` — Prioritas Turun

Rencana lama (di `FINAL_IMPROVEMENT.md`) based on asumsi **56 controller** yang duplikat parah. Sekarang situasinya udah beda jauh: 32 dari 56 itu **udah dihapus** (Round 6, pabrik sebelah), dan 15 sisanya (mesin Filling + Packaging) **udah dibangun konsisten** dari awal (~200 baris tiap file, bukan lagi kode PHPRad yang verbose/beda-beda). Jadi:

- **Urgensinya turun** — kodenya udah rapi & seragam, cuma emang masih ada duplikasi struktur antar 15 file.
- **Kalau tetap mau dikerjain**, estimasi ~**1-2 hari** (turun dari estimasi lama "beberapa hari" karena tinggal 15 controller, bukan 56, dan polanya udah seragam dari awal — bukan config-per-mesin yang beda-beda kayak kode PHPRad lama).
- **SIG masih "fat"** (816 baris, format lama PHPRad) — 1 modul ini beda sendiri dari 10 lainnya yang udah compact. Ada opsi "Joeya-kan SIG" (rewrite ke format compact) yang belum dieksekusi, estimasi ~1-2 jam kerja + testing menyeluruh karena SIG punya data production.

### 4. `TelegramNotifier` Refactor — Nyaris Gak Relevan Lagi

Temuan lama bilang ada 30+ controller yang copy-paste logic notifikasi Telegram. **Dicek ulang minggu ini: tinggal 1 file** (`SigController.php`) — 32 lainnya udah ikut terhapus di Round 6 bareng mesin pabrik sebelah. Item ini bisa turun prioritas jauh, gak worth bikin helper class terpisah buat 1 file doang.

### 5. dompdf Upgrade — Nunggu Akses Jaringan

Upgrade library `dompdf` ke versi modern bakal nyelesain sisa 1 bug PDF yang belum kebenerin (lihat bagian bug di atas). **Estimasi kerja: 30 menit - 1 jam** (jalanin `composer require`, test ulang export di semua mesin) — begitu jaringan kantor bisa akses `github.com`. Ini bukan soal kerjaan coding yang lama, murni nunggu akses jaringan.

---

*Update berikutnya: Selasa depan. Detail teknis lengkap & histori kerja per-sesi ada di [EVALUATION.md](./EVALUATION.md) dan [CLAUDE.md](../CLAUDE.md). Rencana kerja yang belum dieksekusi ada di [FINAL_IMPROVEMENT.md](./FINAL_IMPROVEMENT.md).*
