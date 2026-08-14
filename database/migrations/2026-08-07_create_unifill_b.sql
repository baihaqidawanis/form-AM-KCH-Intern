-- Modul Autonomous Maintenance Unifill B (Filling).
-- Jalankan sekali pada database form_am_plg setelah deploy file aplikasi.
CREATE TABLE IF NOT EXISTS `unifill_b` (
  `id_unifill_b` int(11) NOT NULL AUTO_INCREMENT,
  `conveyor` varchar(255) DEFAULT NULL,
  `cutting_unit` varchar(255) DEFAULT NULL,
  `neck_sealing_unit` varchar(255) DEFAULT NULL,
  `nozzle` varchar(255) DEFAULT NULL,
  `tekanan_angin` varchar(255) DEFAULT NULL,
  `temperature_air_pendingin` varchar(255) DEFAULT NULL,
  `piston_valves_dan_selang_pengisian` varchar(255) DEFAULT NULL,
  `buffer_roller_dispenser` varchar(255) DEFAULT NULL,
  `sensory_eyemark_sensor_redaksi` varchar(255) DEFAULT NULL,
  `cylinder_grip` varchar(255) DEFAULT NULL,
  `timing_belt_pengisian` varchar(255) DEFAULT NULL,
  `filter_airfan` varchar(255) DEFAULT NULL,
  `guide_nozzle` varchar(255) DEFAULT NULL,
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
  PRIMARY KEY (`id_unifill_b`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `kendala_unifill_b` (
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
