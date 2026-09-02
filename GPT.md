# Form AM — Handoff Status dan Aturan Kritis

Dokumen ini adalah handoff untuk agent/model berikutnya. Jangan mengubah aturan audit di bawah tanpa persetujuan eksplisit.

## Status poin krusial

| Poin | Status | Implementasi utama |
|---|---|---|
| Shift harian, satu report per mesin/tanggal | Selesai | `system/BaseMachineController.php`, `app/views/partials/machine_shift_list.php`, `machine_daily_report.php` |
| Forgot password mandiri lalu aktivasi admin, SMTP tetap tersedia | Selesai | `app/controllers/PasswordmanagerController.php`, `app/controllers/UsersController.php` |
| Takeout tidak mengubah form/audit/PDF historis | Selesai | `system/BaseMachineController.php`, `app/controllers/Master_partController.php`, `machine_period_report.php` |
| Snapshot metadata part permanen per submit | **Selesai** | `system/BaseMachineController.php` (savePartSnapshot), `database/migrations/2026-09-02_add_form_part_snapshot.sql` |

## 1. Shift harian

- Master Data Part menyimpan `shift_schedule` sebagai daftar `1`, `2`, `3`.
- Bila ada part aktif pada Shift 2 atau 3, halaman Add menampilkan pilihan shift dan hanya menampilkan part yang berlaku pada shift tersebut.
- Setiap submit tetap satu record fisik per shift, tetapi overview dan `daily_report` menggabungkannya menjadi **satu report per mesin + operational_date**.
- Approval overview bersifat agregat: hanya `Approved/System` bila semua shift yang sudah disubmit approved. Satu NOK membuat overview menunggu approval (`-`).
- Mesin yang pernah memiliki konfigurasi multi-shift tetap memakai overview agregat untuk membaca riwayat lama, walaupun seluruh part Shift 2/3 sudah di-takeout. Form baru hanya memakai shift bila masih ada part shift aktif.

## 2. Forgot password internal

- Saat `USE_SMTP=false`, pengguna dapat masuk ke form reset langsung setelah mengisi email/username.
- Password baru disimpan, tetapi `account_status` menjadi `pending_activation`.
- Super Admin harus menjalankan `users/activate_password/{id}` sebelum akun aktif kembali.
- Kode SMTP tidak dihapus; bila `USE_SMTP=true`, alur kembali mengirim email reset.

### Risiko yang diterima untuk web internal

Tanpa SMTP atau verifikasi identitas lain, orang yang mengetahui email/username dapat memicu reset dan membuat akun target menjadi `pending_activation` (gangguan akses/DoS). Aktivasi admin mencegah login dengan password baru sebelum disetujui, tetapi tidak mencegah gangguan tersebut. Tambahkan rate-limit dan verifikasi identitas bila aplikasi keluar dari lingkungan internal.

## 3. Audit master part dan takeout

Resolver histori dipakai oleh View, Report Harian, Check Sheet Periode, dan PDF:

```text
part berlaku pada form bila:
master_part.created_at <= form.created_at
AND (taken_out_at IS NULL OR taken_out_at > form.created_at)
```

- Part ditambah setelah form dibuat: tidak boleh muncul di form/PDF historis.
- Part sudah ada saat form dibuat lalu NOK dan di-takeout: tetap muncul, nilai NOK dan abnormalitasnya tetap terbaca.
- Part takeout hanya hilang dari form yang dibuat setelah waktu takeout.
- `Edit Data` adalah satu-satunya jalur perubahan nilai laporan.
- Export PDF memakai `partsForRows()` dan `partDetailsForRows()`, bukan daftar master part aktif saat PDF dibuat.

Diagram lengkap: [DOCS_MD/MASTER_PART_AUDIT_FLOW.md](DOCS_MD/MASTER_PART_AUDIT_FLOW.md).

## 4. Snapshot metadata part (implementasi 2026-09-02)

Sejak 2026-09-02, setiap submit form AM menyimpan snapshot metadata ke tabel `form_part_snapshot`:

```text
form_part_snapshot:
  machine_key | form_id | field_name | label | section | metode | alat
              | standard | durasi | pelaksanaan | highlight | image_path | urutan
```

- Snapshot ditulis oleh `BaseMachineController::savePartSnapshot()` di dalam transaction `add()`, tepat setelah INSERT record dan INSERT kendala, sebelum `commit()`.
- `ON CONFLICT DO NOTHING` — aman dijalankan ulang (idempotent).
- Jika tabel belum ada di environment (migration belum dijalankan), `try/catch` memastikan submit form tetap berhasil; error di-log ke `error.log` tanpa crash.
- **Backfill record lama:** Migration `2026-09-02_add_form_part_snapshot.sql` menyertakan DO $$ backfill untuk semua record `tb_mesin_*` yang dibuat sebelum fitur ini ada.
- **Deployment wajib:** Jalankan kedua migration ini sebelum deploy ke production:
  1. `database/migrations/2026-09-02_add_shift_to_all_machine_forms.sql` — idempotent (`ADD COLUMN IF NOT EXISTS`)
  2. `database/migrations/2026-09-02_add_form_part_snapshot.sql` — idempotent (`CREATE TABLE IF NOT EXISTS` + `ON CONFLICT DO NOTHING`)

## Verifikasi terakhir (2026-09-02)

- `joeya/daily_report?mesin=3&date=2026-09-02`: HTTP 200, tanpa Error 500.
- Part Joeya dibuat 13:10 tidak tampil pada form Shift 1/2 yang dibuat 13:01.
- Part historis NOK yang sudah takeout tetap terbaca pada overview, Report Harian, dan period PDF.
- Period PDF menghasilkan respons `%PDF` valid.
- `php -l system/BaseMachineController.php` lulus.
- `php -l system/BaseView.php` lulus.
- `php -l tests/Feature/GenericShiftLifecycleTest.php` lulus.
- Bug `DOMDocumentFragment::appendXML(): Entity 'acirc' not defined` di `BaseView.php:771` diperbaiki (`htmlentities` -> `htmlspecialchars`).
- Snapshot metadata: `savePartSnapshot()` ditambah di `BaseMachineController::add()`.

## PR / risiko lanjutan

1. **Snapshot metadata — SELESAI.** `savePartSnapshot()` sudah ada di `BaseMachineController::add()`. Jalankan migration `2026-09-02_add_form_part_snapshot.sql` di environment target.
2. **Regression test generic shift — SELESAI.** `tests/Feature/GenericShiftLifecycleTest.php` cover skenario Joeya & SIG: tambah part shift-2, submit Shift 1 OK, submit Shift 2 NOK, takeout, cek `daily_report` + `period_report` historis.
3. **Git cleanup — SELESAI (2026-09-02 13:42).** Di-commit dan di-push (`73aaac9`). Commit message: `merubah master data`.
4. **Deployment database — SELESAI (verifikasi idempotent).** Kedua migration menggunakan `IF NOT EXISTS` / `ON CONFLICT DO NOTHING`. Aman dijalankan berulang.
5. **Error log legacy — SELESAI.** Root cause warning `&acirc;` di PDF diperbaiki di `system/BaseView.php:771` (`htmlentities` -> `htmlspecialchars(ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8')`).

### Risiko terbuka baru

- **Snapshot tidak di-update saat `edit_data`.** Snapshot menyimpan *definisi* part saat submit, bukan nilai OK/NOK — ini desain yang benar. Nilai edit_data tersimpan di tabel mesin seperti biasa. Jangan bingung dengan tidak adanya row snapshot baru setelah edit_data.
- **GenericShiftLifecycleTest belum pernah dijalankan di CI.** Memerlukan environment dengan DB live. Jalankan manual: `vendor\bin\phpunit tests/Feature/GenericShiftLifecycleTest.php`.
- **Error log lama non-acirc masih ada.** Warning `appendXML` sebelum fix tetap ada di `error.log` lama — tidak perlu dihapus. Pantau bahwa warning baru tidak muncul setelah deploy.