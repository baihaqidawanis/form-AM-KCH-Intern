# 🛡️ Panduan Prosedur Backup & Restore (Kepatuhan URS & CSV)

Dokumen ini melengkapi persyaratan **URS Form Autonomous Maintenance Poin 4.1.2 dan 4.2**:
- **Regular Backup & Retensi 5 Tahun**
- **Simulasi Uji Coba Backup & Restore Sebelum Go-Live (Computerized System Validation - CSV)**

---

## 📁 Daftar Script yang Tersedia di Folder `scripts/`:

1. **[`backup_form_am.bat`](./backup_form_am.bat)**:
   - Script untuk melakukan backup otomatis database PostgreSQL (`pg_dump` format compressed `.dump`) dan folder foto profil (`uploads/`).
   - File backup disimpan di: `D:\BACKUP_FORM_AM\`.
   - Dapat dipasang pada **Windows Task Scheduler** agar berjalan otomatis setiap malam (Daily jam 01:00 AM).

2. **[`restore_form_am_test.bat`](./restore_form_am_test.bat)**:
   - Script untuk melakukan simulasi uji restore ke database uji (`form_am_test_restore`).
   - Digunakan untuk pembuktian CSV di hadapan QA / Auditor bahwa file backup dapat memulihkan sistem secara utuh 100%.

---

## ⏰ Cara Menjadwalkan di Windows Task Scheduler:
1. Buka **Task Scheduler** di Windows Server.
2. Klik **Create Basic Task...**
3. Masukkan Nama: `Auto Backup Form AM`.
4. Trigger: **Daily** (Pukul 01:00:00).
5. Action: **Start a program** -> Browse file `C:\xampp\htdocs\form-am\scripts\backup_form_am.bat`.
6. Klik **Finish**.
