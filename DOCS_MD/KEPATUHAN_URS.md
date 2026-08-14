# ✅ Kesesuaian Program dengan URS — Form Autonomous Maintenance

> Cross-check langsung ke kode — setiap baris di bawah dites live (curl/Playwright, akun sungguhan, query database), bukan dibaca dari dokumentasi. Dibandingkan terhadap [URS Form Autonomous Maintenance](<./URS_Form%20Autonomous%20Maintenance%20(1).md>) (dokumen GMP resmi, ditandatangani 23 Juni 2026, approved 10 Agustus 2026, mengacu CPOB 2024 Aneks 7 & CPOTB 2021 Bab 5.26 soal *Computerized System Validation*).

> Requirement bertanda **Rank: I (Penting)** artinya "ketersediaan fitur tersebut dapat mempengaruhi keberlangsungan/kelancaran proses bisnis" — beda kelas dari sekadar "kurang fitur". Ini yang biasa jadi temuan kalau app-nya diaudit tim QA/regulator.
>
> **Update terakhir: 14 Agustus 2026**

## Ringkasan

| Area | Status |
|---|---|
| Registrasi Akun (URS 1.1) | ✅ 11/11 |
| Login & Keamanan Sesi (URS 1.2–1.3) | ✅ Lengkap, termasuk bug auto-logout yang ketahuan & difix |
| Role & Kewenangan Akses (URS 2.1–2.2, 3.1) | ✅ 17/17 modul, sesuai matrix |
| Alur Approval (URS 4.1–4.2) | ✅ 17/17 modul |
| Report & Export (URS 5.1) | ✅ |
| ALCOA (URS D.1) | ✅ |
| Di luar ranah kode (infra) | 2 item, perlu tim infra/ops |

**Semua requirement Rank I (Penting) yang ranahnya development sudah selesai.** Sisa cuma 1 item konten (Rank D, prioritas rendah) dan kebijakan infrastruktur (backup/retensi) yang bukan sesuatu yang bisa dicek dari kode.

---

## 1. Registrasi Akun (URS 1.1)

> *"Untuk pendaftaran akun baru, dapat melakukan registrasi baru dengan menginput nama, email, username, area, mesin, password, user role, dan profile picture... username menggunakan NIK dan password menggunakan huruf kapital, huruf kecil, angka dan spesial karakter dengan ketentuan password 8 karakter... dapat menghubungi administrator agar dapat diaktivasi."*

| # | Requirement | Status | Bukti |
|---|---|---|---|
| 1 | 8 field wajib: nama, email, username, area, mesin, password, user role, profile picture | ✅ | Semua field ada di form registrasi, termasuk upload foto profil (dropzone) dan dropdown user role |
| 2 | Username wajib format NIK (Nomor Induk Karyawan — angka saja, 8 digit) | ✅ | Divalidasi server (`is_valid_nik_username()` di `helpers/Functions.php`, regex `^[0-9]{8}$`), dites otomatis 4 role sekaligus (`RegistrationTest`): `admin`/`AB123456` ditolak (bukan angka murni), `12345678` diterima. Hint UI + `minlength`/`maxlength`/`pattern` juga ada. **Update 14 Agustus 2026**: awalnya diimplementasi huruf+angka campur (asumsi awal, gak eksplisit tertulis di URS), dikoreksi ke angka murni 8 digit setelah dikonfirmasi user — NIK di sini maksudnya Nomor Induk Karyawan pabrik, bukan NIK KTP (16 digit) |
| 3 | Password: kapital + kecil + angka + spesial, min 8 karakter | ✅ | Divalidasi server (`is_valid_password_complexity()`), dites live: 6 karakter ditolak |
| 4 | Akun baru berstatus menunggu, harus dihubungi admin buat aktivasi | ✅ | Dites live end-to-end (otomatis, `tests/Feature/RegistrationTest.php`): submit registrasi → `account_status` otomatis `Pending` → login berikutnya ditolak dengan pesan "contact system administrator" |

**Kesimpulan: 11/11 poin URS 1.1 terpenuhi.**

---

## 2. Login & Keamanan Sesi (URS 1.2–1.3)

| Requirement | Status | Bukti |
|---|---|---|
| Salah password 3× berturut-turut → akun terkunci (`Blocked`) | ✅ | Counter reset cuma pas login sukses. Diverifikasi otomatis (`tests/Feature/LockoutTest.php`, pakai akun throwaway biar gak ganggu akun lain) |
| Sesi berakhir setelah 30 menit idle, notifikasi muncul | ✅ | Idle-timer JS (peringatan 5 menit sebelum habis) + server-side backstop. Draft form disimpan otomatis ke browser sebelum logout paksa. Diverifikasi Playwright (`tests/e2e/session-timeout.spec.js`) |

**Bug ketahuan & difix pas nulis test Playwright**: JS auto-logout (`doTimeout()` di `main_layout.php`) manggil `index/logout` **tanpa CSRF token** — request-nya ditolak (`Csrf::cross_check()` → 403) SEBELUM `session_destroy()` sempet jalan. Artinya timeout **kelihatan** jalan (browser redirect) tapi session **beneran tetap aktif** di background — pelanggaran langsung ke requirement "sesi berakhir setelah 30 menit". Fixed: token disisipkan ke URL logout sama kayak link Logout manual. Ini bug yang cukup lama gak ketahuan karena testing manual sebelumnya cuma verifikasi "modal muncul + draft ke-save", bukan "session-nya beneran mati".

---

## 3. Role & Kewenangan Akses (URS 2.1–2.2, 3.1)

Dicek detail per role terhadap teks URS 3.1 (lebih reliabel dibanding tabel checkbox Tabel 4 yang hasil OCR-nya berantakan).

**Matrix akses final (per role, terverifikasi live & otomatis via `tests/Feature/RbacTest.php`):**

| Aksi | Administrator | Manager | Supervisor | Staff/Operator |
|---|:---:|:---:|:---:|:---:|
| Lihat daftar & detail AM | ✅ | ✅ | ✅ | ✅ |
| Isi form AM baru | ✅ | ❌ | ✅ | ✅ |
| Approval (ubah status) | ✅ | ✅ | ✅ | ❌ |
| Edit data — punya sendiri | ✅ | ✅ | ✅ | ✅ |
| Edit data — punya orang lain | ✅ | ❌ | ❌ | ❌ |
| Hapus record | ✅ | ✅ | ✅ | ❌ |
| Menu Users | ✅ | ❌ | ✅ | ❌ |
| Audit Trail | ✅ | ❌ | ❌ | ❌ |

Pembatasan "punya sendiri" dicek di level controller (`user_create` record dibandingkan user yang login), bukan cuma disembunyikan di tampilan — dan sebaliknya, **tombol yang bakal ditolak backend juga disembunyikan di UI** (bukan cuma ditolak pas diklik). Administrator satu-satunya yang bebas dari batasan kepemilikan (URS 1.5: *"hanya administrator yang memiliki akses penuh"*).

**Gap yang sempat ditemukan & difix** (sebelum matrix final di atas): Operator awalnya tidak bisa mengedit data submission miliknya sendiri (tombol muncul tapi ditolak backend), dan Manager awalnya tidak bisa mengedit data maupun menghapus form sama sekali — padahal URS menyebutkan eksplisit keduanya berhak, dibatasi hanya pada data yang pernah mereka tangani sendiri untuk `edit_data`.

---

## 4. Alur Approval (URS 4.1–4.2)

| Requirement | Status | Bukti |
|---|---|---|
| Semua part OK → otomatis approved by system | ✅ | Berlaku di 17/17 modul termasuk SIG. Diverifikasi otomatis (`tests/Feature/MachineCrudTest.php`, `ApprovalFlowTest.php`) |
| Ada kendala (NOK) → tidak auto-approve, wajib approval manual | ✅ | Diverifikasi otomatis: submit 1 part NOK → status tetap kosong menunggu approval manual sesuai role berwenang, approve/reject manual keduanya dites |
| Kolom "User Approve" (siapa/apa yang approve) tampil terpisah di list, sesuai referensi URS (Gambar 23) | ✅ | **Gap ditemukan & difix**: kolom "Approval Oleh" sebelumnya gak ditampilkan di `list2.php` (datanya udah ke-fetch dari awal, cuma gak dirender) — ditambahin ke 17/17 modul, diverifikasi live nunjukin "System" buat record auto-approve |

---

## 5. Report & Export (URS 5.1)

| Requirement | Status | Bukti |
|---|---|---|
| Export PDF dengan histori lengkap | ✅ | PDF asli (`%PDF-1.7`) di semua 17 modul (dompdf v3, di-upgrade dari versi 2020 yang gak kompatibel PHP 8.2) |
| "Printed by" sesuai akun yang mencetak | ✅ | Footer laporan bersama (`report_layout.php`), berlaku otomatis ke semua modul |
| Export Word/CSV/Excel | ✅ | **Bug ditemukan & difix**: CSV & Excel export CRASH TOTAL kalau list mesin lagi kosong (0 record) — `current(array())` balikin `false`, `array_keys(false)` meledak di PHP 8. Ke-cascade lebih parah karena halaman error-nya sendiri ikut nyoba re-export. Fixed + extension PHP `zip` yang ternyata gak aktif dari awal (Excel gak akan pernah jalan) diaktifkan. Diverifikasi otomatis (`tests/Feature/ExportFormatsTest.php`) |
| "Report bulanan" (URS bagian Interfaces) | ✅ (lebih fleksibel) | URS nulis data "menjadi report bulanan". Implementasi sekarang gak dibatasi bulanan — filter `date_from`/`date_to` bebas rentang tanggal apapun (termasuk 1 bulan penuh) baru di-export, jadi requirement-nya tetap terpenuhi (bisa generate laporan bulanan) plus lebih fleksibel dari yang diminta |

---

## 6. ALCOA (URS D.1)

| Requirement | Status | Bukti |
|---|---|---|
| Attributable — jejak audit tercatat otomatis | ✅ | Audit Trail mencatat setiap aksi CRUD (add/edit/edit_data/delete — `view`/`list` sengaja tidak dicatat, noise reduction) |
| Legible — tampilan jelas, gak ambigu | ✅ | Kualitatif (bukan sesuatu yang dicek otomatis lewat test) — badge warna OK/NOK, label Indonesia yang jelas per field, form tervalidasi (`novalidate`+custom validation) biar user gak submit data ambigu/kosong |
| Contemporaneous — tanggal/jam gak bisa diubah, format DD/MM/YYYY konsisten, timezone GMT+7 | ✅ | `created_at` selalu diisi server, format DD/MM/YYYY merata di 17/17 modul. Timezone `Asia/Jakarta` (GMT+7) di-set eksplisit (`DEFAULT_TIMEZONE` di `config.php`), bukan default server yang bisa beda-beda |
| Original — laporan gak bisa diedit, tercantum waktu & pencetak | ✅ | Export PDF read-only by design |
| Accurate — teks kendala operator gak boleh berubah/rusak pas ditampilkan | ✅ | **Stored-XSS ditemukan & difix**: teks kendala di-echo mentah tanpa `htmlspecialchars()` di 14/17 `view.php` + semua `edit_data.php` — user internal manapun (termasuk Staff/Operator lewat form isian normal) bisa nyuntik `<script>` yang tereksekusi buat siapapun yang buka record itu. Diverifikasi otomatis (`tests/Feature/XssEscapingTest.php`) |
| Accurate — search & data master gak boleh error | ✅ | **Bug ditemukan & difix**: search di halaman Users/Roles/Tag/Approval CRASH (Error 500) — kolom integer (`id_user`, `user_role_id`, dst) di-`LIKE` tanpa `CAST`, Postgres gak izinin implisit (beda dari MySQL) |

---

## 7. Di Luar Ranah Kode Aplikasi

| Requirement | Status | Keterangan |
|---|---|---|
| Backup rutin, retensi 5 tahun, disaster recovery 2×24 jam | 🔵 Infra | Kebijakan & jadwal infrastruktur server — perlu disiapkan tim infra/IT saat deploy production, lihat `DEPLOYMENT.md` |
| Panduan Pengisian AM interaktif langkah-per-langkah | 🟡 Konten | Halaman ada, isi masih 1 gambar statis. Rank D (Diinginkan), bukan Rank I — prioritas rendah, menunggu konten dari tim |

---

## Sudah Sesuai (Kabar Baik, Bawaan Sejak Awal)

| Requirement | Bukti |
|---|---|
| **Reset password via email** | `PasswordmanagerController.php` — generate token + kirim email, validasi expiry |
| **My Account self-service** (lihat/edit profil, ganti email) | `AccountController.php` |
| **Export Users** (Print/PDF/Word/CSV/Excel) | Pola sama dengan modul mesin |
| **Navigasi Home (horizontal tab) vs AM Mesin (sidebar vertikal)** | Sesuai Gambar 5-6 URS |
| **Timestamp gak bisa diedit operator** | `created_at` selalu di-set server-side, gak ada field buat operator ubah jam/tanggal |

---

## Sengaja TIDAK Dikerjain (dicek ke URS, ternyata di luar scope)

Dua ide yang sempat didiskusikan (Admin CRUD buat konten part, checkbox UI custom-atur role permission) — dicek ke teks URS asli, ternyata **memang di luar scope**, bukan cuma "belum sempat":

- **Admin CRUD buat konten part** (Metode/Alat/Standard/Durasi/Pelaksanaan) — URS 1.5 cuma minta Administrator bisa kelola *data master* (area, tag, korelasi, klasifikasi, kategori, role), itu sudah ada. Konten SOP per-part beda kelas, dan URS eksplisit bilang perubahan prosedur inspeksi jadi tanggung jawab **pihak pengembang lewat kode**, bukan fitur self-service admin.
- **Checkbox UI custom-atur role permission** — Tabel 4 URS matrix-nya fixed 4 role, gak ada requirement admin bisa ubah-ubah kewenangan sendiri. Yang sekarang ada (`libs/ACL.php`, hardcode per role) sudah persis matching. Menghidupkan infra `role_permissions` yang mati justru berisiko admin bisa geser akses jadi gak sesuai dokumen approved — potensi temuan audit.

---

*Sumber: [URS Form Autonomous Maintenance](<./URS_Form%20Autonomous%20Maintenance%20(1).md>), ditandatangani 23 Juni 2026. Metodologi: cross-check kode + verifikasi live/otomatis (curl, Playwright, PHPUnit — lihat `TESTING.md`) per klaim, bukan asumsi dari baca dokumen. Histori teknis lengkap tiap temuan ada di `EVALUATION.md`.*
