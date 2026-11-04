BEGIN;
LOCK TABLE kategori IN SHARE ROW EXCLUSIVE MODE;
-- Tambahan kategori HSE; ID lama dan histori tetap dipertahankan.
SELECT setval(pg_get_serial_sequence('kategori', 'id'), GREATEST(COALESCE((SELECT MAX(id) FROM kategori), 1), 1));
INSERT INTO kategori (korelasi_id, kategori)
SELECT 2, seed.nama FROM (VALUES
  ('APD'),
  ('Signage'),
  ('Sarana Prasarana'),
  ('Limbah B3'),
  ('Limbah Non B3'),
  ('Efisiensi Energi'),
  ('Efisiensi dan Pencemaran Air'),
  ('Pencemaran Udara')
) AS seed(nama)
WHERE NOT EXISTS (SELECT 1 FROM kategori k WHERE k.korelasi_id = 2 AND k.kategori = seed.nama);
COMMIT;
