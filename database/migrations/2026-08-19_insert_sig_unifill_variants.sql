-- SIG fisiknya ada 2 unit (SIG 5, SIG 6) dan Unifill ada 2 unit (Unifill A, Unifill B).
-- Row 'SIG' lama (id 4) dibiarkan apa adanya supaya record histori tetap valid,
-- cukup ditambah opsi baru buat form add ke depannya. 'Unifill B' (id 17) juga
-- dibiarkan, tinggal ditambah 'Unifill A'.
INSERT INTO "mesin" ("nama_mesin") VALUES
  ('SIG 5'),
  ('SIG 6'),
  ('Unifill A')
ON CONFLICT DO NOTHING;
