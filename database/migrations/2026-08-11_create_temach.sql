-- Modul Autonomous Maintenance Temach (Packaging).
-- Jalankan sekali pada database form_am_plg setelah deploy file aplikasi.
-- Mesin 'Temach' sudah didaftarkan ke tabel `mesin` di migration Chimei
-- (2026-08-11_create_chimei.sql); tidak perlu didaftarkan ulang di sini.
CREATE TABLE IF NOT EXISTS `temach` (
  `id_temach` int(11) NOT NULL AUTO_INCREMENT,
  `conveyor_produk` varchar(255) DEFAULT NULL,
  `pusher_pendorong_pack` varchar(255) DEFAULT NULL,
  `turet` varchar(255) DEFAULT NULL,
  `cam` varchar(255) DEFAULT NULL,
  `lubrikasi_bearing_konveyor` varchar(255) DEFAULT NULL,
  `jalur_compressed_air` varchar(255) DEFAULT NULL,
  `air_regulator` varchar(255) DEFAULT NULL,
  `heater_a_f` varchar(255) DEFAULT NULL,
  `baut_turet` varchar(255) DEFAULT NULL,
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
  PRIMARY KEY (`id_temach`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `kendala_temach` (
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
