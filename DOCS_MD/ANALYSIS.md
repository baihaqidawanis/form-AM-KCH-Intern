# 📋 Analisis Project: Form AM Site Pulogadung (Arsip)

> **Diarsipkan 2026-08-07.** Dokumen ini adalah analisis awal (statis, sebelum ada perbaikan apapun dieksekusi). Sudah digantikan oleh 3 dokumen aktif:
> - [CLAUDE.md](../CLAUDE.md) — context ringkas untuk Claude, diupdate tiap akhir sesi
> - [FINAL_IMPROVEMENT.md](./FINAL_IMPROVEMENT.md) — rencana perbaikan yang **belum** dikerjain
> - [EVALUATION.md](./EVALUATION.md) — checklist & evaluasi dari yang **udah** dikerjain
>
> Isi di bawah ini dibiarkan apa adanya sebagai referensi historis — beberapa temuan di sini sudah **tidak akurat lagi** (misal klaim `ApiController.php` "stub kosong tanpa implementasi" ternyata salah, lihat catatan di `EVALUATION.md`).

---

## 🏭 Tentang Project

**Nama:** Form AM (Asset Maintenance) Site Pulogadung  
**Stack:** PHP Native (PHPRad generated) + MySQL + Apache  
**Tujuan:** Sistem pencatatan form maintenance mesin-mesin di pabrik Pulogadung  
**Database:** MySQL di server internal kantor (`10.167.170.71`) — on-premise, tidak bisa diakses dari luar jaringan kantor  

---

## 🔍 Temuan: Struktur Project

### ✅ Yang Sudah Ada & Berjalan
- 50+ form mesin masing-masing punya halaman: `list`, `add`, `edit`, `view`
- Sistem autentikasi & otorisasi (login, role, permission)
- Export PDF (`dompdf`) dan Excel (`XLSXWriter`)
- Upload file (`Uploader.php`)
- Validasi form (`GUMP.php`)
- Pagination, CSRF protection, ACL
- Docker siap deploy

### ⚠️ Yang Belum Selesai
| Modul | Status | Keterangan |
|-------|--------|------------|
| `panduan_pengisian_am` | ❌ Kosong | Hanya ada 1 gambar placeholder, konten panduan belum dibuat |
| `ApiController.php` | ❌ Stub kosong | File ada (415 bytes) tapi tidak ada implementasi sama sekali |
| `sig/list.php` | ⚠️ Tidak proper | Hanya redirect ke `list2`, tidak konsisten dengan modul lain |
| `HomeController.php` | ⚠️ Minimal | Hanya render view, tidak ada logic dashboard |
| Dokumentasi | ❌ Tidak ada | `readme.txt` hanya berisi teks "test234" |

---

## 🔴 Kritik: Masalah Teknis

### 1. Code Duplication Ekstrem (PALING KRITIS)
**56 controller** dengan isi yang **95% identik** — hanya nama tabel dan kolom yang berbeda.

```
Lt2_blenderController.php  → 518 baris
Lt2_fbdController.php      → 451 baris
Lt2_ibcController.php      → ~450 baris
... (×50 lebih)
```

Ini hasil dari generate PHPRad yang tidak di-refactor. Konsekuensinya:
- Bug di 1 tempat = harus fix di 56 tempat
- Tidak maintainable jangka panjang
- Tambah mesin baru = harus generate + copy file baru

### 2. Tidak Ada Single Source of Truth untuk Schema Mesin
Setiap mesin punya field yang berbeda-beda, tapi tidak ada definisi terpusat. Kalau ada perubahan field, harus ubah di: controller, view add, view edit, view list, view detail, query search — semuanya manual.

### 3. Naming Convention Tidak Konsisten
```php
// Di satu controller:
"user_approve"    → nama kolom di satu tabel
"user_approved"   → di tabel lain
"user_approver"   → di tabel lain lagi
```

Ini menandakan tidak ada standar yang dijaga saat generate form per mesin.

### 4. SQL Hardcoded & Verbose
Query search di setiap controller mengulang `LIKE ?` untuk setiap kolom secara manual — ratusan baris repetitif yang rawan typo dan sulit di-maintain.

### 5. Password & Kredensial Tersimpan di Kode
```php
// config.php — JANGAN COMMIT INI KE PUBLIC REPO!
define("DB_PASSWORD", "Seman94t45!");
```
Ini security issue serius. Seharusnya pakai environment variable (`.env`).

### 6. `timthumb.php` — Library Berpotensi Berbahaya
File `helpers/timthumb.php` (51KB) adalah library resize gambar dari **tahun 2012** yang memiliki riwayat vulnerability keamanan serius. Perlu dicek apakah masih dipakai, dan kalau tidak — hapus.

### 7. Tidak Ada Error Handling yang Proper
Tidak ada try-catch yang konsisten di layer database. Kalau koneksi DB gagal, behavior tidak terprediksi.

### 8. Tidak Ada Logging Terstruktur
Tidak ada application-level logging yang terstruktur. Debugging di production akan sangat sulit.

---

## 🟡 Temuan Lain

- `Dockerfile` awalnya tidak mengaktifkan `mod_rewrite` — menyebabkan 500 error saat pertama run (sudah diperbaiki)
- `docker-compose.yml` masih pakai attribute `version` yang obsolete
- `vendor/` folder ada tapi `composer.json` hanya define 1 dependency (`dompdf`) — sangat minimal
- Tidak ada `.env` file — semua config hardcoded di `config.php`
- Tidak ada `.gitignore` — `vendor/`, `uploads/`, `logs/` seharusnya tidak di-commit

---

## 💡 Rekomendasi Perbaikan (Prioritas)

### 🔴 Prioritas 1 — Segera (Keamanan & Stabilitas)

1. **Pindahkan kredensial ke `.env`**
   ```php
   // Ganti config.php hardcoded menjadi:
   define("DB_PASSWORD", $_ENV['DB_PASSWORD']);
   ```

2. **Tambahkan `.gitignore`** untuk exclude `vendor/`, `uploads/`, `logs/`, file `.env`

3. **Audit `timthumb.php`** — kalau tidak dipakai, hapus langsung

---

### 🟡 Prioritas 2 — Jangka Menengah (Architecture)

4. **Implementasi `ApiController.php`**  
   Tambahkan REST API endpoint yang return JSON. Ini langkah pertama menuju modernisasi tanpa harus rebuild semua.
   ```
   GET  /api/machines         → list semua jenis mesin
   GET  /api/lt2_blender      → list data form blender
   POST /api/lt2_blender      → tambah data
   ```

5. **Buat README.md yang proper**  
   Dokumentasikan cara setup, struktur project, daftar mesin yang sudah ada.

6. **Config-driven machine definition**  
   Buat file JSON/config per mesin sebagai single source of truth untuk field, label, dan tipe input.

---

### 🟢 Prioritas 3 — Jangka Panjang (Modernisasi)

7. **Next.js Frontend di atas PHP API**  
   Setelah ApiController selesai, bangun dashboard Next.js yang consume API tersebut. PHP backend tetap jalan, Next.js hanya tampilan.
   ```
   [Next.js Dashboard] ←→ [PHP REST API] ←→ [MySQL]
   ```

8. **Refactor ke Generic Controller**  
   Ganti 56 controller menjadi 1 controller generik yang baca konfigurasi mesin dari file JSON/database.

9. **Migrasi File Storage ke MinIO**  
   Saat ini semua file upload tersimpan langsung di folder `uploads/` di hard disk server — tidak ada backup, rawan hilang kalau server rusak.  
   MinIO adalah object storage self-hosted (gratis) yang kompatibel dengan AWS S3, cocok untuk infrastruktur on-premise seperti kantor ini.
   ```
   Sekarang : upload → /var/www/html/uploads/photos/
   Setelah  : upload → MinIO server → bisa diakses via URL
   ```
   Keuntungan:
   - Backup otomatis bisa dikonfigurasi
   - File tidak hilang saat server/container restart
   - Scalable tanpa batas disk lokal

10. **Tambahkan monitoring & logging**  
    Implementasi structured logging agar bisa trace issue di production.

---

## 📊 Status Modul (Ringkasan)

| Kategori | Jumlah | Status |
|----------|--------|--------|
| Form mesin (CRUD lengkap) | ~50 | ✅ Done |
| Auth & User Management | 1 | ✅ Done |
| Role & Permission | 1 | ✅ Done |
| Approval system | 1 | ✅ Done |
| Export (PDF/Excel) | - | ✅ Done |
| API Layer | 1 | ❌ Belum |
| Panduan Pengisian | 1 | ❌ Belum |
| Dashboard/Home | 1 | ⚠️ Minimal |
| Dokumentasi | - | ❌ Tidak ada |

---

## 🎯 Saran untuk Magang (6 Bulan)

```
Bulan 1-2 : Pahami codebase → run local → bisa CRUD 1 form mesin
Bulan 3-4 : Isi ApiController.php → REST API untuk semua mesin
Bulan 5-6 : Bangun Next.js dashboard → consume dari API
```

**Di CV/laporan bisa ditulis:**
> "Developed REST API layer on top of legacy PHP system and built modern Next.js dashboard, enabling real-time monitoring of 50+ machine maintenance forms"

---

*Dokumen ini dibuat berdasarkan analisis statis kode pada: 2026-08-04*
