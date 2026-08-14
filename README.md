# Form AM — Site Pulogadung

Sistem digitalisasi Form Autonomous Maintenance (AM) — pencatatan pemeriksaan/perawatan mesin harian pabrik, menggantikan form kertas. Dibangun sesuai [URS resmi](<./DOCS_MD/URS_Form%20Autonomous%20Maintenance%20(1).md>) (approved 10 Agustus 2026, mengacu standar GMP CPOB 2024 & CPOTB 2021).

## Ringkasan

- **17 modul mesin** di 3 kategori: Filling, Packaging, Compounding.
- **4 role pengguna** (Administrator, Manager, Supervisor, Staff/Operator) dengan kewenangan sesuai matrix URS.
- **Auto-approve** kalau semua part kondisi OK, **approval manual** kalau ada kendala (NOK).
- Export laporan PDF/Word/CSV/Excel, Audit Trail otomatis, session timeout, lockout akun.
- Status saat ini: **selesai & teruji di lingkungan development, belum di-deploy ke server production** — lihat [DOCS_MD/DEPLOYMENT.md](./DOCS_MD/DEPLOYMENT.md).

## Stack

PHP Native (bukan framework) + PostgreSQL 17 (dev) + Apache. Detail lengkap arsitektur ada di [DOCS_MD/TECHNICAL_OVERVIEW.md](./DOCS_MD/TECHNICAL_OVERVIEW.md).

## Menjalankan di Lokal

1. Copy `.env.example` jadi `.env`, isi kredensial database.
2. `composer install` (dependency PHP) dan `npm install` (dependency testing, opsional).
3. Pastikan Apache & PostgreSQL menyala, arahkan document root ke folder ini.
4. Jalankan migration di `database/migrations/*.sql` secara berurutan (atau restore dari dump yang sudah ada).
5. Buka `http://localhost/form-am/` — login pakai akun yang tersedia (lihat [DOCS_MD/TESTING.md](./DOCS_MD/TESTING.md) untuk akun testing lokal).

## Dokumentasi

Semua dokumentasi detail ada di folder [DOCS_MD/](./DOCS_MD/):

| Dokumen | Isinya |
|---|---|
| [URS_Form Autonomous Maintenance (1).md](<./DOCS_MD/URS_Form%20Autonomous%20Maintenance%20(1).md>) | Dokumen requirement resmi (sumber kebenaran) — juga tersedia versi `.pdf` dan lampiran `.docx` |
| [KEPATUHAN_URS.md](./DOCS_MD/KEPATUHAN_URS.md) | Checklist kesesuaian program terhadap tiap poin URS, dicek langsung ke kode |
| [TECHNICAL_OVERVIEW.md](./DOCS_MD/TECHNICAL_OVERVIEW.md) | Arsitektur aplikasi, pola tabel database, cara menambah mesin baru |
| [DEPLOYMENT.md](./DOCS_MD/DEPLOYMENT.md) | Rencana & langkah deployment ke server production (belum dieksekusi) |
| [TESTING.md](./DOCS_MD/TESTING.md) | Cara menjalankan test otomatis (PHPUnit + Playwright) dan checklist manual |
| [EVALUATION.md](./DOCS_MD/EVALUATION.md) | Log evaluasi/histori pengembangan — ringkasan eksekutif di bagian atas, detail teknis per-sesi di bawahnya |
| [PANDUAN_PRESENTASI.md](./DOCS_MD/PANDUAN_PRESENTASI.md) | Outline untuk membuat presentasi/PPT tentang project ini |

`CLAUDE.md` di root berisi context kerja untuk sesi AI-assisted development — bisa diabaikan kalau tidak relevan.

## Untuk yang Mengambil Alih (Handover)

1. Baca `README.md` ini dulu, lalu `TECHNICAL_OVERVIEW.md` untuk paham arsitektur.
2. Kalau mau deploy ke production: baca `DEPLOYMENT.md` — ada beberapa keputusan bisnis yang perlu diambil sebelum mulai (mapping role, jadwal downtime, dll), **bukan langsung technical execution**.
3. Sebelum mengubah kode apapun, jalankan test dulu (`TESTING.md`) untuk memastikan baseline masih hijau.
4. Kalau perlu menambah mesin baru, ikuti panduan di `TECHNICAL_OVERVIEW.md` bagian "Cara Nambah Mesin Baru" — pola sudah konsisten di 17 modul yang ada.
