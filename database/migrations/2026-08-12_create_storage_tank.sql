-- Migration for Storage Tank (Compounding)
CREATE TABLE IF NOT EXISTS `storage_tank` (
  `id_storage_tank` int(11) NOT NULL AUTO_INCREMENT,
  `body_storage_tank` varchar(255) DEFAULT 'OK',
  `jalur_pipa_storage_tank` varchar(255) DEFAULT 'OK',
  `motor_dan_gearbox` varchar(255) DEFAULT 'OK',
  `baling_baling_agitator` varchar(255) DEFAULT 'OK',
  `seal_mainhole` varchar(255) DEFAULT 'OK',
  `pengunci_tutup_mainhole` varchar(255) DEFAULT 'OK',
  `clamp_ferrule` varchar(255) DEFAULT 'OK',
  `kendala` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `user_create` varchar(255) DEFAULT NULL,
  `user_approve` varchar(255) DEFAULT NULL,
  `approval` varchar(255) DEFAULT NULL,
  `perubahan` text DEFAULT NULL,
  `user_perubah` varchar(255) DEFAULT NULL,
  `tanggal_perubahan` datetime DEFAULT NULL,
  `kategori_tag` int(11) DEFAULT NULL,
  `korelasi_tag` int(11) DEFAULT NULL,
  `kategori_ketidaksesuaian` int(11) DEFAULT NULL,
  `id_tagging` int(11) DEFAULT NULL,
  `mesin` int(11) DEFAULT NULL,
  `delete_status` int(11) DEFAULT 0,
  PRIMARY KEY (`id_storage_tank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `kendala_storage_tank` (
  `id_kendala` int(11) NOT NULL AUTO_INCREMENT,
  `id_am` int(11) DEFAULT NULL,
  `mesin` int(11) DEFAULT NULL,
  `nama_bagian` varchar(255) DEFAULT NULL,
  `kendala` text DEFAULT NULL,
  `kategori_tag` int(11) DEFAULT NULL,
  `korelasi_tag` int(11) DEFAULT NULL,
  `kategori_ketidaksesuaian` int(11) DEFAULT NULL,
  `klasifikasi_tag` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_kendala`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO mesin (nama_mesin) SELECT * FROM (SELECT 'Storage Tank' AS nama_mesin) AS tmp WHERE NOT EXISTS (SELECT 1 FROM mesin WHERE nama_mesin = 'Storage Tank');
