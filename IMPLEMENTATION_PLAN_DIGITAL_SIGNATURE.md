# Rencana Implementasi: Sistem Tanda Tangan Digital & Paraf Form AM

Dokumen ini merangkum konsep, arsitektur teknis, dan rencana implementasi sistem tanda tangan digital dan paraf untuk form Autonomous Maintenance (Form AM). Dokumen ini disiapkan sebagai referensi dan bahan diskusi meeting.

---

## 1. Pemetaan 3 Zona Tanda Tangan / Paraf

Berdasarkan format standar Form Autonomous Maintenance (PT Bintang Toedjoe / Kalbe Consumer Health), terdapat **3 zona tanda tangan**:

```
+-----------------------------------------------------------------------------------------+
|                                                          |   Diperiksa Oleh             |
|                                                          |   [ QR CODE TTD DIGITAL ]    |
| SPV / Fasilitator: [ PARAF SPV ]                         |   (Validasi Kriptografis)    |
+-----------------------------------------------------------------------------------------+
| ... (Tabel Standar Pembersihan & Pengecekan Mesin) ...                                  |
+-----------------------------------------------------------------------------------------+
| Paraf Pelaksana: | 1 | 2 | 3 | 4 | 5 | ... | 31 | (Otomatis dari Akun Operator Login)  |
+-----------------------------------------------------------------------------------------+
```

### Zona 1: Paraf Pelaksana (Bawah Tabel, Kolom Tanggal 1–31)
* **Kebutuhan Lapangan:** Kotak tanggal berukuran sempit (~25–35px). Operator melakukan inspeksi setiap hari per shift.
* **Pendekatan yang Direkomendasikan (Hybrid: Canvas + Fallback Inisial):**
  * **Canvas di Profil User:** Saat registrasi atau di menu profil akun, operator menggambar parafnya 1 kali (tersimpan sebagai PNG transparan).
  * **Fallback Inisial Otomatis:** Jika operator belum menggambar paraf, sistem otomatis menghasilkan inisial nama (misal: `BHD`, `AH-01`) dengan font khusus agar tetap rapi dan terbaca.
  * **Tempel Otomatis:** Saat operator submit check sheet hari itu, sistem mencatat `user_id` dan otomatis menempelkan paraf/inisial di kolom tanggal terkait. Operator **tidak perlu** menggambar ulang setiap hari.

### Zona 2: Paraf SPV / Fasilitator (Header Tengah)
* **Kebutuhan:** Tanda persetujuan atau monitoring dari supervisor/fasilitator area atas pelaksanaan AM di periode berjalan.
* **Mekanisme:** Menggunakan aset paraf profil akun SPV saat melakukan review di sistem.

### Zona 3: Kotak "Diperiksa Oleh" (Header Kanan Atas — QR Code Kriptografis)
* **Kebutuhan:** Legalitas dan otorisasi resmi dokumen bulanan untuk keperluan audit internal/eksternal (CPOB / BPOM / ALCOA+).
* **Mekanisme:** Menggunakan **Digital Signature berbasis Kriptografi & QR Code** (mirip konsep di `secure-financial-report-sharing`).
  * Saat SPV/Manager klik "Approve Dokumen", sistem membuat **Hash SHA-256** dari lembar check sheet tersebut.
  * Hash tersebut ditandatangani menggunakan **RSA Private Key** akun verifikator.
  * Sistem membuat **QR Code** otomatis di kotak "Diperiksa Oleh".
  * Saat lembar form discan via kamera smartphone oleh auditor/atasan, akan terbuka halaman verifikasi resmi:
    * ✅ **Status:** Dokumen Asli & Terverifikasi
    * **Penandatangan:** Nama Lengkap & NIK
    * **Waktu Tanda Tangan:** Tanggal & Jam (Presisi detik)
    * **Integritas:** Data dipastikan tidak pernah dimanipulasi setelah ditandatangani.

---

## 2. Keunggulan & Biaya Implementasi

1. **100% Gratis (Rp 0,-):**
   * Tidak perlu langganan pihak ketiga berbayar (seperti PrivyID, DocuSign, Peruri) karena sistem diperuntukkan bagi validasi dan audit trail internal perusahaan.
   * Modul kriptografi menggunakan ekstensi bawaan PHP: **OpenSSL** (sudah tersedia di XAMPP dan server Linux).
   * Generator QR Code menggunakan pustaka PHP murni (*open source* & dapat berjalan tanpa koneksi internet luar).
2. **Efisiensi Tinggi di Lantai Produksi:**
   * Menghilangkan proses paraf manual di kertas yang rawan tercecer atau dipalsukan.
   * Operator menghemat waktu karena paraf otomatis terisi sesuai login akun.
3. **Anti-Manipulasi (Tamper-Proof):**
   * Jika ada perubahan nilai inspeksi di database setelah dokumen di-TTD, sistem verifikasi QR Code akan mendeteksi ketidaksesuaian hash dan memberi tanda peringatan.

---

## 3. Rencana Arsitektur Database (PostgreSQL)

```sql
-- 1. Tambahan pada tabel users untuk menyimpan aset paraf dan kunci
ALTER TABLE users ADD COLUMN IF NOT EXISTS paraf_image TEXT;        -- Base64 PNG transparan dari canvas
ALTER TABLE users ADD COLUMN IF NOT EXISTS user_initials VARCHAR(10); -- Inisial fallback (misal: BHD)
ALTER TABLE users ADD COLUMN IF NOT EXISTS public_key_pem TEXT;      -- Kunci publik RSA
ALTER TABLE users ADD COLUMN IF NOT EXISTS encrypted_private_key TEXT; -- Kunci privat terenkripsi

-- 2. Tabel log tanda tangan digital dokumen
CREATE TABLE IF NOT EXISTS document_digital_signatures (
    id SERIAL PRIMARY KEY,
    mesin_slug VARCHAR(100) NOT NULL,
    periode_bulan INT NOT NULL,
    periode_tahun INT NOT NULL,
    signed_by_user_id INT REFERENCES users(id),
    signer_role VARCHAR(50) NOT NULL,
    document_hash VARCHAR(64) NOT NULL,      -- SHA-256 Checksum
    signature_data TEXT NOT NULL,            -- RSA Signature Base64
    verification_token VARCHAR(64) UNIQUE NOT NULL, -- Token untuk URL scan QR Code
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_revoked BOOLEAN DEFAULT FALSE,
    revoked_reason TEXT
);
```

---

## 4. Langkah-Langkah Pengerjaan Teknis (Roadmap)

| Fase | Fokus | Pekerjaan |
| :---: | :--- | :--- |
| **1** | **Profil & Paraf Pelaksana** | - Menambahkan Canvas Signature Pad di halaman profil/registrasi user.<br>- Logika auto-fallback inisial jika canvas kosong.<br>- Menempelkan paraf secara dinamis pada tanggal 1–31 di `machine_period_report.php`. |
| **2** | **Mesin Kriptografi & QR Code** | - Membuat helper PHP untuk enkripsi RSA dan hashing SHA-256 via OpenSSL.<br>- Integrasi library generator QR Code PHP lokal (tanpa internet).<br>- Membuat rute dan view verifikasi publik internal (`/verify?token=...`). |
| **3** | **Check Sheet & PDF Export** | - Menempatkan QR Code di kotak kanan atas "Diperiksa Oleh".<br>- Penyesuaian layout cetak PDF (`check_sheet_layout.php`) agar QR Code & paraf tampil tajam. |
| **4** | **Pengujian & Deployment** | - Uji skenario audit: scan QR Code, uji coba manipulasi data untuk melihat deteksi tamper.<br>- Push ke repository & panduan deploy ke server. |

---

## 5. Bahan Poin untuk Disampaikan di Meeting

1. *"Untuk paraf pelaksana harian, kita gunakan sistem otomatis berbasis akun login dengan opsi gambar paraf di profil atau inisial NIK, sehingga operator tidak terbebani menggambar ulang setiap hari dan tabel tanggal 1–31 tetap rapi."*
2. *"Untuk kotak 'Diperiksa Oleh', kita bangun sistem Tanda Tangan Digital mandiri berbasis QR Code dan Kriptografi SHA-256/RSA bawaan PHP tanpa biaya pihak ketiga (Rp 0,-), yang bisa discan untuk pembuktian keaslian dokumen saat audit CPOB."*
