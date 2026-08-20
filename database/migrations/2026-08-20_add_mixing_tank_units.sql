-- 4 unit fisik Mixing Tank (MT Silverson, MT Tetrapak 1-3), masing-masing
-- punya nomor seri sendiri. Baris mesin lama (id 46, "Mixing Tank") TETAP
-- dibiarkan apa adanya supaya data lama gak rusak -- form add sekarang pakai
-- dropdown baru ini, bukan hardcode id 46 lagi.
INSERT INTO "mesin" ("nama_mesin", "nomor_seri") VALUES
('MT Silverson', '2BTNK02001'),
('MT Tetrapak 1', '2BTNK02002'),
('MT Tetrapak 2', '2BTNK02003'),
('MT Tetrapak 3', '2BTNK02036');
