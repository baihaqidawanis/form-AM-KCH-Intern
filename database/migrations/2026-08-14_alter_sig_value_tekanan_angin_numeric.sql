-- `value_tekanan_angin` (SIG) sebelumnya `int` -- standard part "Tekanan Angin Suplai"
-- itu rentang desimal (0.8 - 1.5 bar), jadi kolom integer nolak nilai kayak "1.2"
-- walau sudah lolos validasi numeric di aplikasi. Ganti ke decimal biar konsisten.
ALTER TABLE `sig`
  MODIFY COLUMN `value_tekanan_angin` decimal(4,2) DEFAULT NULL;
