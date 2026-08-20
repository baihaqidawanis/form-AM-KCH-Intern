-- 15 unit fisik Storage Tank Silverson (ST Liq No 1..15), masing-masing punya
-- nomor seri sendiri. Sebelumnya storage_tank cuma punya 1 baris mesin (id 45,
-- "Storage Tank") yang di-hardcode di form add -- baris itu TETAP dibiarkan
-- apa adanya supaya data lama (tb_mesin_storage_tank yang mesin=45) gak rusak.
INSERT INTO "mesin" ("nama_mesin", "nomor_seri") VALUES
('ST Liq No 1', '2BTNK02004'),
('ST Liq No 2', '2BTNK02005'),
('ST Liq No 3', '2BTNK02006'),
('ST Liq No 4', '2BTNK02007'),
('ST Liq No 5', '2BTNK02008'),
('ST Liq No 6', '2BTNK02009'),
('ST Liq No 7', '2BTNK02010'),
('ST Liq No 8', '2BTNK02011'),
('ST Liq No 9', '2BTNK02012'),
('ST Liq No 10', '2BTNK02013'),
('ST Liq No 11', '2BTNK02014'),
('ST Liq No 12', '2BTNK02015'),
('ST Liq No 13', '2BTNK02016'),
('ST Liq No 14', '2BTNK02017'),
('ST Liq No 15', '2BTNK02018');
