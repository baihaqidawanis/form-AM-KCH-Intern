-- Migration for FBD Glatt (Compounding)
CREATE TABLE IF NOT EXISTS `fbd_glatt` (
  `id_fbd_glatt` int(11) NOT NULL AUTO_INCREMENT,
  `body_mesin` varchar(255) DEFAULT 'OK',
  `panel_fbd` varchar(255) DEFAULT 'OK',
  `hmi_panel_fbd` varchar(255) DEFAULT 'OK',
  `seal_bagtight` varchar(255) DEFAULT 'OK',
  `container_up_down` varchar(255) DEFAULT 'OK',
  `shaking` varchar(255) DEFAULT 'OK',
  `pressure_gauge_damper` varchar(255) DEFAULT 'OK',
  `seal_container` varchar(255) DEFAULT 'OK',
  `guarding_pengunci_kontainer` varchar(255) DEFAULT 'OK',
  `container_mesh_dan_roda` varchar(255) DEFAULT 'OK',
  `filter_dan_bag_tight` varchar(255) DEFAULT 'OK',
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
  PRIMARY KEY (`id_fbd_glatt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `kendala_fbd_glatt` (
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

INSERT INTO mesin (nama_mesin) SELECT * FROM (SELECT 'FBD Glatt' AS nama_mesin) AS tmp WHERE NOT EXISTS (SELECT 1 FROM mesin WHERE nama_mesin = 'FBD Glatt');
