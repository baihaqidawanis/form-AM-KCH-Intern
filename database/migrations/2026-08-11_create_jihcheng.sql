-- Modul Autonomous Maintenance Jihcheng (Packaging).
-- Jalankan sekali pada database form_am_plg setelah deploy file aplikasi.
-- Mesin 'Jihcheng' sudah didaftarkan ke tabel `mesin` di migration Chimei
-- (2026-08-11_create_chimei.sql); tidak perlu didaftarkan ulang di sini.
CREATE TABLE IF NOT EXISTS `jihcheng` (
  `id_jihcheng` int(11) NOT NULL AUTO_INCREMENT,
  `konveyor_belt` varchar(255) DEFAULT NULL,
  `flexible_konveyor_u` varchar(255) DEFAULT NULL,
  `suction_cup` varchar(255) DEFAULT NULL,
  `pocket_pembawa_tube_dan_pack` varchar(255) DEFAULT NULL,
  `shaft_dan_bushing_pusher` varchar(255) DEFAULT NULL,
  `bearing_rantai_tube_cam_pusher` varchar(255) DEFAULT NULL,
  `rantai_penggerak_utama_pocket_tube_pack` varchar(255) DEFAULT NULL,
  `regulator_angin_utama` varchar(255) DEFAULT NULL,
  `regulator_angin_chamber_hot_melt` varchar(255) DEFAULT NULL,
  `sensor_tube_pack_nozzle_lem` varchar(255) DEFAULT NULL,
  `pengecekan_tombol_emergency` varchar(255) DEFAULT NULL,
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
  PRIMARY KEY (`id_jihcheng`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `kendala_jihcheng` (
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
