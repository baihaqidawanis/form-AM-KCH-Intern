BEGIN;

DO $$
DECLARE had_v2 boolean; t text;
BEGIN
  SELECT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = current_schema()
      AND table_name = 'am_period_signatures'
      AND column_name = 'signature_version'
  ) INTO had_v2;

  ALTER TABLE "am_period_signatures" ADD COLUMN IF NOT EXISTS "document_payload" jsonb NOT NULL DEFAULT '{}'::jsonb;
  ALTER TABLE "am_period_signatures" ADD COLUMN IF NOT EXISTS "signature_version" smallint NOT NULL DEFAULT 2;
  ALTER TABLE "am_period_signatures" ADD COLUMN IF NOT EXISTS "operator_name" varchar(255);
  ALTER TABLE "am_period_signatures" ADD COLUMN IF NOT EXISTS "operator_username" varchar(100);
  ALTER TABLE "am_period_signatures" ADD COLUMN IF NOT EXISTS "operator_role_id" integer;
  ALTER TABLE "am_period_signatures" ADD COLUMN IF NOT EXISTS "operator_signature_mac" varchar(64);
  ALTER TABLE "am_period_signatures" ADD COLUMN IF NOT EXISTS "spv_name" varchar(255);
  ALTER TABLE "am_period_signatures" ADD COLUMN IF NOT EXISTS "spv_username" varchar(100);
  ALTER TABLE "am_period_signatures" ADD COLUMN IF NOT EXISTS "spv_role_id" integer;
  ALTER TABLE "am_period_signatures" ADD COLUMN IF NOT EXISTS "spv_signature_mac" varchar(64);
  IF NOT had_v2 THEN DELETE FROM "am_period_signatures"; END IF;

  FOREACH t IN ARRAY ARRAY[
    'sig', 'joeya', 'illapak_1_2', 'illapak_3_12', 'unifill_b',
    'chimei', 'temach', 'jihcheng', 'jinsung_1_4', 'jinsung_5',
    'best_pack', 'cosmec', 'fbd_jaw_chuan', 'fbd_glatt', 'supermixer',
    'storage_tank', 'storage_tank_tetrapak', 'mixing_tank', 'granulator',
    'check_weigher', 'conveyor_sig'
  ] LOOP
    EXECUTE format('ALTER TABLE public.%I DROP COLUMN IF EXISTS no_wr', 'kendala_' || t);
    EXECUTE format('ALTER TABLE public.%I ADD COLUMN IF NOT EXISTS foto_before varchar(255)', 'kendala_' || t);
    EXECUTE format('ALTER TABLE public.%I ADD COLUMN IF NOT EXISTS foto_before_sha256 char(64)', 'kendala_' || t);
    EXECUTE format('ALTER TABLE public.%I ADD COLUMN IF NOT EXISTS foto_before_mime varchar(50)', 'kendala_' || t);
    EXECUTE format('ALTER TABLE public.%I ADD COLUMN IF NOT EXISTS foto_before_size integer', 'kendala_' || t);
  END LOOP;
END $$;

COMMIT;
