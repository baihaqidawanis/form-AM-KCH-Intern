BEGIN;

-- Remember Me sudah dihentikan; sesi hanya berasal dari login interaktif.
ALTER TABLE "users" DROP COLUMN IF EXISTS "login_session_key";

-- PostgreSQL tidak otomatis membuat index pada kolom referensi.
CREATE INDEX IF NOT EXISTS "idx_kendala_sig_id_am" ON "kendala_sig" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_storage_tank_id_am" ON "kendala_storage_tank" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_storage_tank_tetrapak_id_am" ON "kendala_storage_tank_tetrapak" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_mixing_tank_id_am" ON "kendala_mixing_tank" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_granulator_id_am" ON "kendala_granulator" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_supermixer_id_am" ON "kendala_supermixer" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_fbd_jaw_chuan_id_am" ON "kendala_fbd_jaw_chuan" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_fbd_glatt_id_am" ON "kendala_fbd_glatt" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_unifill_b_id_am" ON "kendala_unifill_b" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_temach_id_am" ON "kendala_temach" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_illapak_1_2_id_am" ON "kendala_illapak_1_2" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_illapak_3_12_id_am" ON "kendala_illapak_3_12" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_conveyor_sig_id_am" ON "kendala_conveyor_sig" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_check_weigher_id_am" ON "kendala_check_weigher" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_chimei_id_am" ON "kendala_chimei" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_cosmec_id_am" ON "kendala_cosmec" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_jihcheng_id_am" ON "kendala_jihcheng" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_jinsung_1_4_id_am" ON "kendala_jinsung_1_4" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_jinsung_5_id_am" ON "kendala_jinsung_5" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_joeya_id_am" ON "kendala_joeya" ("id_am");
CREATE INDEX IF NOT EXISTS "idx_kendala_best_pack_id_am" ON "kendala_best_pack" ("id_am");

COMMIT;
