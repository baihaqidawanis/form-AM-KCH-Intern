# 🎤 Panduan Membuat Presentasi — Form AM

> Outline & talking points buat bikin PPT presentasi project ini (ke atasan, tim QA/GMP, atau serah terima ke tim server/ops). Ditulis sebagai markdown biar gampang di-convert ke slide manapun — isinya sudah dikelompokkan per-slide, tinggal salin poin-poinnya.

## Struktur yang Disarankan (10-14 slide)

### Slide 1 — Judul
**Digitalisasi Form Autonomous Maintenance (AM) — Site Pulogadung**
Subjudul: dari form kertas ke sistem digital terintegrasi, sesuai standar GMP (URS approved 10 Agustus 2026).

### Slide 2 — Latar Belakang / Masalah
- Pencatatan AM sebelumnya manual/kertas — rawan hilang, lambat direkap, sulit ditelusuri riwayatnya.
- Kebutuhan sesuai dokumen resmi CR-PR-PR-1203.00 dan standar GMP (CPOB 2024 Aneks 7, CPOTB 2021 Bab 5.26 — *Computerized System Validation*).
- Target: 1 sistem yang mengcover 3 kategori mesin (Filling, Packaging, Compounding), sesuai role kewenangan yang jelas, dan siap diaudit.

### Slide 3 — Cakupan Akhir (Angka Besar)
- **17 modul mesin** aktif: 5 Filling, 6 Packaging, 6 Compounding.
- **4 role pengguna** (Administrator, Manager, Supervisor, Staff/Operator) sesuai matrix kewenangan URS.
- **1 kerangka kode terpusat** (`BaseMachineController`) — bukan 17 modul yang masing-masing punya kode sendiri.
- **38 test otomatis + checklist manual** — regresi bisa dicek dalam hitungan detik/menit, bukan testing manual berjam-jam tiap kali ada perubahan.

*(Screenshot yang bagus buat slide ini: Home dashboard menampilkan 3 tab kategori dengan card tiap mesin.)*

### Slide 4 — Alur Kerja Aplikasi
Diagram sederhana: Operator isi form → (semua OK → auto-approve sistem) / (ada NOK → approval manual Manager/Supervisor/Administrator) → tersimpan dengan jejak audit → bisa di-export (PDF/Word/CSV/Excel) kapan saja.

*(Screenshot: halaman `add` sebuah mesin, dan halaman `view` yang menampilkan badge OK/NOK.)*

### Slide 5 — Kepatuhan terhadap URS
- Semua requirement Rank I (Penting) di URS — **selesai**, dicek satu-per-satu ke kode & dites langsung (bukan asumsi).
- Highlight fitur GMP-critical: auto-approve/manual-approve, audit trail, lockout akun, session timeout, format tanggal standar, "printed by" di laporan (prinsip ALCOA).
- Rujuk ke `KEPATUHAN_URS.md` untuk detail tabel per-poin.

### Slide 6 — Keamanan & Kontrol Akses
- RBAC 4 role, dicek di level backend (bukan cuma sembunyikan tombol di tampilan).
- Operator/Manager/Supervisor hanya bisa mengubah data yang mereka buat sendiri (kecuali Administrator).
- Beberapa celah keamanan ditemukan & ditutup selama pengembangan (stored-XSS di form kendala, dll) — tunjukkan sebagai bukti proses QA yang serius, bukan disembunyikan.

### Slide 7 — Testing & Jaminan Kualitas
- Setiap perubahan kode diverifikasi hidup (submit form beneran, cek database, bukan cuma baca kode).
- 38 test otomatis (PHPUnit) + 1 test browser otomatis (Playwright) — mengecek RBAC, alur approval, export, keamanan (anti-XSS), dan skenario sesi/timeout.
- Checklist manual buat skenario yang butuh mata manusia (tampilan, UX, device produksi asli).

### Slide 8 — Tantangan Teknis yang Diselesaikan
Pilih 2-3 cerita paling berkesan buat storytelling (jangan semua — pilih yang paling relate dengan audiens):
- Migrasi database dari MySQL ke PostgreSQL, termasuk migrasi data & pemetaan role lama.
- Bug kritis yang ditemukan lewat proses testing (bukan lewat laporan user) — nunjukin proses QA proaktif.
- Refactor besar 17 modul dari kode terduplikasi jadi 1 kerangka terpusat — dampak ke maintainability jangka panjang.

### Slide 9 — Status Saat Ini & Yang Masih Berjalan
- Lingkungan development: **selesai & teruji**.
- Server production: **belum dimigrasi** — perlu keputusan bisnis (mapping role lama, jadwal downtime) sebelum go-live. Rujuk `DEPLOYMENT.md`.
- Beberapa mesin (Illapak 1-12, Unifill B) masih menunggu data konten asli (nama part, foto, SOP) dari user.

### Slide 10 — Rencana ke Depan
- Deployment ke production (lihat fase-fase di `DEPLOYMENT.md`).
- Melengkapi konten mesin yang masih placeholder.
- (Opsional, roadmap jangka panjang) REST API layer & dashboard modern — lihat `TECHNICAL_OVERVIEW.md`.

### Slide 11 — Penutup / Q&A
Ringkas 3 pesan utama:
1. Sistem sudah lengkap secara fungsional & sesuai URS.
2. Kualitas dijaga lewat testing otomatis yang bisa dijalankan siapa saja, kapan saja.
3. Langkah selanjutnya adalah deployment — butuh keputusan & koordinasi dari tim, bukan lagi soal kode.

## Tips Presentasi

- **Untuk audiens non-teknis** (manajemen/QA): fokus ke Slide 1-6, 9-11. Jangan masuk ke detail kode.
- **Untuk audiens teknis** (tim IT/developer lain): tambahkan detail dari `TECHNICAL_OVERVIEW.md` (arsitektur `BaseMachineController`, pola tabel per-mesin) dan `TESTING.md`.
- **Screenshot yang paling kuat**: Home dashboard (kesan "banyak & lengkap"), halaman `view` dengan badge OK/NOK (kesan "detail & rapi"), dan tabel matrix RBAC dari `KEPATUHAN_URS.md` (kesan "terkontrol & sesuai standar").
- **Jangan sembunyikan bug yang ditemukan & diperbaiki** — dalam konteks GMP/audit, riwayat temuan-dan-perbaikan yang terdokumentasi justru memperkuat kredibilitas proses QA, bukan melemahkan.
