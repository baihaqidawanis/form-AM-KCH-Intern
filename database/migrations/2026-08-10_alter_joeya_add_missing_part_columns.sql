-- Modul Autonomous Maintenance Joeya (Filling).
-- Round 9 menambahkan 7 kolom part baru ke tabel `joeya` (12 part total) via ALTER TABLE manual
-- yang tidak pernah tercatat sebagai migration terpisah. File `2026-08-07_create_joeya.sql` jadi
-- ketinggalan (cuma 5 kolom part versi Round 7). Jalankan file ini setelah `2026-08-07_create_joeya.sql`
-- untuk menyamakan skema `joeya` dengan yang dibutuhkan `JoeyaController.php` saat ini.
ALTER TABLE `joeya`
  ADD COLUMN IF NOT EXISTS `bearing_sealing` varchar(255) DEFAULT NULL AFTER `roller_foil_film`,
  ADD COLUMN IF NOT EXISTS `bearing_pisau_sachet_cutting` varchar(255) DEFAULT NULL AFTER `bearing_sealing`,
  ADD COLUMN IF NOT EXISTS `final_cutting` varchar(255) DEFAULT NULL AFTER `bearing_pisau_sachet_cutting`,
  ADD COLUMN IF NOT EXISTS `per_transmisi_sealing` varchar(255) DEFAULT NULL AFTER `final_cutting`,
  ADD COLUMN IF NOT EXISTS `filling_pump` varchar(255) DEFAULT NULL AFTER `per_transmisi_sealing`,
  ADD COLUMN IF NOT EXISTS `bantalan_sealing` varchar(255) DEFAULT NULL AFTER `filling_pump`,
  ADD COLUMN IF NOT EXISTS `isolasi_tahan_panas` varchar(255) DEFAULT NULL AFTER `bantalan_sealing`;
