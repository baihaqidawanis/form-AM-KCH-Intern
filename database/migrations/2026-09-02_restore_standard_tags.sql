-- Master tag standar AM. Hapus residue automation, lalu pastikan pilihan Red/White tersedia.
DELETE FROM tag WHERE kategori_tag ILIKE 'PHPUnit Test Tag%';
INSERT INTO tag (kategori_tag)
SELECT 'Red Tag'
WHERE NOT EXISTS (SELECT 1 FROM tag WHERE lower(kategori_tag) = 'red tag');
INSERT INTO tag (kategori_tag)
SELECT 'White Tag'
WHERE NOT EXISTS (SELECT 1 FROM tag WHERE lower(kategori_tag) = 'white tag');
