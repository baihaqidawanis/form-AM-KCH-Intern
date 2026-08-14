-- Modul Autonomous Maintenance Inkjet Kemas & Best Pack (Packaging).
-- Jalankan sekali pada database form_am_plg setelah deploy file aplikasi.

INSERT INTO `mesin` (`nama_mesin`)
SELECT * FROM (SELECT 'Injekt Kemas & Best Pack' AS nama_mesin) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mesin` WHERE `nama_mesin` = 'Injekt Kemas & Best Pack');

CREATE TABLE IF NOT EXISTS `best_pack` (
  `id_best_pack` int(11) NOT NULL AUTO_INCREMENT,
  `body_best_pack` varchar(255) DEFAULT NULL,
  `konveyor_best_pack` varchar(255) DEFAULT NULL,
  `print_head_inkjet` varchar(255) DEFAULT NULL,
  `belt_conveyor_best_pack` varchar(255) DEFAULT NULL,
  `pisau_best_pack` varchar(255) DEFAULT NULL,
  `selang_angin_best_pack` varchar(255) DEFAULT NULL,
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
  PRIMARY KEY (`id_best_pack`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `kendala_best_pack` (
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
