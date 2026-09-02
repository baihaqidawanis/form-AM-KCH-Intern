-- Data lama hasil import menyimpan apostrof sebagai HTML entity. Entity harus
-- disimpan sebagai karakter asli di database; escaping cukup dilakukan saat view.
UPDATE "master_part"
SET "durasi" = replace(replace("durasi", '&#039;', ''''), '&#39;', '''')
WHERE "durasi" LIKE '%&#039;%' OR "durasi" LIKE '%&#39;%';
