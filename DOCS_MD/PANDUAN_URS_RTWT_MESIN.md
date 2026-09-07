# PANDUAN PENGISIAN DOKUMEN URS (USER REQUIREMENT SPECIFICATION)
## SISTEM RED TAG WHITE TAG (RTWT) MESIN - PT. BINTANG TOEDJOE
**Format Resmi Mengacu pada Lampiran 5 - WI-QO-QO-1046.03**

---

## LEMBAR PERSETUJUAN DOKUMEN
*(Gunakan tabel persetujuan resmi di halaman 1 Google Docs: Disusun oleh QS Supervisor, Diperiksa/Disetujui oleh QS Manager, OT Lead, QO Div Head, dan Head of Analytics).*

---

## DAFTAR ISI
* A. PENDAHULUAN
  * 1. Latar Belakang
  * 2. Tujuan Penggunaan
  * 3. Ruang Lingkup
  * 4. Standar yang Diacu
  * (Gambar 1 Activity Diagram)
* B. OPERATIONAL REQUIREMENTS
  * 1. Functional Requirements (Tabel Spesifikasi Fungsional)
  * 2. Technical Requirements
  * 4. Non-functional attributes
    * 3.1 Disaster recovery & recovery time
    * 3.2 Back up, restore
    * 3.3 Server yang digunakan & data storage
  * 5. Software tools
  * 6. Kebutuhan Lain-lain (Dokumen Kualifikasi)
* C. GLOSARIUM
* D. PERSETUJUAN AKHIR

---

## A. PENDAHULUAN

### 1. Latar Belakang
Dalam rangka mendukung implementasi *Total Productive Maintenance* (TPM) dan menjaga keandalan mesin di Plant Pulogadung PT. Bintang Toedjoe, program *Autonomous Maintenance* (AM) telah diterapkan pada lini produksi Filling, Kemas, dan Compounding. Selama inspeksi AM berkala, temuan abnormalitas part atau kondisi tidak standar (*Not OK / NOK*) perlu ditindaklanjuti secara terstruktur menggunakan sistem *Red Tag White Tag* (RTWT).

Sebelumnya, pencatatan dan pelaporan bahaya/abnormalitas melalui program RTWT (K3LH, 5R, dan Mesin) masih memiliki kendala: pelaporan manual belum terdokumentasi terpusat, sulit memantau tindak lanjut perbaikan secara *real-time*, belum memiliki mekanisme verifikasi penutupan (*closure verification*) yang akuntabel, serta belum terintegrasi langsung dengan nomor *Work Request* (WR) teknik dan Form AM.

Seiring perkembangan teknologi informasi, diperlukan aplikasi digital RTWT yang *closed-loop*, transparan, dan terintegrasi untuk mempercepat alur penugasan perbaikan, meningkatkan kesadaran terhadap potensi bahaya/kerusakan, dan memastikan bahwa setiap tiket yang dibuka wajib diselesaikan hingga berstatus *Closed*.

### 2. Tujuan Penggunaan
Aplikasi RTWT Mesin dibuat dengan tujuan untuk:
* **a. Meningkatkan Efisiensi Proses:** Mempercepat proses pelaporan, penugasan massal (*Collective Assign* oleh Supervisor Teknik), dan verifikasi penutupan tiket secara online dan terstruktur.
* **b. Menjamin Alur Closed-Loop:** Memastikan setiap tiket temuan abnormalitas mesin wajib ditindaklanjuti dengan bukti perbaikan (*foto sesudah*) dan diverifikasi resmi oleh pihak berwenang sebelum dinyatakan *Closed* (sebagai dasar penilaian KPI Quality System).
* **c. Meningkatkan Akurasi dan Keamanan Data:** Menyediakan basis data terintegrasi yang menyimpan seluruh riwayat penanganan mesin secara aman, mencegah manipulasi data, dan terintegrasi via REST API dengan sistem Form AM.

### 3. Ruang Lingkup
Ruang lingkup sistem RTWT mencakup:
* **a. RTWT Mesin / Proses Produksi:** Penanganan temuan kelainan pada mesin produksi di area Filling, Kemas, dan Compounding Plant Pulogadung (terintegrasi dengan Form AM dan Nomor WR Teknik).
* **b. RTWT Dept Support (Ruang Office):** Penanganan temuan 5R, K3LH, dan fasilitas pendukung di area non-mesin/kantor (HR, Finance, QS Office, Gudang, dll).
* **c. Sistem Verifikasi Terpisah:** 
  * Temuan kategori **Mesin / Productivity** diverifikasi & ditutup oleh **Tim Teknik**.
  * Temuan kategori **5R & HSE** diverifikasi & ditutup oleh **Tim QS (Quality System)**.

### 4. Standar yang Diacu
Penerapan sistem mengacu pada regulasi dan standar berikut:
* a. Peraturan Pemerintah (PP) Nomor 50 Tahun 2012 tentang Penerapan SMK3
* b. ISO 45001:2018 (Occupational Health and Safety Management System)
* c. ISO 14001:2015 (Environmental Management System)
* d. ISO 9001:2015 (Quality Management System)
* e. Prinsip Integritas Data Farmasi (ALCOA+: Attributable, Legible, Contemporaneous, Original, Accurate).

---

### (Gambar 1: Activity Diagram RTWT Mesin & Integrasi Form AM)
*(Tempelkan screenshot diagram 3 swimlane dari file `activity_diagram_rtwt.html` pada bagian ini)*

---

## B. OPERATIONAL REQUIREMENTS

### 1. Functional Requirements
Persyaratan fungsional disusun dalam format tabel spesifikasi resmi:

| No | Tahapan | Informasi yang Dibutuhkan | Sumber Data | Requirement |
|:---|:---|:---|:---|:---|
| **001A** | **Login Page** | Username, Password | Database (`users`) | • Pengguna memasukkan username & password.<br>• Username bersifat unik.<br>• Password minimal 8 karakter dengan fitur show/hide.<br>• Fitur enkripsi password standar industri. |
| **001B** | **Access Level & Role** | Role ID, Hak Akses | Database (`roles`) | • **Operator / Observer:** Input tiket baru & melihat status laporan miliknya.<br>• **Supervisor Teknik:** Fitur *Search* & *Collective Assign* tiket mesin ke teknisi.<br>• **Teknisi Maintenance:** Menerima tugas, input tindakan perbaikan & upload foto sesudah.<br>• **Verificator Teknik:** Verifikasi & penutupan (*Close*) tiket kategori Mesin/Productivity.<br>• **Verificator QS:** Verifikasi & penutupan (*Close*) tiket kategori 5R & HSE.<br>• **Admin:** Pengelolaan user dan master data line/mesin. |
| **002A** | **Form Input RTWT Mesin** | Site, Area, Line, Mesin, Bagian, Kategori Tag, Korelasi, Kategori Ketidaksesuaian, Deskripsi, Foto Sebelum | Database (`masters`, `rtwt_mesin`) | • Dropdown Site: Pulogadung, Cikarang.<br>• Dropdown Area: Filling, Kemas, Compounding.<br>• Pilihan Kategori Tag: Red Tag (Teknik), White Tag (Operator), Blue Tag.<br>• Korelasi Tag: Productivity (Mesin), 5R, HSE.<br>• Dropdown Kategori Ketidaksesuaian: Otomatis berubah sesuai korelasi yang dipilih (*Chained dropdown*).<br>• Wajib melampirkan Foto Bukti Temuan (*Foto Sebelum*). |
| **002B** | **Integrasi Form AM (REST API)** | Payload JSON (Area, Line, Mesin, Part, Issue NOK, Foto, No WR) | Form AM System | • Menyediakan endpoint API aman (`POST ?route=rtwt_mesin/api_sync`).<br>• Setiap temuan inspeksi AM berstatus *NOK* otomatis membentuk tiket Red Tag di sistem RTWT dengan Nomor WR tertera. |
| **003A** | **Collective Assign by Category** | Filter Kategori, Checkbox Tiket, Sub-Dept & Teknisi Tujuan | Database (`rtwt_mesin`, `users`) | • Supervisor / Assigner Teknik memfilter & mengklasifikasikan tiket berdasarkan kategori kendala.<br>• **Disposisi ke Sub-Dept Terkait:** Assigner mendeliver penugasan ke sub-departemen yang sesuai: **Maintenance (MTC / Mesin)**, **Utility**, atau **Bengkel Sparepart**.<br>• Fitur *Collective Assign*: Mencentang beberapa tiket sekaligus dan menugaskannya secara massal.<br>• Status tiket otomatis berubah menjadi `Assigned`. |
| **003B** | **Pengerjaan & Tindakan Perbaikan** | Tindakan Korektif, Foto Sesudah | User Input | • Teknisi yang ditugaskan mengubah status menjadi `In Progress`.<br>• Teknisi mencatat uraian tindakan perbaikan dan **wajib mengunggah Foto Bukti Sesudah Perbaikan (*Foto After*)**. |
| **004A** | **Verifikasi & Penutupan Closed-Loop** | Status Verifikasi, Catatan Verifikator | Database (`rtwt_mesin`) | • **Kategori Mesin / Productivity:** Diverifikasi & di-close oleh **Tim Teknik**.<br>• **Kategori 5R & HSE:** Diverifikasi & di-close oleh **Tim QS**.<br>• **Validasi Sistem:** Tiket TIDAK BISA ditutup jika foto sesudah atau tindakan perbaikan masih kosong.<br>• Tiket yang telah `Closed` berstatus *read-only* (tidak dapat diedit lagi untuk kepatuhan audit). |
| **005A** | **Menu & Navigasi** | Role User | Database (`roles`) | • Menu Sidebar sebelah kiri bersifat responsif.<br>• Menu ditampilkan sesuai hak akses role (Teknik melihat menu penugasan, QS melihat menu verifikasi 5R/HSE). |
| **006A** | **Dashboard & Penilaian KPI QS** | Filter Bulan, Tahun, Line, Area, Rasio Status | Database (`rtwt_mesin`) | • Filter data berdasarkan Bulan, Tahun, Area, dan Kategori.<br>• Menampilkan metrik utama penilaian QS: **Persentase Tiket Closed (%)**.<br>• Menampilkan grafik frekuensi temuan per Line dan per Mesin.<br>• Tidak menggunakan metrik MTBF/MTTR (karena fokus sistem adalah ketuntasan penanganan Tagging). |
| **007A** | **Sistem Notifikasi** | Alert Web / Email Event | System Event | • Fase operasional saat ini: *In-App Notification* (alert badge pada menu MyTask).<br>• Target implementasi dokumen URS: Pengiriman notifikasi otomatis via **Email** ke teknisi/verifikator saat tiket dibuat/ditugaskan. |

---

### 2. Technical Requirements
* **Arsitektur Aplikasi:** Berbasis Web (*Web-based Application*) dengan pola Model-View-Controller (MVC).
* **Bahasa Pemrograman:** PHP (Native/PHPRad Framework) versi 7.4 - 8.x.
* **Database Management System (DBMS):** MySQL / MariaDB versi 10.x.
* **Front-End Styling:** HTML5, CSS3, Bootstrap 4, JavaScript / jQuery.
* **Web Server:** Apache HTTP Server (port standar 80 / 443 HTTPS).
* **Protokol Integrasi:** REST API berbasis JSON melalui HTTP POST.

---

### 4. Non-Functional Attributes

#### 3.1 Disaster Recovery & Recovery Time
* **Recovery Time Objective (RTO):** Maksimal 4 jam sistem dapat dipulihkan kembali jika terjadi kegagalan server.
* **Recovery Point Objective (RPO):** Maksimal data hilang adalah 24 jam terakhir (sesuai siklus backup database harian).

#### 3.2 Back Up & Restore
* **Jadwal Backup:** Dilakukan otomatis setiap hari (*daily automated backup*) pada pukul 00:00 WIB oleh tim IT Infrastructure Bintang Toedjoe.
* **Lokasi Penyimpanan:** File backup disimpan di server terpisah (*secondary storage / NAS*).
* **Uji Restore:** Prosedur uji pemulihan data (*restore testing*) dilakukan minimal 1 kali per tahun untuk memastikan integritas file backup.

#### 3.3 Server yang Digunakan & Data Storage
* **Hosting:** Server internal (*On-Premise Server*) Data Center PT Bintang Toedjoe Pulogadung.
* **Kapasitas Penyimpanan (Storage):** Minimal 100 GB HDD/SSD untuk penyimpanan data transaksional dan direktori upload foto kompresi.
* **Spesifikasi Server:** Minimal 4 Core CPU, 8 GB RAM, OS Linux / Windows Server.

---

### 5. Software Tools
* Web Browser modern yang didukung: Google Chrome (versi 100+), Microsoft Edge, Mozilla Firefox.
* Text Editor / IDE: VS Code / Antigravity IDE.
* Database Client: phpMyAdmin / DBeaver / HeidiSQL.

---

### 6. Kebutuhan Lain-Lain (Dokumen Kualifikasi)
Sesuai prosedur validasi sistem komputerisasi farmasi (GAMP 5):
1. **User Requirement Specification (URS):** Dokumen spesifikasi kebutuhan pengguna (dokumen ini).
2. **Functional Specification (FS) & Design Specification (DS):** Dokumen arsitektur teknis dan ERD database.
3. **Instalation Qualification (IQ) & Operational Qualification (OQ):** Protokol pengujian instalasi dan verifikasi fungsional fitur sebelum Go-Live.
4. **User Acceptance Testing (UAT):** Pengujian penerimaan akhir bersama perwakilan tim Operator, Teknik, dan QS Pulogadung.

---

## C. GLOSARIUM
* **RTWT (Red Tag White Tag):** Sistem penandaan visual untuk mengidentifikasi dan menindaklanjuti ketidaksesuaian/kelainan di area kerja atau mesin.
* **Red Tag (Tag Merah):** Label penandaan kelainan mesin yang memerlukan keahlian dan tindakan perbaikan oleh tim Teknik (*Maintenance*).
* **White Tag (Tag Putih):** Label penandaan kelainan ringan yang dapat diselesaikan langsung oleh Operator lini produksi.
* **Closed-Loop:** Alur kerja sistem di mana setiap tiket yang dibuka wajib diselesaikan, diverifikasi dengan bukti konkret, dan ditutup secara resmi oleh verifikator berwenang.
* **Form AM (Autonomous Maintenance):** Sistem lembar kerja pemeliharaan mandiri oleh operator lini untuk inspeksi rutin mesin (CIL: Cleaning, Inspection, Lubrication).
* **Collective Assign:** Fitur penugasan beberapa tiket abnormalitas secara serentak kepada seorang teknisi berdasarkan kelompok kategori kendala mesin.
* **ALCOA+:** Standar integritas data industri farmasi (*Attributable, Legible, Contemporaneous, Original, Accurate*).
* **QS (Quality System):** Departemen pemastian mutu dan kepatuhan sistem standar di PT. Bintang Toedjoe.

---

## D. PERSETUJUAN AKHIR
Dokumen URS ini dinyatakan disetujui sebagai acuan resmi perancangan, pengembangan, dan pengujian sistem RTWT Mesin Plant Pulogadung PT. Bintang Toedjoe. Setiap perubahan terhadap kebutuhan di kemudian hari wajib melalui prosedur *Change Management* / *Change Control*.
