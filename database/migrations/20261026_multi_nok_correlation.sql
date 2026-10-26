-- Keep the existing single-value columns for historical reports and clients.
-- The new arrays hold all selected correlations and their nonconformity categories.
DO $$
DECLARE detail_table record;
BEGIN
  FOR detail_table IN
    SELECT c.table_name
    FROM information_schema.columns c
    WHERE c.table_schema = 'public'
      AND c.table_name LIKE 'kendala_%'
      AND c.column_name = 'korelasi_tag'
  LOOP
    EXECUTE format('ALTER TABLE %I ADD COLUMN IF NOT EXISTS korelasi_tags jsonb', detail_table.table_name);
    EXECUTE format('ALTER TABLE %I ADD COLUMN IF NOT EXISTS kategori_ketidaksesuaian_ids jsonb', detail_table.table_name);
    EXECUTE format('UPDATE %I SET korelasi_tags = CASE WHEN korelasi_tag IS NULL OR korelasi_tag = 0 THEN ''[]''::jsonb ELSE jsonb_build_array(korelasi_tag) END WHERE korelasi_tags IS NULL', detail_table.table_name);
    EXECUTE format('UPDATE %I SET kategori_ketidaksesuaian_ids = CASE WHEN kategori_ketidaksesuaian IS NULL OR kategori_ketidaksesuaian = 0 THEN ''[]''::jsonb ELSE jsonb_build_array(kategori_ketidaksesuaian) END WHERE kategori_ketidaksesuaian_ids IS NULL', detail_table.table_name);
    EXECUTE format('ALTER TABLE %I ALTER COLUMN korelasi_tags SET DEFAULT ''[]''::jsonb, ALTER COLUMN kategori_ketidaksesuaian_ids SET DEFAULT ''[]''::jsonb', detail_table.table_name);
  END LOOP;
END $$;
