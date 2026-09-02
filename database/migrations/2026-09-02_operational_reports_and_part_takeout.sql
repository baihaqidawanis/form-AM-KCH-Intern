-- Daily operational reporting and non-destructive part takeout.
-- Run after the existing master_part migration.

ALTER TABLE "master_part" ADD COLUMN IF NOT EXISTS "active_from" date NOT NULL DEFAULT DATE '2000-01-01';
ALTER TABLE "master_part" ADD COLUMN IF NOT EXISTS "taken_out_at" timestamp NULL;
ALTER TABLE "master_part" ADD COLUMN IF NOT EXISTS "taken_out_by" varchar(255) NULL;
ALTER TABLE "master_part" ADD COLUMN IF NOT EXISTS "takeout_reason" text NULL;

-- An operational day is 06:45 through 05:45 the following calendar day.
ALTER TABLE "tb_mesin_sig" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_joeya" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_illapak_1_2" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_illapak_3_12" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_unifill_b" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_chimei" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_temach" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_jihcheng" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_jinsung_1_4" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_jinsung_5" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_best_pack" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_cosmec" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_fbd_jaw_chuan" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_fbd_glatt" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_supermixer" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_storage_tank" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_storage_tank_tetrapak" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_mixing_tank" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_granulator" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_check_weigher" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
ALTER TABLE "tb_mesin_conveyor_sig" ADD COLUMN IF NOT EXISTS "operational_date" date NULL;
CREATE UNIQUE INDEX IF NOT EXISTS "uq_illapak_1_2_operational_shift" ON "tb_mesin_illapak_1_2" ("mesin", "operational_date", "shift") WHERE "shift" IS NOT NULL;

-- Preserve the intended operational day for existing historical rows.
DO $$
DECLARE t text;
BEGIN
  FOREACH t IN ARRAY ARRAY['sig','joeya','illapak_1_2','illapak_3_12','unifill_b','chimei','temach','jihcheng','jinsung_1_4','jinsung_5','best_pack','cosmec','fbd_jaw_chuan','fbd_glatt','supermixer','storage_tank','storage_tank_tetrapak','mixing_tank','granulator','check_weigher','conveyor_sig']
  LOOP
    EXECUTE format('UPDATE tb_mesin_%I SET operational_date = (created_at - CASE WHEN created_at::time < TIME ''06:45'' THEN INTERVAL ''1 day'' ELSE INTERVAL ''0 day'' END)::date WHERE operational_date IS NULL', t);
    EXECUTE format('CREATE INDEX IF NOT EXISTS %I ON tb_mesin_%I (mesin, operational_date)', 'idx_' || t || '_operational_date', t);
  END LOOP;
END $$;
