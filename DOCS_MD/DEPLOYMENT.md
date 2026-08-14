# 🚀 Rencana Deployment ke Production

**Status saat ini (14 Agustus 2026): aplikasi belum di-deploy ke server production.** Semua yang sudah dikerjakan — migrasi ke PostgreSQL, sistem role 4-tingkat, penyatuan kode 17 modul mesin, dan seterusnya — baru berjalan di database lokal komputer development. Server production (`10.167.170.71`) masih dalam kondisi sebelum migrasi: masih MySQL/MariaDB, dan kemungkinan besar masih memakai skema data serta struktur role yang lama. Dokumen ini adalah rencana untuk menyambungkan dua kondisi tersebut.

**Instalasi ke server tidak perlu Docker.** Rencana di dokumen ini murni instalasi langsung (Apache + PHP + PostgreSQL terpasang di server, seperti setup XAMPP tapi versi production) — bukan lewat container. Ada `Dockerfile` di root project sebagai opsi cadangan yang sudah teruji bisa jalan, tapi itu bukan bagian dari rencana ini. Untuk skala aplikasi ini (internal perusahaan, satu server, dipakai puluhan sampai ratusan operator), instalasi langsung lebih sederhana dan lebih mudah dirawat dibanding menambah lapisan container yang sebenarnya tidak dibutuhkan di sini.

## Kenapa Ini Bukan Sekadar "Upload File, Selesai"

Kalau kode di komputer development ini langsung disalin ke server production dan dijalankan begitu saja, aplikasinya akan langsung rusak. Ada empat alasan:

1. **Skemanya sudah berubah total.** Kode sekarang mengharapkan struktur database Postgres yang sudah dirapikan (role 4-tingkat, satu kerangka kode untuk semua mesin, nama kolom yang konsisten huruf kecil). Server production masih memakai skema MySQL yang lama.
2. **Data di production itu data sungguhan.** Bukan data uji coba — ada histori pengisian form dari operator yang sudah berjalan sekian lama, dan itu tidak boleh hilang begitu saja saat proses migrasi.
3. **Akun-akun di production itu orang sungguhan**, dengan sistem role yang lama. Memetakan role lama ke 4 role baru (Administrator/Manager/Supervisor/Staff-Operator) itu keputusan bisnis — siapa jadi apa — bukan sesuatu yang bisa saya putuskan sendiri dari kode.
4. **Server production kemungkinan belum siap secara teknis** — versi PHP-nya mungkin belum sesuai, extension yang dibutuhkan (`pdo_pgsql`, dst) mungkin belum terpasang, dan seterusnya.

## Hal yang Perlu Diputuskan Dulu (Bukan oleh Saya)

Sebelum proses deployment bisa dimulai, ada beberapa hal yang perlu disepakati oleh tim atau pemilik sistem:

**1. Apakah production ikut pindah ke PostgreSQL, atau tetap di MySQL?**

Saran saya: pindah ke Postgres. Alasannya, semua pekerjaan yang sudah diuji berbulan-bulan ini memang dibangun di atas Postgres. Tapi ada satu hal yang perlu dipahami: mau pindah ke Postgres atau tetap di MySQL, skema tabelnya **tetap harus dimigrasi/disesuaikan** — karena kode sekarang konsisten pakai nama kolom huruf kecil, sementara skema MySQL lama masih ada beberapa kolom yang capitalized. Jadi kalau proses migrasi skema toh harus dilakukan, sekalian pindah ke Postgres jauh lebih masuk akal, karena itu yang sudah benar-benar teruji.

**2. Kalau di data production lama ada mesin di luar 17 mesin yang sekarang ada di aplikasi — datanya mau diapakan?** Kalau memang bukan mesin dari pabrik ini, kemungkinan besar tidak perlu ikut dimigrasi (cukup diarsipkan terpisah). Tapi ini perlu dikonfirmasi eksplisit dulu, jangan sampai data itu "ditinggal begitu saja" tanpa ada yang tahu.

**3. Siapa masuk role apa?** Ini pemetaan dari sistem role lama ke 4 role baru — murni keputusan bisnis, bukan sesuatu yang bisa ditebak dari data.

**4. Kapan waktu yang paling pas untuk proses migrasi?** Prosesnya butuh waktu di mana aplikasi tidak bisa dipakai operator — perlu dicari jendela waktu yang paling tidak mengganggu (misalnya pergantian shift atau akhir pekan).

## Rencana Kerja (dengan Asumsi Pindah ke Postgres)

Saya bagi jadi lima fase. Fase 0 bisa dikerjakan kapan saja sebelum hari-H, tidak perlu menunggu jendela downtime.

### Fase 0 — Persiapan

- [ ] Backup penuh database production MySQL yang sekarang — dump lengkap, dan kalau memungkinkan snapshot filenya juga. Ini dilakukan **sebelum** menyentuh apapun.
- [ ] Pasang PostgreSQL 17 di server production (atau di server database terpisah kalau memang begitu arsitekturnya).
- [ ] Pasang PHP 8.2 beserta extension yang dibutuhkan (`pdo_pgsql`, `pgsql`, dan `zip` — yang terakhir ini dibutuhkan supaya export ke Excel bisa jalan, detailnya ada di `TECHNICAL_OVERVIEW.md`).
- [ ] (Opsional, hati-hati) OPcache bisa dicoba diaktifkan untuk performa, tapi di lingkungan development (Windows) ternyata bikin Apache tidak stabil (`VirtualProtect() failed` di log, request gagal random) — masalah yang dikenal terjadi antara OPcache dan Windows. Kalau server production Linux, kemungkinan besar tidak akan mengalami masalah yang sama, tapi tetap **uji stabilitasnya dulu** sebelum diaktifkan permanen (submit beberapa form berturut-turut, cek log error) — jangan langsung diaktifkan tanpa dicek.
- [ ] Install dependency lewat Composer dan npm di server. Sebelum itu, cek dulu apakah server bisa mengakses `packagist.org`, `github.com`, dan `registry.npmjs.org` — kalau server production tidak ada akses internet sama sekali, folder `vendor/` dan `node_modules/` perlu disalin manual dari komputer yang punya akses.
- [ ] Siapkan file `.env` khusus production, isinya kredensial database production. Yang paling penting: pastikan `DEVELOPMENT_MODE=false`. Ini gampang sekali terlewat, tapi dampaknya cukup serius — kalau lupa, detail error PHP (termasuk path file di server) bisa muncul ke siapa saja yang mengakses, bahkan yang belum login.
- [ ] Ganti kredensial SMTP di `.env` production ke akun email sungguhan (`USE_SMTP=true`, `SMTP_HOST`/`SMTP_USERNAME`/`SMTP_PASSWORD`/dst milik perusahaan). File `.env` di komputer development ini isinya kredensial **Ethereal Email** (`smtp.ethereal.email`) — cuma akun tes gratis buat verifikasi alur reset-password, email yang "terkirim" tidak pernah sampai ke inbox sungguhan mana pun. Kalau file `.env` development ini ikut disalin ke production tanpa diganti, fitur reset password lewat email akan terlihat jalan (tidak error) tapi user tidak akan pernah menerima emailnya.

### Fase 1 — Migrasi Skema dan Data

- [ ] Bangun skema Postgres berdasarkan skema production yang sesungguhnya (bukan cuma dari yang ada di komputer development — production mungkin punya kolom atau tabel tambahan yang di lokal sudah tidak ada). Semua file migrasi ada di `database/migrations/`.
- [ ] Migrasikan data SIG — ini kemungkinan satu-satunya mesin yang punya histori data sungguhan dari production lama, karena 16 mesin lainnya memang baru dibuat di aplikasi ini dan belum pernah ada datanya di production.
- [ ] 16 mesin lainnya mulai dari nol record — itu memang wajar, bukan tanda ada yang salah.
- [ ] Migrasikan data user, sambil memetakan ke role baru sesuai kesepakatan tim.
- [ ] Cek dan sesuaikan data master (kategori tag, korelasi, klasifikasi, daftar mesin, dst).

### Fase 2 — Deploy Kode

- [ ] Deploy kode dari versi yang sudah final dan sudah diuji ke server production.
- [ ] Pasang file `.env` production yang sudah disiapkan di Fase 0 — bukan file `.env` development.
- [ ] Restart Apache supaya konfigurasi baru terbaca.

### Fase 3 — Uji Coba di Production (Sebelum Operator Mulai Pakai)

- [ ] Jalankan test otomatis (`vendor/bin/phpunit --testdox` dan `npm run test:e2e`) langsung di server production, arahkan ke domain production-nya.
- [ ] Jalankan checklist manual di `TESTING.md` — login pakai 4 akun asli (bukan akun dummy), coba isi form tiap kategori mesin, coba approval, coba export.
- [ ] Bandingkan jumlah record SIG yang berhasil dimigrasi dengan jumlah aslinya di MySQL lama — pastikan tidak ada yang hilang.
- [ ] Cek sekali lagi `DEVELOPMENT_MODE=false` sebelum operator mulai diberi akses. Ini poin yang sama seperti di Fase 0, tapi cukup penting untuk dicek dua kali.

### Fase 4 — Go-Live

- [ ] Informasikan ke operator bahwa sistemnya sudah baru, tapi akun lama tetap bisa dipakai (username sama), dan siapa yang bisa dihubungi kalau ada kendala.
- [ ] Pantau log error dan Audit Trail lebih intensif di beberapa hari pertama.
- [ ] Simpan backup MySQL lama minimal beberapa bulan — jangan langsung dihapus, untuk jaga-jaga kalau ternyata ada data yang terlewat saat migrasi.

## Kalau Ada yang Tidak Beres (Rencana Rollback)

Backup MySQL lama dari Fase 0 tetap disimpan, jadi kalau terjadi masalah serius, masih memungkinkan untuk sementara kembali ke sistem lama sambil masalahnya diselidiki. Karena semua kredensial dan tipe database diatur lewat file `.env` (bukan ditulis langsung di kode), secara teknis proses kembali ini cukup dengan mengganti `.env` dan restart web server. Catatan pentingnya: ini hanya berlaku selama skema dan data di sisi MySQL lama masih dijaga utuh — jangan dihapus saat migrasi, setidaknya sampai beberapa minggu setelah go-live terbukti stabil.

## Yang Di Luar Cakupan Dokumen Ini

- Penjadwalan waktu downtime — ini keputusan operasional yang perlu dikoordinasikan dengan pihak pabrik.
- Proses approval/sign-off perubahan sistem produksi — biasanya ada proses change-management tersendiri untuk sistem yang terkait GMP.
- Training ulang operator kalau ada perubahan tampilan yang cukup signifikan — kemungkinan ini akan cukup banyak, mengingat 16 dari 17 mesin memang benar-benar baru dibanding sistem yang lama.
