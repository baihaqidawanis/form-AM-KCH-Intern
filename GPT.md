# Form AM — Handoff Status dan Aturan Kritis

Dokumen ini adalah handoff untuk agent/model berikutnya. Jangan mengubah aturan audit di bawah tanpa persetujuan eksplisit.

## Status tiga poin krusial

| Poin | Status | Implementasi utama |
|---|---|---|
| Shift harian, satu report per mesin/tanggal | Selesai | `system/BaseMachineController.php`, `app/views/partials/machine_shift_list.php`, `machine_daily_report.php` |
| Forgot password mandiri lalu aktivasi admin, SMTP tetap tersedia | Selesai | `app/controllers/PasswordmanagerController.php`, `app/controllers/UsersController.php` |
| Takeout tidak mengubah form/audit/PDF historis | Selesai | `system/BaseMachineController.php`, `app/controllers/Master_partController.php`, `machine_period_report.php` |

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

## Verifikasi terakhir

- `joeya/daily_report?mesin=3&date=2026-09-02`: HTTP 200, tanpa Error 500.
- Part Joeya dibuat 13:10 tidak tampil pada form Shift 1/2 yang dibuat 13:01.
- Part historis NOK yang sudah takeout tetap terbaca pada overview, Report Harian, dan period PDF.
- Period PDF menghasilkan respons `%PDF` valid.
- `php -l system/BaseMachineController.php` lulus.

## PR / risiko lanjutan

1. **Snapshot metadata belum permanen.** Nilai OK/NOK dan keberadaan part sudah historis, tetapi jika admin mengubah `label`, `section`, `metode`, `standard`, atau foto master part, tampilan metadata laporan lama masih dapat berubah. Solusi audit paling kuat: simpan snapshot metadata part per record saat submit.
2. **Regression test generic shift belum lengkap.** Tambahkan test HTTP untuk mesin non-Illapak seperti Joeya/SIG: tambah part shift, submit Shift 1 OK, submit Shift 2 NOK, takeout, lalu cek View/Report/PDF.
3. **Working tree sangat kotor dan belum dipisahkan commit.** Jangan menjalankan reset/checkout massal. Audit perubahan lalu pecah commit per fitur sebelum deployment.
4. **Deployment database.** Pastikan migration `database/migrations/2026-09-02_add_shift_to_all_machine_forms.sql` dan schema/seed PostgreSQL ikut diterapkan di environment target.
5. **Error log legacy.** Ada warning/deprecation lama di `error.log`; bukan penyebab alur tiga poin di atas, tetapi perlu dibersihkan sebelum production formal.