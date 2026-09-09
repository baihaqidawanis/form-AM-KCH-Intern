-- Split fisik Best Pack. ID legacy 35 tidak diubah agar histori transaksi lama tetap utuh.
BEGIN;

INSERT INTO "mesin" ("nama_mesin")
SELECT unit.nama_mesin
FROM (VALUES
  ('Kemas Best Pack - Ilapak 1'),
  ('Kemas Best Pack - SIG 5'),
  ('Kemas Best Pack - SIG 6'),
  ('Kemas Best Pack - Joyea'),
  ('Best Pack (non Inkjet) - Jinsung 1'),
  ('Best Pack (non Inkjet) - Jinsung 2'),
  ('Best Pack (non Inkjet) - Jinsung 3'),
  ('Best Pack (non Inkjet) - Jinsung 4'),
  ('Best Pack (non Inkjet) - Jinsung 5'),
  ('Best Pack (non Inkjet) - Unifill B'),
  ('Best Pack (non Inkjet) - Ilapak 11')
) AS unit(nama_mesin)
WHERE NOT EXISTS (
  SELECT 1 FROM "mesin" m WHERE m."nama_mesin" = unit.nama_mesin
);

COMMIT;
