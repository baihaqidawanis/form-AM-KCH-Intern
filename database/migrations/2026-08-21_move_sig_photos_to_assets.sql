-- Foto SIG sebelumnya di-upload manual lewat Master Data Part (tersimpan di
-- uploads/files/, folder yang GAK ikut deploy lewat git) -- dipindah balik
-- ke assets/images/sig/ (folder yang emang belum pernah ada sebelumnya --
-- SIG dari awal gak punya foto asli, cuma placeholder path yang gak pernah
-- kesimpen filenya) biar foto asli ini otomatis ikut kebawa tiap deploy.
UPDATE "master_part" SET "image_path" = 'assets/images/sig/sig antistatic.png' WHERE "machine_key" = 'sig' AND "field_name" = 'antistatic';
UPDATE "master_part" SET "image_path" = 'assets/images/sig/sig akrilik.png' WHERE "machine_key" = 'sig' AND "field_name" = 'guarding_akrilik';
UPDATE "master_part" SET "image_path" = 'assets/images/sig/sig inkjet.png' WHERE "machine_key" = 'sig' AND "field_name" = 'inkjet';
UPDATE "master_part" SET "image_path" = 'assets/images/sig/sig conveyor.png' WHERE "machine_key" = 'sig' AND "field_name" = 'jalur_conveyor';
UPDATE "master_part" SET "image_path" = 'assets/images/sig/sig slider nozzle.png' WHERE "machine_key" = 'sig' AND "field_name" = 'jarak_slider_dengan_nozzle';
UPDATE "master_part" SET "image_path" = 'assets/images/sig/sig modul pisau.png' WHERE "machine_key" = 'sig' AND "field_name" = 'modul_pisau';
UPDATE "master_part" SET "image_path" = 'assets/images/sig/sig pisau belah.png' WHERE "machine_key" = 'sig' AND "field_name" = 'pisau_belah';
UPDATE "master_part" SET "image_path" = 'assets/images/sig/sig roll penarik &slitting shim.png' WHERE "machine_key" = 'sig' AND "field_name" = 'rol_penarik_sachet_dan_foil_slitting_shim';
UPDATE "master_part" SET "image_path" = 'assets/images/sig/sig sealing cross.png' WHERE "machine_key" = 'sig' AND "field_name" = 'sealing_cross_dan_vertikal';
UPDATE "master_part" SET "image_path" = 'assets/images/sig/sig pressure.png' WHERE "machine_key" = 'sig' AND "field_name" = 'tekanan_angin_suplai';
UPDATE "master_part" SET "image_path" = 'assets/images/sig/sig vacuum hood.png' WHERE "machine_key" = 'sig' AND "field_name" = 'vacuum_hood';
