-- 1. Bersihkan dummy PHPUnit part dari tabel master_part
DELETE FROM "master_part" 
WHERE "field_name" LIKE 'phpunit_%' 
   OR "label" LIKE 'PHPUnit%';

-- 2. Drop kolom dummy phpunit dari tb_mesin_illapak_1_2
ALTER TABLE "tb_mesin_illapak_1_2" DROP COLUMN IF EXISTS "phpunit_part_shift_2_3";
ALTER TABLE "tb_mesin_illapak_1_2" DROP COLUMN IF EXISTS "phpunit_part_shift_c4bac93e";
ALTER TABLE "tb_mesin_illapak_1_2" DROP COLUMN IF EXISTS "phpunit_part_shift_0cd74f62";
ALTER TABLE "tb_mesin_illapak_1_2" DROP COLUMN IF EXISTS "phpunit_part_shift_175d4896";
ALTER TABLE "tb_mesin_illapak_1_2" DROP COLUMN IF EXISTS "phpunit_part_shift_196ab506";
ALTER TABLE "tb_mesin_illapak_1_2" DROP COLUMN IF EXISTS "phpunit_part_shift_1ab971e0";
ALTER TABLE "tb_mesin_illapak_1_2" DROP COLUMN IF EXISTS "phpunit_part_shift_1d5663f4";
ALTER TABLE "tb_mesin_illapak_1_2" DROP COLUMN IF EXISTS "phpunit_part_shift_1e76d122";
ALTER TABLE "tb_mesin_illapak_1_2" DROP COLUMN IF EXISTS "phpunit_part_shift_40bdfdee";
ALTER TABLE "tb_mesin_illapak_1_2" DROP COLUMN IF EXISTS "phpunit_part_shift_41f13e70";

-- 3. Pastikan 4 part awal illapak_1_2 yang sempat terhapus di-restore ke master_part
INSERT INTO "master_part" ("machine_key", "field_name", "label", "section", "metode", "alat", "standard", "durasi", "pelaksanaan", "highlight", "image_path", "urutan", "shift_schedule")
VALUES
  ('illapak_1_2', 'sealing_horizontal', 'Sealing Horizontal', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disikat', 'Sikat kawat', 'Tidak ada sisa foil menempel', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_1_2/illapak_1_2 sealing horizontal.png', 1, '1'),
  ('illapak_1_2', 'sealing_vertikal', 'Sealing Vertikal', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disikat', 'Sikat kawat', 'Tidak ada sisa foil menempel', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_1_2/illapak_1_2 sealing vertikal.png', 2, '1'),
  ('illapak_1_2', 'body_mesin', 'Body Mesin dan Conveyor', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Quiltec dan PW', 'Bersih dari kotoran', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/illapak_1_2/illapak_1_2 body mesin.png', 3, '1'),
  ('illapak_1_2', 'roller_foil_film', 'Roller Foil (Setelah Inkjet)', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Quiltec dan PW', 'Bersih dari kotoran', '15''', '2 Mingguan (Setiap W1, W3, W5 Senin Shift 1)', 'bulanan', 'assets/images/illapak_1_2/illapak_1_2 roller foil film.png', 4, '1')
ON CONFLICT ("machine_key", "field_name") DO UPDATE SET
  "label" = EXCLUDED."label",
  "section" = EXCLUDED."section",
  "metode" = EXCLUDED."metode",
  "alat" = EXCLUDED."alat",
  "standard" = EXCLUDED."standard",
  "durasi" = EXCLUDED."durasi",
  "pelaksanaan" = EXCLUDED."pelaksanaan",
  "highlight" = EXCLUDED."highlight",
  "image_path" = EXCLUDED."image_path",
  "urutan" = EXCLUDED."urutan",
  "shift_schedule" = EXCLUDED."shift_schedule",
  "taken_out_at" = NULL;

-- Pastikan urutan dan shift_schedule part 5 & 6 illapak_1_2 konsisten
UPDATE "master_part" SET "urutan" = 5, "shift_schedule" = '1,2,3', "section" = 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)' WHERE "machine_key" = 'illapak_1_2' AND "field_name" = 'position_indicator_sealing_vertical';
UPDATE "master_part" SET "urutan" = 6, "shift_schedule" = '1,2,3', "section" = 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)' WHERE "machine_key" = 'illapak_1_2' AND "field_name" = 'vacum_sliter';
UPDATE "master_part" SET "urutan" = 7, "shift_schedule" = '1', "section" = 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)' WHERE "machine_key" = 'illapak_1_2' AND "field_name" = 'piston_pengisian';
UPDATE "master_part" SET "urutan" = 8, "shift_schedule" = '1', "section" = 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)' WHERE "machine_key" = 'illapak_1_2' AND "field_name" = 'pneumatic_valves_pengisian';
UPDATE "master_part" SET "urutan" = 9, "shift_schedule" = '1', "section" = 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)' WHERE "machine_key" = 'illapak_1_2' AND "field_name" = 'baut_sealing_vertikal';
UPDATE "master_part" SET "urutan" = 10, "shift_schedule" = '1', "section" = 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)' WHERE "machine_key" = 'illapak_1_2' AND "field_name" = 'rubber_penarik_foil';
UPDATE "master_part" SET "urutan" = 11, "shift_schedule" = '1', "section" = 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)' WHERE "machine_key" = 'illapak_1_2' AND "field_name" = 'sensor_eyemark_dan_sambungan_foil';
UPDATE "master_part" SET "urutan" = 12, "shift_schedule" = '1', "section" = 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)' WHERE "machine_key" = 'illapak_1_2' AND "field_name" = 'guarding_mesin';
UPDATE "master_part" SET "urutan" = 13, "shift_schedule" = '1', "section" = 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)' WHERE "machine_key" = 'illapak_1_2' AND "field_name" = 'pressure_blow_sealing_vertical';
UPDATE "master_part" SET "urutan" = 14, "shift_schedule" = '1', "section" = 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)' WHERE "machine_key" = 'illapak_1_2' AND "field_name" = 'inkjet';
UPDATE "master_part" SET "urutan" = 15, "shift_schedule" = '1', "section" = 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)' WHERE "machine_key" = 'illapak_1_2' AND "field_name" = 'pengunci_nozzle_pengisian';

-- 4. Sinkronisasi multi-shift ke illapak_3_12
ALTER TABLE "tb_mesin_illapak_3_12" ADD COLUMN IF NOT EXISTS "shift" varchar(1) DEFAULT NULL;

UPDATE "master_part"
SET "shift_schedule" = '1,2,3'
WHERE "machine_key" = 'illapak_3_12'
  AND "field_name" IN ('position_indicator_sealing_vertical', 'vacum_sliter', 'alarm_temperature');
