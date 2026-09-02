-- Semua form AM dapat menyimpan shift bila Master Data Part mengaktifkan Shift 2/3.
DO $$
DECLARE form_table text;
BEGIN
  FOR form_table IN
    SELECT table_name FROM information_schema.tables
    WHERE table_schema = 'public' AND table_name LIKE 'tb_mesin_%'
  LOOP
    EXECUTE format('ALTER TABLE %I ADD COLUMN IF NOT EXISTS shift varchar(1) DEFAULT NULL', form_table);
  END LOOP;
END $$;
