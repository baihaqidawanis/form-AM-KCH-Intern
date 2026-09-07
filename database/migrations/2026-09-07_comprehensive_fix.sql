BEGIN;

UPDATE "master_part"
SET "shift_schedule" = '1,2,3'
WHERE "machine_key" = 'illapak_3_12'
  AND "field_name" IN (
    'position_indicator_sealing_vertical',
    'vacum_sliter',
    'alarm_temperature'
  );

UPDATE "mesin"
SET "nama_mesin" = regexp_replace("nama_mesin", 'illapak', 'Ilapak', 'gi')
WHERE "nama_mesin" ILIKE '%illapak%';

UPDATE "mesin"
SET "nama_mesin" = 'JOYEA'
WHERE "nama_mesin" ILIKE 'joeya';

DO $$
DECLARE
    t text;
BEGIN
    FOREACH t IN ARRAY ARRAY[
        'sig', 'joeya', 'illapak_1_2', 'illapak_3_12', 'unifill_b',
        'chimei', 'temach', 'jihcheng', 'jinsung_1_4', 'jinsung_5',
        'best_pack', 'cosmec', 'fbd_jaw_chuan', 'fbd_glatt', 'supermixer',
        'storage_tank', 'storage_tank_tetrapak', 'mixing_tank', 'granulator',
        'check_weigher', 'conveyor_sig'
    ]
    LOOP
        EXECUTE format('ALTER TABLE public.%I ADD COLUMN IF NOT EXISTS no_wr varchar(20) DEFAULT NULL', 'kendala_' || t);
        EXECUTE format('ALTER TABLE public.%I ADD COLUMN IF NOT EXISTS shift varchar(1) DEFAULT NULL', 'tb_mesin_' || t);
        EXECUTE format('ALTER TABLE public.%I ADD COLUMN IF NOT EXISTS operational_date date NULL', 'tb_mesin_' || t);
        EXECUTE format('ALTER TABLE public.%I OWNER TO formam', 'tb_mesin_' || t);
        EXECUTE format('ALTER TABLE public.%I OWNER TO formam', 'kendala_' || t);
    END LOOP;
END $$;

COMMIT;