-- Tambah 8 unit fisik Chimei ke tabel mesin.
-- Masing-masing unit punya nama sendiri, tanpa nomor seri.
-- Mesin induk 'Chimei' (id=31) tetap ada, unit-unit ini dipakai
-- sebagai pilihan dropdown di form Add Chimei.

INSERT INTO "mesin" ("nama_mesin") VALUES
  ('Chimei 12A (JS 1)'),
  ('Chimei 4B (JS 2)'),
  ('Chimei 10A (JS 3)'),
  ('Chimei 11A (JS 4)'),
  ('Chimei 6A (Illapak 1)'),
  ('Chimei 9A (Illapak 11)'),
  ('Chimei 5B (Unifill B)'),
  ('Chimei 1A (SIG 6)')
ON CONFLICT DO NOTHING;
