-- Modul Autonomous Maintenance Chimei (Packaging).
-- Jalankan sekali pada database form_am_plg setelah deploy file aplikasi.
CREATE TABLE IF NOT EXISTS `chimei` (
  `id_chimei` int(11) NOT NULL AUTO_INCREMENT,
  `conveyor_produk` varchar(255) DEFAULT NULL,
  `roller_opp` varchar(255) DEFAULT NULL,
  `rantai_opp` varchar(255) DEFAULT NULL,
  `bearing_break_opp` varchar(255) DEFAULT NULL,
  `rantai_motor_utama_cam` varchar(255) DEFAULT NULL,
  `as_pendorong_pack` varchar(255) DEFAULT NULL,
  `jalur_compressed_air` varchar(255) DEFAULT NULL,
  `air_regulator` varchar(255) DEFAULT NULL,
  `sensor_produk_opp_pack` varchar(255) DEFAULT NULL,
  `kendala` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime(6) DEFAULT NULL,
  `user_create` varchar(255) DEFAULT NULL,
  `user_approve` varchar(255) DEFAULT NULL,
  `approval` varchar(255) DEFAULT NULL,
  `perubahan` varchar(255) DEFAULT NULL,
  `user_perubah` varchar(255) DEFAULT NULL,
  `tanggal_perubahan` varchar(255) DEFAULT NULL,
  `kategori_tag` int(11) DEFAULT 0,
  `korelasi_tag` int(11) DEFAULT 0,
  `kategori_ketidaksesuaian` int(11) DEFAULT 0,
  `id_tagging` int(11) DEFAULT 0,
  `mesin` int(11) DEFAULT NULL,
  `delete_status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_chimei`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `kendala_chimei` (
  `id_kendala` int(11) NOT NULL AUTO_INCREMENT,
  `id_am` int(11) DEFAULT NULL,
  `mesin` int(11) DEFAULT NULL,
  `nama_bagian` varchar(255) DEFAULT NULL,
  `kendala` text DEFAULT NULL,
  `kategori_tag` int(11) DEFAULT NULL,
  `korelasi_tag` int(11) DEFAULT NULL,
  `kategori_ketidaksesuaian` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `klasifikasi_tag` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_kendala`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Daftarkan 5 mesin Packaging di tabel master `mesin`. Cuma Chimei yang sudah
-- punya modul fungsional; 4 lainnya (Temach, Jinsung, Jihcheng, Injekt Kemas &
-- Best Pack) didaftarkan dulu sebagai placeholder, modulnya nyusul.
INSERT INTO `mesin` (`nama_mesin`)
SELECT * FROM (SELECT 'Chimei' AS nama_mesin) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mesin` WHERE `nama_mesin` = 'Chimei');

INSERT INTO `mesin` (`nama_mesin`)
SELECT * FROM (SELECT 'Temach' AS nama_mesin) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mesin` WHERE `nama_mesin` = 'Temach');

INSERT INTO `mesin` (`nama_mesin`)
SELECT * FROM (SELECT 'Jinsung' AS nama_mesin) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mesin` WHERE `nama_mesin` = 'Jinsung');

INSERT INTO `mesin` (`nama_mesin`)
SELECT * FROM (SELECT 'Jihcheng' AS nama_mesin) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mesin` WHERE `nama_mesin` = 'Jihcheng');

INSERT INTO `mesin` (`nama_mesin`)
SELECT * FROM (SELECT 'Injekt Kemas & Best Pack' AS nama_mesin) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mesin` WHERE `nama_mesin` = 'Injekt Kemas & Best Pack');
