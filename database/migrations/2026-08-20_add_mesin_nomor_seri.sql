-- Nomor seri fisik mesin (bukan bagian dari master_part -- ini atribut mesin
-- itu sendiri, bukan detail per-part). Ditampilkan di halaman list2 tiap
-- modul, langsung di bawah judul, tanpa label "Nomor Seri:" -- teksnya aja.
-- Baru diisi buat 4 mesin Compounding dulu (rollout bertahap sesuai
-- permintaan user), sisanya NULL -- gak tampil apa-apa di bawah judul
-- (aman, gak ada baris kosong yang aneh, lihat kondisi if di view).
ALTER TABLE "mesin" ADD COLUMN IF NOT EXISTS "nomor_seri" varchar(50) DEFAULT NULL;

UPDATE "mesin" SET "nomor_seri" = '2BMIX13011' WHERE "nama_mesin" = 'Cosmec';
UPDATE "mesin" SET "nomor_seri" = '3BDRY13001' WHERE "nama_mesin" = 'FBD Glatt';
UPDATE "mesin" SET "nomor_seri" = '2BDRY13003' WHERE "nama_mesin" = 'FBD Jaw Chuan';
UPDATE "mesin" SET "nomor_seri" = '2BMIX13001' WHERE "nama_mesin" = 'Supermixer';
