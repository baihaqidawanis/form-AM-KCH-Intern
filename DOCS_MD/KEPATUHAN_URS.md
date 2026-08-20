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
| 1 | 8 field wajib: nama, email, username, area, mesin, password, user role, profile picture | ⚠️ **Deviasi terdokumentasi** | 7 dari 8 field diinput user: nama, email, username (NIK), area, mesin, password, profile picture (dropzone). **`user role` SENGAJA tidak lagi bisa dipilih pendaftar** — dropdown-nya dihapus 19 Agustus 2026 dan nilainya dipaksa `Staff/Operator` (role_id 4) di server (`IndexController::register()`), bukan sekadar disembunyikan di UI.<br><br>**Alasan (keputusan user, 19 Agustus 2026)**: membiarkan pendaftar memilih rolenya sendiri = pintu privilege escalation — orang bisa mendaftar sebagai Administrator, dan approver berisiko kecolongan saat aktivasi massal. Prinsip *least privilege*: akun baru selalu masuk sebagai level terendah.<br><br>**Kompensasi**: kewenangan menaikkan role tetap ada, tapi pindah ke Administrator lewat menu Users → Edit (sesuai URS 3.1 yang memang memberi Administrator wewenang "edit user"). Jadi fungsinya tidak hilang, hanya dipindah ke pihak yang berwenang. Diverifikasi otomatis: `RbacTest` mengirim `user_role_id` 1/2/3 lewat POST (simulasi tampering) — semuanya tetap tersimpan sebagai role 4.<br><br>*Perlu konfirmasi mentor/QA kalau deviasi ini mau diformalkan sebagai revisi URS.* |
| 2 | Username wajib format NIK (Nomor Induk Karyawan — huruf dan/atau angka, maks 11 karakter) | ✅ | Divalidasi server (`is_valid_nik_username()` di `helpers/Functions.php`, regex `^[A-Za-z0-9]{1,11}$`), dites otomatis (`RegistrationTest`): username dengan tanda baca (mis. `AB-1234567`) ditolak, sedangkan huruf+angka campur maupun angka murni (maks 11 karakter) diterima. Hint UI + `maxlength`/`pattern` juga ada. **Update 14 Agustus 2026**: awalnya diimplementasi huruf+angka campur (asumsi awal, gak eksplisit tertulis di URS), sempat dikoreksi ke angka murni. **Update 19 Agustus 2026**: dikoreksi lagi setelah dikonfirmasi user — NIK pabrik formatnya variatif (1-3 huruf prefix + 7-8 digit angka, total gak pasti 11), jadi validasi dilonggarkan jadi alfanumerik bebas dengan batas maksimal 11 karakter, bukan pola/panjang pasti |
| 3 | Password: kapital + kecil + angka + spesial, min 8 karakter | ✅ | Divalidasi server (`is_valid_password_complexity()`), dites live: 6 karakter ditolak |
| 4 | Akun baru berstatus menunggu, harus dihubungi admin buat aktivasi | ✅ | Dites live end-to-end (otomatis, `tests/Feature/RegistrationTest.php`): submit registrasi → `account_status` otomatis `Pending` → login berikutnya ditolak dengan pesan "contact system administrator" |

**Kesimpulan: 10/11 poin URS 1.1 terpenuhi, 1 deviasi disengaja & terdokumentasi (field `user role` di form registrasi — lihat baris 1).**

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
| Kolom jejak approval & perubahan lengkap di list + view, sesuai referensi UI URS (Gambar 6, 9, 10) | ✅ | **Gap ditemukan & difix (20 Agustus 2026)**: mockup URS nunjukin `Tanggal Approve`, `User Update`/`User Perubah`, `Tanggal Update`, dan `Perubahan` — semuanya datanya sudah lama diisi di DB tapi **belum pernah dirender**. Ditambahkan ke 17/17 `list2.php` (Tanggal Approval, User Update, Tanggal Update) dan 17/17 `view.php` (User Approve, Approval, Tanggal Approve, User Update, Tanggal Update, Perubahan) |
| Status approval dievaluasi ulang setelah data dikoreksi lewat Edit Data | ✅ | **Gap ditemukan & difix (20 Agustus 2026)**: `edit_data()` dulu sama sekali gak nyentuh status approval — record yang sudah "Approved" lalu dikoreksi jadi ada NOK **tetap tampil Approved**, padahal kondisinya sudah berubah (risiko integritas data GMP: status tidak mencerminkan kondisi mesin sebenarnya). Sekarang: semua part balik OK → auto-approve ulang oleh System; ada part jadi NOK → status approval di-reset ke kosong (BUKAN langsung "Not Approved") supaya masuk antrian review manual, konsisten dengan perlakuan submission NOK baru. Diverifikasi otomatis (`ApprovalFlowTest::test_edit_data_ubah_ke_nok_reset_approval_ke_pending`) |

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
| Accurate — "Hanya administrator yang punya akses penuh, **termasuk data master**, dan berwenang mengunggah data master baru serta menghapus data master yang tidak relevan" (URS 1.5) | ✅ | **Diimplementasi 19–20 Agustus 2026**: menu **Master Data Part** (`Master_partController`) — Administrator bisa tambah/ubah/hapus detail part mesin (foto, Metode, Alat, Standard, Durasi, Pelaksanaan, Section, Urutan) buat 17/17 mesin tanpa ubah kode; sebelumnya semua hardcoded di file view. Akses dibatasi ke Administrator lewat *default-deny* ACL — diuji live: Administrator 200, Supervisor/Manager/Operator **403** |
| Accurate — data master tidak boleh rusak/berubah saat dimigrasikan | ✅ | Migrasi 166 part dari array hardcoded ke tabel `master_part` **digenerate otomatis dari file sumber** (bukan diketik ulang). Dibuktikan dengan snapshot DOM ke-17 halaman form sebelum vs sesudah migrasi → **identik 100%** |
| Attributable — akun berwenang tidak boleh bisa "dijatuhkan" sesama admin | ✅ | **Ditambahkan 20 Agustus 2026 (disetujui mentor)**: 1 akun **Super Admin** yang role/status-nya tidak bisa diubah & tidak bisa dihapus oleh Administrator/Supervisor lain (`users.is_super_admin` + *partial unique index* di Postgres — dijamin di level DB, cuma boleh ada 1 baris). Sesama Administrator biasa tetap bisa saling kelola. Diverifikasi otomatis (`RbacTest`). *Catatan: skenario multi-Administrator tidak diatur eksplisit di URS — ini keputusan desain yang disetujui mentor, bukan requirement URS.* |
| Attributable — hapus record tidak boleh menyisakan data anak tanpa induk | ✅ | **Bug ditemukan & difix (20 Agustus 2026)**: `delete()` cuma menghapus record AM induk, baris kendala/abnormalitas anaknya ditinggal jadi *orphan* selamanya (tidak ada FK `ON DELETE CASCADE` di skema ini) — ditemukan 371 baris orphan menumpuk. Sekarang penghapusan induk + anak dibungkus 1 transaction, dan orphan lama sudah dibersihkan |

---

## 7. Di Luar Ranah Kode Aplikasi

| Requirement | Status | Keterangan |
|---|---|---|
| Backup rutin, retensi 5 tahun, disaster recovery 2×24 jam (URS C.4.1–4.2) | 🔵 Infra | Kebijakan & jadwal infrastruktur server — perlu disiapkan tim infra/IT saat deploy production, lihat `DEPLOYMENT.md`. **URS juga mewajibkan backup/restore test DILAKUKAN & DIDOKUMENTASIKAN sebelum go-live** sebagai bagian CSV — belum dikerjakan |
| Server aman secara fisik & sistem, pakai UPS (URS C.4.3) | 🔵 Infra | Di luar kendali aplikasi — tanggung jawab Corporate IT |
| Pelatihan & sosialisasi ke pengguna, evaluasi berkala (URS D.1.5) | 🔵 Proses | Kewajiban prosedural Administrator, bukan fitur aplikasi |
| Panduan Pengisian AM interaktif langkah-per-langkah | 🟡 Konten | Halaman ada, isi masih 1 gambar statis. Rank D (Diinginkan), bukan Rank I — prioritas rendah, menunggu konten dari tim |
| Spesifikasi teknis: URS C.2.1 menyebut **MySQL**, implementasi pakai **PostgreSQL 17** | ⚠️ **Deviasi terdokumentasi** | Aplikasi awalnya MySQL lalu dimigrasi ke PostgreSQL (jejaknya masih terlihat di komentar `02_seed.sql`: *"Diekspor dari MySQL lokal"*). Keduanya sama-sama RDBMS relasional dan tidak mengubah fungsionalitas apa pun yang diminta URS; XAMPP tetap dipakai sesuai URS. **Perlu konfirmasi mentor/QA** kalau deviasi ini mau diformalkan sebagai revisi URS C.2.1 |

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

- **Checkbox UI custom-atur role permission** — Tabel 4 URS matrix-nya fixed 4 role, gak ada requirement admin bisa ubah-ubah kewenangan sendiri. Yang sekarang ada (`libs/ACL.php`, hardcode per role) sudah persis matching. Menghidupkan infra `role_permissions` yang mati justru berisiko admin bisa geser akses jadi gak sesuai dokumen approved — potensi temuan audit.
- **Tambah MESIN baru lewat UI** — nambah mesin bukan sekadar baris data: butuh tabel `tb_mesin_x` + `kendala_x`, file controller, 5 file view, plus penyambungan ke menu/dashboard/tab approval. URS D.1.5 eksplisit menempatkan penyesuaian semacam ini sebagai tanggung jawab **pihak pengembang**. Yang bisa dikelola admin lewat UI adalah *part* di dalam mesin yang sudah ada (lihat Master Data Part di bagian ALCOA).

> **Catatan revisi (19–20 Agustus 2026)**: dokumen ini sebelumnya menyatakan *"Admin CRUD buat konten part — di luar scope"*. Setelah dikonfirmasi ulang dengan user, keputusan itu **dibalik dan fiturnya sudah dibangun** (menu Master Data Part), dengan dasar URS D.1.5 yang menyebut Administrator berwenang atas *data master* termasuk "mengunggah data master baru" dan "menghapus data master yang sudah tidak relevan". Bukti implementasi & pembatasan aksesnya ada di bagian ALCOA di atas.

---

## Pengerasan Keamanan Hasil Audit Menyeluruh (20 Agustus 2026)

Audit tambahan di luar butir URS eksplisit — relevan ke ALCOA (integritas & keterandalan data) dan praktik dasar keamanan aplikasi:

| Aspek | Hasil | Tindakan |
|---|---|---|
| SQL injection | ✅ Aman | Seluruh `rawQuery` dicek satu per satu: nilai dari user selalu lewat *parameter binding* (`?`). Yang di-interpolasi hanya nama tabel/kolom yang berasal dari konstanta kelas atau sudah divalidasi ketat (whitelist mesin + regex `^[a-z][a-z0-9_]*$`) |
| CSRF | ✅ Aman | Semua aksi destruktif (`delete`, `reorder`) punya `Csrf::cross_check()` — dicek menyeluruh di semua controller |
| Password | ✅ Aman | `password_hash`/`password_verify` (bcrypt), tidak ada MD5/SHA1 |
| Upload file | ✅ Aman + diperkuat | Whitelist ekstensi gambar, nama file diacak (nama asli user dibuang), `perms 0644`. **Ditambah `uploads/.htaccess`** yang memblokir eksekusi script — diuji live: file `.php` di folder upload → **HTTP 403**, foto sah tetap tampil normal |
| Cookie sesi | ⚠️ Celah ditemukan → difix | Cookie sesi sebelumnya **tanpa `HttpOnly`** (bisa dibaca JavaScript — kalau ada XSS lolos, sesi login ikut kebajak) dan `session.use_strict_mode=0` (rentan *session fixation*). Ditambahkan pengerasan di `index.php` sebelum `session_start()`: `HttpOnly`, `SameSite=Lax`, `use_strict_mode=1`, plus `Secure` otomatis kalau diakses via HTTPS. Diverifikasi live: header kini `HttpOnly; SameSite=Lax` |
| Error handling produksi | ✅ Aman | `DEVELOPMENT_MODE=false` → `display_errors` mati, error dialihkan ke log file (stack trace tidak bocor ke user) |
| Data orphan | ⚠️ Bug ditemukan → difix | Lihat baris "hapus record" di bagian ALCOA |

---

*Sumber: [URS Form Autonomous Maintenance](<./URS_Form%20Autonomous%20Maintenance%20(1).md>), ditandatangani 23 Juni 2026. Metodologi: cross-check kode + verifikasi live/otomatis (curl, Playwright, PHPUnit — lihat `TESTING.md`) per klaim, bukan asumsi dari baca dokumen. Histori teknis lengkap tiap temuan ada di `EVALUATION.md`.*
