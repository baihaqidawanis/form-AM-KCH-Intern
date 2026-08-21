-- Master_partController dulu jalanin sanitize_string (htmlspecialchars) di
-- SAVE time buat field deskriptif (section/label/metode/alat/standard/durasi/
-- pelaksanaan) -- padahal semua view yang nampilin field ini udah escape lagi
-- pas OUTPUT. Hasilnya "&" kesimpen literal jadi "&amp;" di DB. Kode sanitize
-- ini udah dihapus (lihat Master_partController::add()/edit()), migrasi ini
-- benerin 6 row SIG yang kadung kesimpen korup.
UPDATE "master_part" SET
  "section" = replace("section", '&amp;', '&'),
  "standard" = replace("standard", '&amp;', '&')
WHERE "section" LIKE '%&amp;%' OR "standard" LIKE '%&amp;%';

-- Urutan sempat bisa diisi manual (input angka), jadi ada yang duplikat
-- (mis. dua part sama-sama urutan=5) atau minus. Sekarang urutan cuma bisa
-- diatur lewat drag-and-drop (lihat Master_partController::reorder()), jadi
-- di sini dirapihin jadi 1..N per mesin, urutan relatif yang ada sekarang
-- dipertahankan (urutan ASC, id ASC buat tie-break).
WITH ranked AS (
  SELECT id, ROW_NUMBER() OVER (PARTITION BY machine_key ORDER BY urutan ASC, id ASC) AS rn
  FROM "master_part"
)
UPDATE "master_part" m
SET "urutan" = ranked.rn
FROM ranked
WHERE m.id = ranked.id;
