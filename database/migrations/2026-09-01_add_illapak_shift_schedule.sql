-- Shift adalah atribut record AM, sedangkan jadwal shift adalah master per part.
-- Run setelah migrasi master_part dan rename tabel mesin ke tb_mesin_*.
ALTER TABLE "master_part" ADD COLUMN IF NOT EXISTS "shift_schedule" varchar(10) NOT NULL DEFAULT '1';
ALTER TABLE "tb_mesin_illapak_1_2" ADD COLUMN IF NOT EXISTS "shift" varchar(1) DEFAULT NULL;

-- Transkripsi dari jadwal Illapak yang telah ada. Semua baris lain tetap Shift 1.
UPDATE "master_part"
SET "shift_schedule" = '1,2,3'
WHERE "machine_key" = 'illapak_1_2'
  AND "field_name" IN ('position_indicator_sealing_vertical', 'vacum_sliter');
