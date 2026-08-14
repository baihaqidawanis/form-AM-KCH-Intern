-- Modul Autonomous Maintenance Cosmec (Compounding) — mesin pertama di kategori ini.
-- Jalankan sekali pada database form_am_plg setelah deploy file aplikasi.
CREATE TABLE IF NOT EXISTS `cosmec` (
  `id_cosmec` int(11) NOT NULL AUTO_INCREMENT,
  `body_panel_hmi` varchar(255) DEFAULT NULL,
  `body_mesin` varchar(255) DEFAULT NULL,
  `pengunci_bin` varchar(255) DEFAULT NULL,
  `switch_rantai` varchar(255) DEFAULT NULL,
  `as_dan_flange_tumbler` varchar(255) DEFAULT NULL,
  `baut_dan_mur_pada_flange_shaft` varchar(255) DEFAULT NULL,
  `panel_pompa_hidrolik_mesin` varchar(255) DEFAULT NULL,
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
  PRIMARY KEY (`id_cosmec`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `kendala_cosmec` (
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

INSERT INTO `mesin` (`nama_mesin`)
SELECT * FROM (SELECT 'Cosmec' AS nama_mesin) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mesin` WHERE `nama_mesin` = 'Cosmec');
