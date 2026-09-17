# Manual QA Form AM

Isi `PASS`, `FAIL`, atau `BLOCKED` pada setiap kotak. Jalankan pada server testing dengan source terbaru dan database yang sudah menjalankan `database/postgres/update.sql`.

## Data dan bukti

- Run ID: `MANUAL_QA_<tanggal>_<inisial>`
- Ambil screenshot halaman/error, URL, waktu, role, dan ID record untuk setiap FAIL.
- Gunakan foto JPG kecil khusus QA untuk NOK.
- Jangan memakai data operasional nyata bila server akan di-reset.

## Preflight

- [ ] Aplikasi terbuka dan login bekerja.
- [ ] PostgreSQL terhubung; tidak ada error schema/kolom `created_by_user_id`.
- [ ] Folder `uploads/photos/nok` dapat ditulis oleh Apache.
- [ ] `SIGNATURE_HMAC_KEY` tersedia; halaman QR verification dapat dibuka.
- [ ] Tersedia akun Administrator, Manager, Supervisor, Staff, dan Operator.
- [ ] Tersedia mesin aktif serta minimal satu part aktif pada setiap modul yang diuji.

## A. Akun, login, dan role

| ID | Uji | Langkah ringkas | Expected |
|---|---|---|---|
| A-01 | Registrasi | Register email/NIK baru, lalu login sebelum dan sesudah aktivasi | Pending ditolak login; setelah aktivasi menjadi Operator dan dapat login |
| A-02 | Lockout | Salah password tiga kali, lalu coba password benar | Akun Blocked dan tetap ditolak |
| A-03 | Session revocation | Browser A login Operator; Browser B Admin ubah role atau Blocked; Browser A refresh lalu submit | Browser A langsung kehilangan akses/harus login ulang |
| A-04 | Area RBAC | Login Staff/Operator area Filling lalu buka URL mesin area lain | Mesin area sendiri dapat dibuka; area lain 403 |
| A-05 | Matrix approval | Buat NOK pending; login Admin, Manager, lalu Supervisor dan coba Approval | Ketiga role dapat approve/reject; Staff/Operator ditolak |
| A-06 | Ownership | Operator A membuat checklist, ubah username A, lalu edit checklist lama; buat user B dengan username lama | A tetap dapat edit; B tidak dapat edit |
| A-07 | Email unik | Register email yang sama dengan variasi huruf besar/kecil | Pendaftaran kedua ditolak |
| A-08 | Idle session | Biarkan tab terbuka tanpa aktivitas lalu gunakan kembali | Tidak ada forced timeout; aksi tetap mengikuti status akun terbaru |

## B. Checklist dan NOK

| ID | Uji | Langkah ringkas | Expected |
|---|---|---|---|
| B-01 | Submit OK | Isi semua part OK, submit, refresh, logout/login, buka View dan report | Satu record persisten; approval System/Approved |
| B-02 | NOK validation | Pilih NOK tanpa detail/foto, submit; lalu lengkapi detail/tag/foto | Submit pertama ditolak tanpa record/file orphan; kedua menjadi pending approval |
| B-03 | Upload | Upload JPG valid, lalu coba ekstensi/MIME/ukuran tidak valid | JPG valid tersimpan; file tidak valid ditolak |
| B-04 | Edit | Edit record belum TTD dari OK menjadi NOK dan isi alasan perubahan | Approval kembali pending dan detail NOK tersimpan |
| B-05 | Duplicate non-shift | Submit ulang mesin/tanggal sama | Record duplikat ditolak |
| B-06 | Shift | Submit Shift 1 dan Shift 2 pada tanggal sama; ulang Shift 1 | Shift berbeda boleh; Shift 1 kedua ditolak |
| B-07 | Delete | Admin hapus checklist NOK belum TTD | Parent/detail/snapshot/foto terhapus; daftar tidak lagi memuat record |
| B-08 | Re-entry shift | Hapus checklist Shift 1 belum TTD, lalu buat Shift 1 baru untuk tanggal sama | Form Shift 1 baru dapat disimpan dengan ID baru |

## C. Approval, TTD, QR, dan concurrency

| ID | Uji | Langkah ringkas | Expected |
|---|---|---|---|
| C-01 | Pending approval | Buat NOK lalu coba TTD sebelum approval | TTD ditolak hanya selama ada checklist `approval IS NULL` |
| C-02 | TTD kosong | Pilih mesin/periode tanpa checklist, TTD Operator lalu Supervisor | TTD diperbolehkan; report tetap kosong tanpa error |
| C-03 | TTD lifecycle | Record approved; Operator TTD, scan QR; Supervisor TTD, scan QR | Urutan wajib Operator lalu SPV; QR valid |
| C-04 | TTD lock | Setelah TTD, coba add, edit, dan delete checklist periode sama | Semua ditolak sampai TTD dibatalkan |
| C-05 | Cancel | Operator coba batal ketika TTD SPV ada; SPV batalkan; lalu Operator batalkan | Urutan cancellation dipaksa dan alasan tercatat |
| C-06 | Race | Dua browser: satu TTD, satu submit/edit record pada periode sama secara bersamaan | Hanya satu proses yang berhasil sesuai lock; tidak ada record berubah setelah TTD |
| C-07 | Invalid machine | Panggil/ubah parameter TTD ke mesin ID tidak ada | Ditolak; signature tidak dibuat |

## D. Report, master, dan operasi mesin

| ID | Uji | Langkah ringkas | Expected |
|---|---|---|---|
| D-01 | Daily report | Buat OK/NOK dan shift, buka report harian | Data, shift, status, operator, dan NOK sesuai record |
| D-02 | Period report/export | Buka periode 1 dan 2; export PDF/XLSX/CSV/Word bila tersedia | File valid dan data/signature/QR konsisten |
| D-03 | Historical part | Buat record, Takeout/ubah Master Part, buka report record lama | Record lama memakai snapshot part historis |
| D-04 | Machine status | Deactivate mesin, coba submit, reactivate, submit ulang | Submit ditolak saat nonaktif dan boleh saat aktif |
| D-05 | QR integrity | Setelah TTD, buka QR; lalu coba perubahan yang seharusnya diblokir | QR tetap valid; perubahan tidak lolos |

## E. Security smoke

- [ ] URL filter valid pada list Users/Roles/Tag/Approval/Audit Log tetap berfungsi.
- [ ] URL dengan nama kolom acak atau karakter SQL menghasilkan 404/error aman, tanpa database error detail.
- [ ] Direct URL add/edit/delete/approval dari role yang tidak berhak menghasilkan 403.
- [ ] Form POST tanpa CSRF token ditolak.
- [ ] Kendala yang berisi `<script>` tampil sebagai teks, tidak dieksekusi.

## F. Smoke 21 modul mesin

Untuk setiap modul: login role yang sesuai, buka List, Add, View/Report; submit satu form OK bila test data tersedia. Tandai hasil.

| # | Modul | List | Add OK | View | Daily/Period Report | Catatan/ID Record |
|---|---|---|---|---|---|---|
| 1 | SIG | [ ] | [ ] | [ ] | [ ] | |
| 2 | JOYEA | [ ] | [ ] | [ ] | [ ] | |
| 3 | Ilapak 1-2 | [ ] | [ ] | [ ] | [ ] | |
| 4 | Ilapak 3-12 | [ ] | [ ] | [ ] | [ ] | |
| 5 | Unifill B | [ ] | [ ] | [ ] | [ ] | |
| 6 | Chimei | [ ] | [ ] | [ ] | [ ] | |
| 7 | Temach | [ ] | [ ] | [ ] | [ ] | |
| 8 | Check Weigher | [ ] | [ ] | [ ] | [ ] | |
| 9 | Conveyor SIG | [ ] | [ ] | [ ] | [ ] | |
| 10 | Jihcheng | [ ] | [ ] | [ ] | [ ] | |
| 11 | Jinsung 1-4 | [ ] | [ ] | [ ] | [ ] | |
| 12 | Jinsung 5 | [ ] | [ ] | [ ] | [ ] | |
| 13 | Best Pack | [ ] | [ ] | [ ] | [ ] | |
| 14 | Cosmec | [ ] | [ ] | [ ] | [ ] | |
| 15 | FBD Jaw Chuan | [ ] | [ ] | [ ] | [ ] | |
| 16 | FBD Glatt | [ ] | [ ] | [ ] | [ ] | |
| 17 | Supermixer | [ ] | [ ] | [ ] | [ ] | |
| 18 | Granulator | [ ] | [ ] | [ ] | [ ] | |
| 19 | Storage Tank | [ ] | [ ] | [ ] | [ ] | |
| 20 | Storage Tank Tetrapak | [ ] | [ ] | [ ] | [ ] | |
| 21 | Mixing Tank | [ ] | [ ] | [ ] | [ ] | |

## Exit criteria

- Tidak ada P0/P1 FAIL.
- Semua A-C PASS, atau setiap FAIL memiliki ticket dan keputusan release.
- Smoke 21 modul selesai; tidak ada 500, warning PHP, atau akses role salah.
- Report/export dan QR pada data QA dapat dibuka.
- Bukti screenshot, URL, role, dan record ID tersimpan untuk setiap FAIL.
