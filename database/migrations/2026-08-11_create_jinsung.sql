-- Modul Autonomous Maintenance Jinsung (Packaging).
-- Jinsung ada 5 mesin fisik, dipecah jadi 2 modul (ngikutin pola Illapak 1-2 /
-- Illapak 3-12): "Jinsung 1 - 4" (part template sama utk 4 mesin) dan
-- "Jinsung 5" (part template beda dikit - alat & 1 part tambahan).
-- Jalankan sekali pada database form_am_plg setelah deploy file aplikasi.

-- Ganti 1 baris generik 'Jinsung' (didaftarkan di migration Chimei) jadi 5
-- baris per-mesin fisik, biar dropdown filter per modul bisa milih spesifik.
DELETE FROM `mesin` WHERE `nama_mesin` = 'Jinsung';

INSERT INTO `mesin` (`nama_mesin`)
SELECT * FROM (SELECT 'Jinsung 1' AS nama_mesin) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mesin` WHERE `nama_mesin` = 'Jinsung 1');

INSERT INTO `mesin` (`nama_mesin`)
SELECT * FROM (SELECT 'Jinsung 2' AS nama_mesin) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mesin` WHERE `nama_mesin` = 'Jinsung 2');

INSERT INTO `mesin` (`nama_mesin`)
SELECT * FROM (SELECT 'Jinsung 3' AS nama_mesin) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mesin` WHERE `nama_mesin` = 'Jinsung 3');

INSERT INTO `mesin` (`nama_mesin`)
SELECT * FROM (SELECT 'Jinsung 4' AS nama_mesin) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mesin` WHERE `nama_mesin` = 'Jinsung 4');

INSERT INTO `mesin` (`nama_mesin`)
SELECT * FROM (SELECT 'Jinsung 5' AS nama_mesin) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mesin` WHERE `nama_mesin` = 'Jinsung 5');

CREATE TABLE IF NOT EXISTS `jinsung_1_4` (
  `id_jinsung_1_4` int(11) NOT NULL AUTO_INCREMENT,
  `flexible_conveyor_infeed_belt_conveyor` varchar(255) DEFAULT NULL,
  `pocket_pembawa_sachet` varchar(255) DEFAULT NULL,
  `shaft_bushing_pusher_stacking` varchar(255) DEFAULT NULL,
  `rantai_penggerak` varchar(255) DEFAULT NULL,
  `regulator_angin_stacking_cartoning_check_weigher` varchar(255) DEFAULT NULL,
  `regulator_angin_chamber_hot_melt` varchar(255) DEFAULT NULL,
  `sensor_sachet_pack_nozzle` varchar(255) DEFAULT NULL,
  `penekan_sachet` varchar(255) DEFAULT NULL,
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
  PRIMARY KEY (`id_jinsung_1_4`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `kendala_jinsung_1_4` (
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

CREATE TABLE IF NOT EXISTS `jinsung_5` (
  `id_jinsung_5` int(11) NOT NULL AUTO_INCREMENT,
  `belt_conveyor_dan_pusher_pack` varchar(255) DEFAULT NULL,
  `pocket_pembawa_sachet` varchar(255) DEFAULT NULL,
  `shaft_bushing_pusher_piston` varchar(255) DEFAULT NULL,
  `rantai_penggerak_utama_cartoning` varchar(255) DEFAULT NULL,
  `regulator_angin_conveyor_cartoning_check_weigher` varchar(255) DEFAULT NULL,
  `regulator_angin_chamber_hot_melt` varchar(255) DEFAULT NULL,
  `sensor_sachet_pack_nozzle` varchar(255) DEFAULT NULL,
  `timing_belt_conveyor` varchar(255) DEFAULT NULL,
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
  PRIMARY KEY (`id_jinsung_5`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `kendala_jinsung_5` (
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
