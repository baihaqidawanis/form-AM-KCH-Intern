-- Data master Form AM untuk PostgreSQL
-- Diekspor dari MySQL lokal (data master saja, tanpa data transaksi).

-- mesin (30 baris)
INSERT INTO "mesin" ("id", "nama_mesin") OVERRIDING SYSTEM VALUE VALUES
  ('3', 'Joeya'),
  ('4', 'SIG'),
  ('5', 'Illapak 1'),
  ('6', 'Illapak 2'),
  ('7', 'Illapak 3'),
  ('8', 'Illapak 4'),
  ('9', 'Illapak 5'),
  ('10', 'Illapak 6'),
  ('11', 'Illapak 7'),
  ('12', 'Illapak 8'),
  ('13', 'Illapak 9'),
  ('14', 'Illapak 10'),
  ('15', 'Illapak 11'),
  ('16', 'Illapak 12'),
  ('17', 'Unifill B'),
  ('31', 'Chimei'),
  ('32', 'Temach'),
  ('34', 'Jihcheng'),
  ('35', 'Injekt Kemas & Best Pack'),
  ('36', 'Jinsung 1'),
  ('37', 'Jinsung 2'),
  ('38', 'Jinsung 3'),
  ('39', 'Jinsung 4'),
  ('40', 'Jinsung 5'),
  ('41', 'Cosmec'),
  ('42', 'FBD Jaw Chuan'),
  ('43', 'FBD Glatt'),
  ('44', 'Supermixer'),
  ('45', 'Storage Tank'),
  ('46', 'Mixing Tank'),
  ('47', 'SIG 5'),
  ('48', 'SIG 6'),
  ('49', 'Unifill A'),
  ('50', 'ST Liq No 1'),
  ('51', 'ST Liq No 2'),
  ('52', 'ST Liq No 3'),
  ('53', 'ST Liq No 4'),
  ('54', 'ST Liq No 5'),
  ('55', 'ST Liq No 6'),
  ('56', 'ST Liq No 7'),
  ('57', 'ST Liq No 8'),
  ('58', 'ST Liq No 9'),
  ('59', 'ST Liq No 10'),
  ('60', 'ST Liq No 11'),
  ('61', 'ST Liq No 12'),
  ('62', 'ST Liq No 13'),
  ('63', 'ST Liq No 14'),
  ('64', 'ST Liq No 15'),
  ('65', 'ST Liq 2 No 3'),
  ('66', 'ST Liq 2 No 4'),
  ('67', 'ST Liq 2 No 5'),
  ('68', 'ST Liq 2 No 6'),
  ('69', 'ST Liq 2 No 7'),
  ('70', 'ST Liq 2 No 8'),
  ('71', 'ST Liq 2 No 9'),
  ('72', 'ST Liq 2 No 10'),
  ('73', 'ST Liq 2 No 11'),
  ('74', 'ST Liq 2 No 12'),
  ('75', 'ST Liq 2 No 13'),
  ('76', 'ST Liq 2 No 14'),
  ('77', 'ST Liq 2 No 15'),
  ('78', 'ST Liq 2 No 16'),
  ('79', 'ST Liq 2 No 17'),
  ('80', 'MT Silverson'),
  ('81', 'MT Tetrapak 1'),
  ('82', 'MT Tetrapak 2'),
  ('83', 'MT Tetrapak 3')
ON CONFLICT DO NOTHING;
SELECT setval(pg_get_serial_sequence('"mesin"', 'id'), COALESCE((SELECT MAX("id") FROM "mesin"), 1));

-- Nomor seri fisik unit Storage Tank Silverson -- lihat
-- database/migrations/2026-08-20_add_storage_tank_units.sql
UPDATE "mesin" SET "nomor_seri" = '2BTNK02004' WHERE "id" = '50';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02005' WHERE "id" = '51';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02006' WHERE "id" = '52';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02007' WHERE "id" = '53';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02008' WHERE "id" = '54';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02009' WHERE "id" = '55';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02010' WHERE "id" = '56';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02011' WHERE "id" = '57';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02012' WHERE "id" = '58';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02013' WHERE "id" = '59';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02014' WHERE "id" = '60';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02015' WHERE "id" = '61';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02016' WHERE "id" = '62';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02017' WHERE "id" = '63';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02018' WHERE "id" = '64';

-- Nomor seri fisik unit Storage Tank Tetrapak -- nomor 029 dobel buat No 11 &
-- No 12 sesuai sumber data asli (dikonfirmasi user, bukan typo migrasi). Lihat
-- database/migrations/2026-08-20_add_storage_tank_tetrapak.sql
UPDATE "mesin" SET "nomor_seri" = '2BTNK02021' WHERE "id" = '65';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02022' WHERE "id" = '66';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02023' WHERE "id" = '67';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02024' WHERE "id" = '68';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02025' WHERE "id" = '69';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02026' WHERE "id" = '70';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02027' WHERE "id" = '71';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02028' WHERE "id" = '72';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02029' WHERE "id" = '73';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02029' WHERE "id" = '74';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02030' WHERE "id" = '75';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02031' WHERE "id" = '76';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02032' WHERE "id" = '77';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02033' WHERE "id" = '78';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02034' WHERE "id" = '79';

-- Nomor seri fisik unit Mixing Tank -- lihat
-- database/migrations/2026-08-20_add_mixing_tank_units.sql
UPDATE "mesin" SET "nomor_seri" = '2BTNK02001' WHERE "id" = '80';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02002' WHERE "id" = '81';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02003' WHERE "id" = '82';
UPDATE "mesin" SET "nomor_seri" = '2BTNK02036' WHERE "id" = '83';

-- 8 unit fisik Chimei (tanpa nomor seri) -- lihat
-- database/migrations/2026-08-20_add_chimei_units.sql
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

-- kategori (9 baris)
INSERT INTO "kategori" ("id", "korelasi_id", "kategori") OVERRIDING SYSTEM VALUE VALUES
  ('1', '1', 'Ringkas'),
  ('2', '1', 'Rapih'),
  ('3', '1', 'Resik'),
  ('4', '1', 'Rawat'),
  ('5', '1', 'Rajin'),
  ('6', '2', 'Unsafe Action'),
  ('7', '2', 'Unsafe Condition'),
  ('8', '3', 'None'),
  ('11', '5', 'None')
ON CONFLICT DO NOTHING;
SELECT setval(pg_get_serial_sequence('"kategori"', 'id'), COALESCE((SELECT MAX("id") FROM "kategori"), 1));

-- korelasi (4 baris)
INSERT INTO "korelasi" ("id", "nama") OVERRIDING SYSTEM VALUE VALUES
  ('1', '5R'),
  ('2', 'HSE'),
  ('3', 'Productivity'),
  ('5', 'None')
ON CONFLICT DO NOTHING;
SELECT setval(pg_get_serial_sequence('"korelasi"', 'id'), COALESCE((SELECT MAX("id") FROM "korelasi"), 1));

-- klasifikasi (3 baris)
INSERT INTO "klasifikasi" ("id", "nama") OVERRIDING SYSTEM VALUE VALUES
  ('1', 'Abnormal'),
  ('2', 'SOC'),
  ('3', 'HTR')
ON CONFLICT DO NOTHING;
SELECT setval(pg_get_serial_sequence('"klasifikasi"', 'id'), COALESCE((SELECT MAX("id") FROM "klasifikasi"), 1));

-- tag (2 baris)
INSERT INTO "tag" ("id", "kategori_tag") OVERRIDING SYSTEM VALUE VALUES
  ('1', 'Red Tag'),
  ('2', 'White Tag')
ON CONFLICT DO NOTHING;
SELECT setval(pg_get_serial_sequence('"tag"', 'id'), COALESCE((SELECT MAX("id") FROM "tag"), 1));

-- 4 role sesuai URS poin 2.1 (Administrator, Manager, Supervisor, Staff/Operator)
INSERT INTO "roles" ("role_id", "role_name") OVERRIDING SYSTEM VALUE VALUES
  (1, 'Administrator'),
  (2, 'Manager'),
  (3, 'Supervisor'),
  (4, 'Staff/Operator')
ON CONFLICT DO NOTHING;
SELECT setval(pg_get_serial_sequence('"roles"', 'role_id'), COALESCE((SELECT MAX("role_id") FROM "roles"), 1));

-- Akun superadmin awal. Username sengaja bukan NIK (pengecualian khusus).
-- Password sementara: Admin@123  <-- GANTI setelah login pertama.
INSERT INTO "users" ("nama", "email", "username", "password", "account_status", "user_role_id", "is_super_admin")
VALUES ('Super Admin', 'admin@localhost', 'superadmin', '$2y$10$XT.XFKi3xXDkv12zaWU5VuWnVbmMORonOFJbVe/mOVsXWq2VGfLuy', 'Active', 1, true)
ON CONFLICT DO NOTHING;

-- Nomor seri fisik mesin -- lihat database/migrations/2026-08-20_add_mesin_nomor_seri.sql
UPDATE "mesin" SET "nomor_seri" = '2BMIX13011' WHERE "nama_mesin" = 'Cosmec';
UPDATE "mesin" SET "nomor_seri" = '3BDRY13001' WHERE "nama_mesin" = 'FBD Glatt';
UPDATE "mesin" SET "nomor_seri" = '2BDRY13003' WHERE "nama_mesin" = 'FBD Jaw Chuan';
UPDATE "mesin" SET "nomor_seri" = '2BMIX13001' WHERE "nama_mesin" = 'Supermixer';

-- Master data detail part Cosmec (pilot) -- lihat database/migrations/2026-08-19_create_master_part.sql
INSERT INTO "master_part"
  ("machine_key", "field_name", "label", "section", "metode", "alat", "standard", "durasi", "pelaksanaan", "highlight", "image_path", "urutan")
VALUES
  ('cosmec', 'body_panel_hmi', 'Body Panel HMI', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall lembab dengan Alkohol 70%', 'Bagian luar bersih dari kotoran', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/cosmec/cosmec body panel hmi.png', 1),
  ('cosmec', 'body_mesin', 'Body Mesin', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall lembab dengan Alkohol 70%', 'Bersih dari kotoran', '10''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/cosmec/cosmec body mesin.png', 2),
  ('cosmec', 'pengunci_bin', 'Pengunci Bin', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Kencang dan tidak oblak', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/cosmec/cosmec pengunci bin.png', 3),
  ('cosmec', 'switch_rantai', 'Switch Rantai', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Tombol start tidak aktif jika rantai tidak terpasang', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/cosmec/cosmec switch rantai.png', 4),
  ('cosmec', 'as_dan_flange_tumbler', 'As Dan Flange Tumbler', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak ada Retakan', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/cosmec/cosmec as dan flange tumbler.png', 5),
  ('cosmec', 'baut_dan_mur_pada_flange_shaft', 'Baut Dan Mur Pada Flange Shaft', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Lengkap dan tidak ada yang retak/patah', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/cosmec/cosmec baut dan mur pada flange shaft.png', 6),
  ('cosmec', 'panel_pompa_hidrolik_mesin', 'Panel Pompa Hidrolik Mesin', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak ada tetesan Oli', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/cosmec/cosmec panel pompa hidrolik mesin.png', 7)
ON CONFLICT ("machine_key", "field_name") DO NOTHING;


-- Master data part 16 mesin sisanya -- lihat database/migrations/2026-08-20_migrate_master_part_16_mesin.sql
INSERT INTO "master_part"
  ("machine_key", "field_name", "label", "section", "metode", "alat", "standard", "durasi", "pelaksanaan", "highlight", "image_path", "urutan")
VALUES
  ('sig', 'sealing_cross_dan_vertikal', 'Sealing Cross dan Vertikal', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disikat', 'Sikat Kawat', 'Bersih dari kotoran', '5''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/sig/sig sealing cross.png', 1),
  ('sig', 'guarding_akrilik', 'Guarding Akrilik', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Quiltec', 'Bersih dari kotoran', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/sig/sig akrilik.png', 2),
  ('sig', 'jalur_conveyor', 'Jalur Conveyor', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Quiltec', 'Bersih dari kotoran', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/sig/sig conveyor.png', 3),
  ('sig', 'vacuum_hood', 'Vacuum Hood', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disemprot angin dan sistem filtrasi by SMC', 'Air Gun', 'Bersih dari kotoran', '6''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/sig/sig vacuum hood.png', 4),
  ('sig', 'antistatic', 'Antistatic', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Quiltec', 'Bersih dari kotoran', '6''', 'Bulanan (Setiap W1 Senin Shift 1)', 'bulanan', 'assets/images/sig/sig antistatic.png', 5),
  ('sig', 'tekanan_angin_suplai', 'Tekanan Angin Suplai', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek, disetting', 'Visual', 'Tekanan angin 0.8 - 1.5 bar', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/sig/sig pressure.png', 6),
  ('sig', 'jarak_slider_dengan_nozzle', 'Jarak Slider dengan Nozzle', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Filler Gauge', 'Filler gauge 0.2 tidak ada bulk yang keluar', '5''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/sig/sig slider nozzle.png', 7),
  ('sig', 'rol_penarik_sachet_dan_foil_slitting_shim', 'Rol Penarik Sachet dan Foil / Slitting Shim', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual', 'Tidak aus, tidak ada cacat, berfungsi, dan kondisi blade tidak melengkung', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/sig/sig roll penarik &slitting shim.png', 8),
  ('sig', 'pisau_belah', 'Pisau Belah', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual', 'Kondisi pisau tidak geripis', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/sig/sig pisau belah.png', 9),
  ('sig', 'modul_pisau', 'Modul Pisau', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual', 'Inject grease secukupnya', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/sig/sig modul pisau.png', 10),
  ('sig', 'inkjet', 'Inkjet', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Tidak bleber & hasil coding tidak pudar', '6''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/sig/sig inkjet.png', 11),
  ('joeya', 'sealing_horizontal', 'Sealing Horizontal', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap, disteam', 'sikat tembaga, Steam uap', 'bersih tidak ada sisa bulk/kerak', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/joeya/joeya sealing horizontal.png', 1),
  ('joeya', 'sealing_vertikal', 'Sealing Vertikal', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap, disteam', 'sikat tembaga, Steam uap', 'bersih tidak ada sisa bulk/kerak', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/joeya/joeya sealing vertikal.png', 2),
  ('joeya', 'jalur_konveyor_sachet', 'Jalur Konveyor Sachet', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap, disemprot', 'lap bebas serat, Compressed Air', 'bersih tidak ada sisa bulk/kerak', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/joeya/joeya jalur konveyor sachet.png', 3),
  ('joeya', 'collecting_plate_seluncuran_sachet', 'Collecting Plate / Seluncuran Sachet', 'STANDAR PEMBERSIHAN (CLEANING)', 'dilap', 'lap bebas serat/Wypall', 'bersih tidak ada sisa bulk/kerak', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/joeya/joeya collecting plate seluncuran sachet.png', 4),
  ('joeya', 'roller_foil_film', 'Roller Foil / Film', 'STANDAR PEMBERSIHAN (CLEANING)', 'dilap', 'lap bebas serat/Wypall', 'bersih tidak ada debu', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/joeya/joeya roller foil film.png', 5),
  ('joeya', 'bearing_sealing', 'Bearing Sealing', 'STANDAR PELUMASAN (LUBRICATING)', 'Disemprot', 'Kluber Lubrication', 'Terlumasi', '2''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/joeya/joeya bearing sealing.png', 6),
  ('joeya', 'bearing_pisau_sachet_cutting', 'Bearing Pisau / Sachet Cutting', 'STANDAR PELUMASAN (LUBRICATING)', 'Disemprot', 'Kluber Lubrication', 'Terlumasi', '2''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/joeya/joeya bearing pisau sachet cutting.png', 7),
  ('joeya', 'final_cutting', 'Final Cutting', 'STANDAR PELUMASAN (LUBRICATING)', 'Disemprot', 'Kluber Lubrication', 'Terlumasi', '2''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/joeya/joeya final cutting.png', 8),
  ('joeya', 'per_transmisi_sealing', 'Per Transmisi Sealing', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'cek visual', 'Visual Control', 'tidak putus', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/joeya/joeya per transmisi sealing.png', 9),
  ('joeya', 'filling_pump', 'Filling Pump', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Penggantian air', 'Visual Control', 'Air tidak keruh', '2''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/joeya/joeya filling pump.png', 10),
  ('joeya', 'bantalan_sealing', 'Bantalan Sealing', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'cek visual', 'Visual Control', 'bantalan utuh/tidak sobek', '1''', '2 Mingguan (Setiap Senin Shift 1)', 'bulanan', 'assets/images/joeya/joeya bantalan sealing.png', 11),
  ('joeya', 'isolasi_tahan_panas', 'Isolasi Tahan Panas', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'cek visual', 'Visual Control', 'bersih tidak ada sisa bulk/kerak, tidak sobek', '6''', '2 Mingguan (Setiap Senin Shift 1)', 'bulanan', 'assets/images/joeya/joeya isolasi tahan panas.png', 12),
  ('illapak_1_2', 'sealing_horizontal', 'Sealing Horizontal', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disikat', 'Sikat kawat', 'Tidak ada sisa foil menempel', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_1_2/illapak_1_2 sealing horizontal.png', 1),
  ('illapak_1_2', 'sealing_vertikal', 'Sealing Vertikal', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disikat', 'Sikat kawat', 'Tidak ada sisa foil menempel', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_1_2/illapak_1_2 sealing vertikal.png', 2),
  ('illapak_1_2', 'body_mesin', 'Body Mesin dan Conveyor', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Quiltec dan PW', 'Bersih dari kotoran', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/illapak_1_2/illapak_1_2 body mesin.png', 3),
  ('illapak_1_2', 'roller_foil_film', 'Roller Foil (Setelah Inkjet)', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Quiltec dan PW', 'Bersih dari kotoran', '15''', '2 Mingguan (Setiap W1, W3, W5 Senin Shift 1)', 'bulanan', 'assets/images/illapak_1_2/illapak_1_2 roller foil film.png', 4),
  ('illapak_1_2', 'position_indicator_sealing_vertical', 'Position Indicator Sealing Vertical', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Skala bagian kiri dan kanan menunjukkan angka 0 (nol)', '1''', 'Harian (Awal Shift 1, 2, 3)', NULL, 'assets/images/illapak_1_2/illapak_1_2 position indicator sealing vertical.png', 5),
  ('illapak_1_2', 'vacum_sliter', 'Vacum Sliter', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak penuh', '1''', 'Harian (Awal Shift 1, 2, 3)', NULL, 'assets/images/illapak_1_2/illapak_1_2 vacum sliter.png', 6),
  ('illapak_1_2', 'piston_pengisian', 'Piston Pengisian', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak bocor', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_1_2/illapak_1_2 piston pengisian.png', 7),
  ('illapak_1_2', 'pneumatic_valves_pengisian', 'Pneumatic Valves Pengisian', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak bocor, kotor, ataupun kendor', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_1_2/illapak_1_2 pneumatic valves pengisian.png', 8),
  ('illapak_1_2', 'baut_sealing_vertikal', 'Baut Sealing Vertikal', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Kunci pas', 'Visual Control', 'Kencang', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_1_2/illapak_1_2 baut sealing vertikal.png', 9),
  ('illapak_1_2', 'rubber_penarik_foil', 'Rubber Penarik Foil', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Kencang', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_1_2/illapak_1_2 rubber penarik foil.png', 10),
  ('illapak_1_2', 'sensor_eyemark_dan_sambungan_foil', 'Sensor Eyemark dan Sambungan Foil', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'tes Fungsi', 'Manual', 'Berfungsi normal', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_1_2/illapak_1_2 sensor eyemark dan sambungan foil.png', 11),
  ('illapak_1_2', 'guarding_mesin', 'Guarding Mesin', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'tes Fungsi', 'Manual', 'Mesin bunyi ketika guarding terbuka', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_1_2/illapak_1_2 guarding mesin.png', 12),
  ('illapak_1_2', 'pressure_blow_sealing_vertical', 'Pressure Blow Sealing Vertical', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan 1,5 bar', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_1_2/illapak_1_2 pressure blow sealing vertical.png', 13),
  ('illapak_1_2', 'inkjet', 'Inkjet', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Tidak bleber & hasil coding tidak pudar', '8''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_1_2/illapak_1_2 inkjet.png', 14),
  ('illapak_1_2', 'pengunci_nozzle_pengisian', 'Pengunci Nozzle Pengisian', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak kendor', '1''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/illapak_1_2/illapak_1_2 pengunci nozzle pengisian.png', 15),
  ('illapak_3_12', 'sealing_horizontal', 'Sealing Horizontal', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disikat', 'Sikat kawat', 'Tidak ada sisa foil menempel', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_3_12/illapak_3_12 sealing horizontal.png', 1),
  ('illapak_3_12', 'sealing_vertikal', 'Sealing Vertikal', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disikat', 'Sikat kawat', 'Tidak ada sisa foil menempel', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_3_12/illapak_3_12 sealing vertikal.png', 2),
  ('illapak_3_12', 'body_mesin', 'Body Mesin dan Conveyor', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Quiltec dan PW', 'Bersih dari kotoran', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/illapak_3_12/illapak_3_12 body mesin.png', 3),
  ('illapak_3_12', 'roller_foil_film', 'Roller Foil (Setelah Inkjet)', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Quiltec dan PW', 'Bersih dari kotoran', '15''', '2 Mingguan (Setiap W1, W3, W5 Senin Shift 1)', 'bulanan', 'assets/images/illapak_3_12/illapak_3_12 roller foil film.png', 4),
  ('illapak_3_12', 'position_indicator_sealing_vertical', 'Position Indicator Sealing Vertical', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Skala bagian kiri dan kanan menunjukkan angka 0 (nol)', '1''', 'Harian (Awal Shift 1, 2, 3)', NULL, 'assets/images/illapak_3_12/illapak_3_12 position indicator sealing vertical.png', 5),
  ('illapak_3_12', 'vacum_sliter', 'Vacum Sliter', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak penuh', '1''', 'Harian (Awal Shift 1, 2, 3)', NULL, 'assets/images/illapak_3_12/illapak_3_12 vacum sliter.png', 6),
  ('illapak_3_12', 'alarm_temperature', 'Alarm Temperature', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Alarm temperature menyala (berwarna kuning di layar)', '1''', 'Harian (Awal Shift 1, 2, 3)', NULL, 'assets/images/illapak_3_12/illapak_3_12 alarm temperature.png', 7),
  ('illapak_3_12', 'piston_pengisian', 'Piston Pengisian', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak bocor', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_3_12/illapak_3_12 piston pengisian.png', 8),
  ('illapak_3_12', 'pneumatic_valves_pengisian', 'Pneumatic Valves Pengisian', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak bocor, kotor, ataupun kendor', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_3_12/illapak_3_12 pneumatic valves pengisian.png', 9),
  ('illapak_3_12', 'baut_sealing_vertikal', 'Baut Sealing Vertikal', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Kunci pas', 'Visual Control', 'Kencang', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_3_12/illapak_3_12 baut sealing vertikal.png', 10),
  ('illapak_3_12', 'rubber_penarik_foil', 'Rubber Penarik Foil', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Kencang', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_3_12/illapak_3_12 rubber penarik foil.png', 11),
  ('illapak_3_12', 'sensor_eyemark_dan_sambungan_foil', 'Sensor Eyemark dan Sambungan Foil', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'tes Fungsi', 'Manual', 'Berfungsi normal', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_3_12/illapak_3_12 sensor eyemark dan sambungan foil.png', 12),
  ('illapak_3_12', 'guarding_mesin', 'Guarding Mesin', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'tes Fungsi', 'Manual', 'Mesin bunyi ketika guarding terbuka', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_3_12/illapak_3_12 guarding mesin.png', 13),
  ('illapak_3_12', 'pressure_blow_sealing_vertical', 'Pressure Blow Sealing Vertical', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan 1,5 bar', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_3_12/illapak_3_12 pressure blow sealing vertical.png', 14),
  ('illapak_3_12', 'inkjet', 'Inkjet', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Tidak bleber & hasil coding tidak pudar', '8''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/illapak_3_12/illapak_3_12 inkjet.png', 15),
  ('illapak_3_12', 'pengunci_nozzle_pengisian', 'Pengunci Nozzle Pengisian', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak kendor', '1''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/illapak_3_12/illapak_3_12 pengunci nozzle pengisian.png', 16),
  ('unifill_b', 'conveyor', 'Conveyor', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Quiltec', 'Bersih', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/unifill/unifill conveyor.png', 1),
  ('unifill_b', 'cutting_unit', 'Cutting Unit', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disemprot', 'Botol Semprot dan Angin Bertekanan', 'Bersih dari sisa potongan foil', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/unifill/unifill cutting unit.png', 2),
  ('unifill_b', 'neck_sealing_unit', 'Neck Sealing Unit', 'STANDAR PEMBERSIHAN (CLEANING)', 'Diambil', 'Pinset', 'Tidak ada sisa foil dan lem menempel', '5''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/unifill/unifill neck sealing unit.png', 3),
  ('unifill_b', 'nozzle', 'Nozzle', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Quiltec', 'Bersih dan tidak berkerak', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/unifill/unifill nozzle.png', 4),
  ('unifill_b', 'tekanan_angin', 'Tekanan Angin', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Minimal 6 bar', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/unifill/unifill tekanan angin.png', 5),
  ('unifill_b', 'temperature_air_pendingin', 'Temperature Air Pendingin', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Suhu < 14 °C', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/unifill/unifill temperature air pendingin.png', 6),
  ('unifill_b', 'piston_valves_dan_selang_pengisian', 'Piston, Valves, dan Selang Pengisian', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak bocor, ataupun kendor', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/unifill/unifill piston valves dan selang pengisian.png', 7),
  ('unifill_b', 'buffer_roller_dispenser', 'Buffer Roller / Dispenser', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Dapat berputar dengan lancar/tidak seret', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/unifill/unifill buffer roller dispenser.png', 8),
  ('unifill_b', 'sensory_eyemark_sensor_redaksi', 'Sensory Eyemark, Sensor Redaksi', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Sensor berfungsi', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/unifill/unifill sensory eyemark sensor redaksi.png', 9),
  ('unifill_b', 'cylinder_grip', 'Cylinder Grip', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control & Manual Jog', 'Gripper tidak slip', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/unifill/unifill cylinder grip.png', 10),
  ('unifill_b', 'timing_belt_pengisian', 'Timing Belt Pengisian', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak ada retakan', '2''', 'Mingguan', 'mingguan', 'assets/images/unifill/unifill timing belt pengisian.png', 11),
  ('unifill_b', 'filter_airfan', 'Filter Airfan', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Bersih', '1''', 'Mingguan', 'mingguan', 'assets/images/unifill/unifill filter airfan.png', 12),
  ('unifill_b', 'guide_nozzle', 'Guide Nozzle', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Guide nozzle pastikan lurus dengan nozzle', '1''', 'Mingguan', 'mingguan', 'assets/images/unifill/unifill guide nozzle.png', 13),
  ('chimei', 'conveyor_produk', 'Conveyor Produk', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap basah', 'Bersih dari kotoran', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/chimei/chimei conveyor produk.png', 1),
  ('chimei', 'roller_opp', 'Roller OPP', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap basah', 'Bersih dari kotoran', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/chimei/chimei roller opp.png', 2),
  ('chimei', 'rantai_opp', 'Rantai OPP', 'STANDAR PELUMASAN (LUBRICATING)', 'Dilumasi', 'Grease', 'Terlumasi, tidak kendur', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/chimei/chimei rantai opp.png', 3),
  ('chimei', 'bearing_break_opp', 'Bearing Break OPP', 'STANDAR PELUMASAN (LUBRICATING)', 'Disemprot', 'Spray Lube', 'Pastikan Tidak Macet', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/chimei/chimei bearing break opp.png', 4),
  ('chimei', 'rantai_motor_utama_cam', 'Rantai Motor Utama & Cam', 'STANDAR PELUMASAN (LUBRICATING)', 'Dilumasi', 'Grease', 'Terlumasi, tidak kendur', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/chimei/chimei rantai motor utama cam.png', 5),
  ('chimei', 'as_pendorong_pack', 'As Pendorong Pack', 'STANDAR PELUMASAN (LUBRICATING)', 'Dilumasi', 'Grease', 'Pastikan Tidak Macet', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/chimei/chimei as pendorong pack.png', 6),
  ('chimei', 'jalur_compressed_air', 'Jalur Compressed Air', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak ada kebocoran pada pneumatic, selang dan fitting. Pastikan tidak ada bunyi desis', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/chimei/chimei jalur compressed air.png', 7),
  ('chimei', 'air_regulator', 'Air Regulator', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan udara 4-6 bar', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/chimei/chimei air regulator.png', 8),
  ('chimei', 'sensor_produk_opp_pack', 'Sensor : Produk, OPP, Pack', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Sensor berfungsi', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/chimei/chimei sensor produk opp pack.png', 9),
  ('temach', 'conveyor_produk', 'Conveyor Produk', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap basah', 'Bersih dari kotoran', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/temach/temach conveyor produk.png', 1),
  ('temach', 'pusher_pendorong_pack', 'Pusher Pendorong Pack', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap basah', 'Bersih dari kotoran', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/temach/temach pusher pendorong pack.png', 2),
  ('temach', 'turet', 'Turet', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap kering', 'Bersih dari kotoran', '5''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/temach/temach turet.png', 3),
  ('temach', 'cam', 'Cam', 'STANDAR PELUMASAN (LUBRICATING)', 'Dilumasi', 'Grease', 'Terlumasi, tidak kendur', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/temach/temach cam.png', 4),
  ('temach', 'lubrikasi_bearing_konveyor', 'Lubrikasi Bearing Konveyor', 'STANDAR PELUMASAN (LUBRICATING)', 'Dilumasi', 'Lubrikasi oli', 'Terlumasi, tidak macet', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/temach/temach lubrikasi bearing konveyor.png', 5),
  ('temach', 'jalur_compressed_air', 'Jalur Compressed Air', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak ada kebocoran pada pneumatic, selang dan fitting. Pastikan tidak ada bunyi desis', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/temach/temach jalur compressed air.png', 6),
  ('temach', 'air_regulator', 'Air Regulator', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan udara 4 - 6 bar', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/temach/temach air regulator.png', 7),
  ('temach', 'heater_a_f', 'Heater (A-F)', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Tidak ada cacat pada kabel dan berfungsi normal', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/temach/temach heater a f.png', 8),
  ('temach', 'baut_turet', 'Baut Turet', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek, kencangkan bila perlu', 'Kunci L/Kunci Pas sesuai kebutuhan', 'Buat kencang, tidak aus, sesuai marka, berfungsi normal', '5''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/temach/temach baut turet.png', 9),
  ('jihcheng', 'konveyor_belt', 'Konveyor Belt', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap Kain dan Air', 'Bersih dari kotoran', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jihcheng/jihcheng konveyor belt.png', 1),
  ('jihcheng', 'flexible_konveyor_u', 'Flexible Konveyor U', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap Kain dan Air', 'Bersih dari kotoran', '5''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jihcheng/jihcheng flexible konveyor u.png', 2),
  ('jihcheng', 'suction_cup', 'Suction Cup', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap Kain dan Air', 'Bersih dan tidak lengket', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jihcheng/jihcheng suction cup.png', 3),
  ('jihcheng', 'pocket_pembawa_tube_dan_pack', 'Pocket Pembawa Tube dan Pack', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dijog 1 putaran dan dilap', 'Lap Kain dan Air', 'Bersih dan Tidak Lengket', '10''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/jihcheng/jihcheng pocket pembawa tube dan pack.png', 4),
  ('jihcheng', 'shaft_dan_bushing_pusher', 'Shaft dan Bushing Pusher', 'STANDAR PELUMASAN (LUBRICATING)', 'Dilumasi', 'Grease', 'Terlumasi merata', '5''', 'Mingguan (Setiap Selasa Shift 1)', 'mingguan', 'assets/images/jihcheng/jihcheng shaft dan bushing pusher.png', 5),
  ('jihcheng', 'bearing_rantai_tube_cam_pusher', 'Bearing Rantai Tube, Cam Pusher', 'STANDAR PELUMASAN (LUBRICATING)', 'Dilumasi', 'Grease', 'Terlumasi merata', '5''', 'Mingguan (Setiap Selasa Shift 1)', 'mingguan', 'assets/images/jihcheng/jihcheng bearing rantai tube cam pusher.png', 6),
  ('jihcheng', 'rantai_penggerak_utama_pocket_tube_pack', 'Rantai Penggerak Utama, Rantai Pocket Tube, Rantai Pocket Pack', 'STANDAR PELUMASAN (LUBRICATING)', 'Disemprot', 'Chain Lube', 'Terlumasi merata', '7''', 'Mingguan (Setiap Selasa Shift 1)', 'mingguan', 'assets/images/jihcheng/jihcheng rantai penggerak utama pocket tube pack.png', 7),
  ('jihcheng', 'regulator_angin_utama', 'Regulator Angin Utama', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan minimal 4 bar', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jihcheng/jihcheng regulator angin utama.png', 8),
  ('jihcheng', 'regulator_angin_chamber_hot_melt', 'Regulator Angin Chamber Hot Melt', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan 1.5 - 3 bar', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jihcheng/jihcheng regulator angin chamber hot melt.png', 9),
  ('jihcheng', 'sensor_tube_pack_nozzle_lem', 'Sensor : Tube, Pack, Nozzle Lem', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Sensor berfungsi', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jihcheng/jihcheng sensor tube pack nozzle lem.png', 10),
  ('jihcheng', 'pengecekan_tombol_emergency', 'Pengecekan Tombol Emergency', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Buzzer menyala', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jihcheng/jihcheng pengecekan tombol emergency.png', 11),
  ('jinsung_1_4', 'flexible_conveyor_infeed_belt_conveyor', 'Flexible Conveyor dan Infeed Belt Conveyor', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap Kain dan Air', 'Bersih dari kotoran', '7''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jinsung_1_4/jinsung 1-4 flexible conveyor infeed belt conveyor.png', 1),
  ('jinsung_1_4', 'pocket_pembawa_sachet', 'Pocket Pembawa Sachet', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dijog 1 putaran dan dilap', 'Lap Kain dan Air', 'Bersih dan Tidak Lengket', '30''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/jinsung_1_4/jinsung 1-4 pocket pembawa sachet.png', 2),
  ('jinsung_1_4', 'shaft_bushing_pusher_stacking', 'Shaft dan Bushing Pusher, dan Stacking', 'STANDAR PELUMASAN (LUBRICATING)', 'Dilumasi', 'Grease', 'Terlumasi merata', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/jinsung_1_4/jinsung 1-4 shaft bushing pusher stacking.png', 3),
  ('jinsung_1_4', 'rantai_penggerak', 'Rantai Penggerak', 'STANDAR PELUMASAN (LUBRICATING)', 'Dilumasi', 'Grease', 'Terlumasi merata', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/jinsung_1_4/jinsung 1-4 rantai penggerak.png', 4),
  ('jinsung_1_4', 'regulator_angin_stacking_cartoning_check_weigher', 'Regulator Angin Stacking 1-4, dan Mesin Cartoning, Check Weigher', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan minimal 4 bar', '1''', 'Harian (Setiap Senin Shift 1)', NULL, 'assets/images/jinsung_1_4/jinsung 1-4 regulator angin stacking cartoning check weigher.png', 5),
  ('jinsung_1_4', 'regulator_angin_chamber_hot_melt', 'Regulator Angin Chamber Hot Melt', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan 1.5 - 3 bar', '1''', 'Harian (Setiap Senin Shift 1)', NULL, 'assets/images/jinsung_1_4/jinsung 1-4 regulator angin chamber hot melt.png', 6),
  ('jinsung_1_4', 'sensor_sachet_pack_nozzle', 'Sensor : Sachet, Pack, Nozzle', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Sensor berfungsi', '1''', 'Harian (Setiap Senin Shift 1)', NULL, 'assets/images/jinsung_1_4/jinsung 1-4 sensor sachet pack nozzle.png', 7),
  ('jinsung_1_4', 'penekan_sachet', 'Penekan Sachet', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek, kencangkan bila perlu', 'Visual Control', 'Tidak rusak, tidak kendur', '1''', 'Harian (Setiap Senin Shift 1)', NULL, 'assets/images/jinsung_1_4/jinsung 1-4 penekan sachet.png', 8),
  ('jinsung_5', 'belt_conveyor_dan_pusher_pack', 'Belt Conveyor dan Pusher Pack', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Kuas/Quiltec dan Air', 'Bersih dari kotoran', '7''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jinsung_5/jinsung 5 belt conveyor dan pusher pack.png', 1),
  ('jinsung_5', 'pocket_pembawa_sachet', 'Pocket Pembawa Sachet', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dijog 1 putaran dan dilap', 'Kuas/Quiltec + Air Panas', 'Bersih dan Tidak Lengket', '30''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/jinsung_5/jinsung 5 pocket pembawa sachet.png', 2),
  ('jinsung_5', 'shaft_bushing_pusher_piston', 'Shaft dan Bushing Pusher dan Piston', 'STANDAR PELUMASAN (LUBRICATING)', 'Dilumasi', 'Grease', 'Terlumasi merata', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/jinsung_5/jinsung 5 shaft bushing pusher piston.png', 3),
  ('jinsung_5', 'rantai_penggerak_utama_cartoning', 'Rantai Penggerak Utama Cartoning', 'STANDAR PELUMASAN (LUBRICATING)', 'Dilumasi', 'Grease', 'Terlumasi merata', '5''', 'Mingguan (Setiap Senin Shift 1)', 'mingguan', 'assets/images/jinsung_5/jinsung 5 rantai penggerak utama cartoning.png', 4),
  ('jinsung_5', 'regulator_angin_conveyor_cartoning_check_weigher', 'Regulator Angin Conveyor Unit 1, Conveyor Unit 2, dan Mesin Cartoning, Check Weigher', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan minimal 4 bar', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jinsung_5/jinsung 5 regulator angin conveyor cartoning check weigher.png', 5),
  ('jinsung_5', 'regulator_angin_chamber_hot_melt', 'Regulator Angin Chamber Hot Melt', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan 1.5 - 3 bar', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jinsung_5/jinsung 5 regulator angin chamber hot melt.png', 6),
  ('jinsung_5', 'sensor_sachet_pack_nozzle', 'Sensor : Sachet, Pack, Nozzle', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Sensor berfungsi', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jinsung_5/jinsung 5 sensor sachet pack nozzle.png', 7),
  ('jinsung_5', 'timing_belt_conveyor', 'Timing Belt Conveyor', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak rusak', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/jinsung_5/jinsung 5 timing belt conveyor.png', 8),
  ('best_pack', 'body_best_pack', 'Body Best Pack', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall dan Air', 'Bersih dari kotoran', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/best_pack/best pack body best pack.png', 1),
  ('best_pack', 'konveyor_best_pack', 'Konveyor Best Pack', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall dan Air', 'Bersih dari kotoran', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/best_pack/best pack konveyor best pack.png', 2),
  ('best_pack', 'print_head_inkjet', 'Print Head Inkjet', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall dan Cleaner', 'Bersih dari kotoran', '5''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/best_pack/best pack print head inkjet.png', 3),
  ('best_pack', 'belt_conveyor_best_pack', 'Belt Conveyor Best Pack', 'STANDAR PENGECEKAN (INSPECTION)', 'Dicek', 'Visual Control', 'Tidak sobek', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/best_pack/best pack belt conveyor best pack.png', 4),
  ('best_pack', 'pisau_best_pack', 'Pisau Best Pack', 'STANDAR PENGECEKAN (INSPECTION)', 'Tes Fungsi', 'Manual', 'Tidak tumpul dan bisa memotong', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/best_pack/best pack pisau best pack.png', 5),
  ('best_pack', 'selang_angin_best_pack', 'Selang Angin Best pack', 'STANDAR PENGECEKAN (INSPECTION)', 'Tes Fungsi', 'Manual', 'Angin keluar', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/best_pack/best pack selang angin best pack.png', 6),
  ('fbd_jaw_chuan', 'body_mesin', 'Body Mesin', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disemprot', 'Selang, Wiper Lantai, Wypall dan air', 'Bagian luar bersih dari kotoran', '10''', 'Mingguan (Setiap Jumat akhir Shift 1)', 'mingguan', 'assets/images/fbd_jaw_chuan/fbd_jaw_chuan body mesin.png', 1),
  ('fbd_jaw_chuan', 'panel_fbd', 'Panel FBD', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall lembab dengan Alkohol 70%', 'Bagian luar bersih dari kotoran', '2''', 'Mingguan (Setiap Jumat akhir Shift 1)', 'mingguan', 'assets/images/fbd_jaw_chuan/fbd_jaw_chuan panel fbd.png', 2),
  ('fbd_jaw_chuan', 'tombol_tombol_pada_panel_fbd', 'Tombol-tombol pada panel FBD (Power, Timer, Heater)', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', 'Lampu indikator menyala', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_jaw_chuan/fbd_jaw_chuan tombol tombol pada panel fbd.png', 3),
  ('fbd_jaw_chuan', 'seal_bagtight', 'Seal Bagtight', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', 'Mengembang', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_jaw_chuan/fbd_jaw_chuan seal bagtight.png', 4),
  ('fbd_jaw_chuan', 'container_up_down', 'Container Up-Down', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', 'Kontainer dapat naik dan turun', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_jaw_chuan/fbd_jaw_chuan container up down.png', 5),
  ('fbd_jaw_chuan', 'shaking', 'Shaking', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', 'Filter bag bergerak kanan dan kiri', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_jaw_chuan/fbd_jaw_chuan shaking.png', 6),
  ('fbd_jaw_chuan', 'pressure_gauge_damper', 'Pressure Gauge Damper', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', '4-6 bar', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_jaw_chuan/fbd_jaw_chuan pressure gauge damper.png', 7),
  ('fbd_jaw_chuan', 'seal_container', 'Seal Container', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak ada kerusakan/robek/gompal/bocor', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_jaw_chuan/fbd_jaw_chuan seal container.png', 8),
  ('fbd_jaw_chuan', 'guarding_pengunci_kontainer', 'Guarding Pengunci Kontainer', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', 'Pengunci tidak kendor', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_jaw_chuan/fbd_jaw_chuan guarding pengunci kontainer.png', 9),
  ('fbd_jaw_chuan', 'container_mesh_dan_roda', 'Container (Mesh dan Roda)', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Mesh normal tidak sobek
Roda normal tidak macet/rusak', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_jaw_chuan/fbd_jaw_chuan container mesh dan roda.png', 10),
  ('fbd_jaw_chuan', 'filter_dan_bag_tight', 'Filter dan Bag Tight', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Filter tidak sobek
Seal tidak bocor', '10''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_jaw_chuan/fbd_jaw_chuan filter dan bag tight.png', 11),
  ('fbd_glatt', 'body_mesin', 'Body Mesin', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disemprot', 'Selang, Wiper Lantai, Wypall dan air', 'Bagian luar bersih dari kotoran', '10''', 'Harian (Setiap akhir Shift 1) *note: dilakukaan jika ada proses', NULL, 'assets/images/fbd_glatt/fbd_glatt body mesin.png', 1),
  ('fbd_glatt', 'panel_fbd', 'Panel FBD', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall lembab dengan Alkohol 70%', 'Bagian luar bersih dari kotoran', '2''', 'Harian (Setiap akhir Shift 1) *note: dilakukaan jika ada proses', NULL, 'assets/images/fbd_glatt/fbd_glatt panel fbd.png', 2),
  ('fbd_glatt', 'hmi_panel_fbd', 'HMI Panel FBD', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', 'Layar/display merespons ketika disentuh', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_glatt/fbd_glatt hmi panel fbd.png', 3),
  ('fbd_glatt', 'seal_bagtight', 'Seal Bagtight', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', 'Mengembang', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_glatt/fbd_glatt seal bagtight.png', 4),
  ('fbd_glatt', 'container_up_down', 'Container Up-Down', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', 'Kontainer dapat naik dan turun', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_glatt/fbd_glatt container up down.png', 5),
  ('fbd_glatt', 'shaking', 'Shaking', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', 'Filter bag bergerak kanan dan kiri', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_glatt/fbd_glatt shaking.png', 6),
  ('fbd_glatt', 'pressure_gauge_damper', 'Pressure Gauge Damper', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', '4-6 bar', '3''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_glatt/fbd_glatt pressure gauge damper.png', 7),
  ('fbd_glatt', 'seal_container', 'Seal Container', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak ada kerusakan/robek/gompal/bocor', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_glatt/fbd_glatt seal container.png', 8),
  ('fbd_glatt', 'guarding_pengunci_kontainer', 'Guarding Pengunci Kontainer', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', 'Pengunci tidak kendor', '1''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_glatt/fbd_glatt guarding pengunci kontainer.png', 9),
  ('fbd_glatt', 'container_mesh_dan_roda', 'Container (Mesh dan Roda)', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Mesh normal tidak sobek
Roda normal tidak macet/rusak', '2''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_glatt/fbd_glatt container mesh dan roda.png', 10),
  ('fbd_glatt', 'filter_dan_bag_tight', 'Filter dan Bag Tight', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Filter tidak sobek
Seal tidak bocor', '10''', 'Harian (Setiap Awal Shift 1)', NULL, 'assets/images/fbd_glatt/fbd_glatt filter dan bag tight.png', 11),
  ('supermixer', 'body_mesin', 'Body Mesin', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disemprot', 'Selang, Wiper Lantai, Wypall dan air', 'Bagian luar bersih dari kotoran', '10''', 'Harian (Setiap akhir Shift 1)', NULL, 'assets/images/supermixer/supermixer body mesin.png', 1),
  ('supermixer', 'pressure_gauge', 'Pressure Gauge', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan 5 - 6 bar', '1''', 'Harian (Setiap akhir Shift 1)', NULL, 'assets/images/supermixer/supermixer pressure gauge.png', 2),
  ('supermixer', 'timer', 'Timer', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', 'Berfungsi normal', '1''', 'Harian (Setiap akhir Shift 1)', NULL, 'assets/images/supermixer/supermixer timer.png', 3),
  ('supermixer', 'chopper', 'Chopper', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak retak', '1''', 'Harian (Setiap akhir Shift 1)', NULL, 'assets/images/supermixer/supermixer chopper.png', 4),
  ('supermixer', 'agitator', 'Agitator', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Manual', 'Audio Control', 'Tidak bergesek', '1''', 'Harian (Setiap akhir Shift 1)', NULL, 'assets/images/supermixer/supermixer agitator.png', 5),
  ('supermixer', 'valve_hopper', 'Valve Hopper', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Valve tertutup rapat', '1''', 'Harian (Setiap akhir Shift 1)', NULL, 'assets/images/supermixer/supermixer valve hopper.png', 6),
  ('supermixer', 'discharge_valve', 'Discharge Valve', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek, kencangkan bila perlu', 'Visual Control', 'Klem kencang, tidak ada kebocoran', '1''', 'Harian (Setiap akhir Shift 1)', NULL, 'assets/images/supermixer/supermixer discharge valve.png', 7),
  ('storage_tank', 'body_storage_tank', 'Body Storage Tank', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall dan air', 'Bagian luar bersih dari kotoran', '15''', 'Bulanan (Setiap awal Shift 1) note: tidak mengikat hari', 'bulanan', 'assets/images/storage_tank/storage_tank body storage tank.png', 1),
  ('storage_tank', 'jalur_pipa_storage_tank', 'Jalur Pipa Storage Tank', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall dan air', 'Bagian luar bersih dari kotoran', '10''', 'Bulanan (Setiap awal Shift 1) note: tidak mengikat hari', 'bulanan', 'assets/images/storage_tank/storage_tank jalur pipa storage tank.png', 2),
  ('storage_tank', 'motor_dan_gearbox', 'Motor dan gearbox', 'STANDAR PENGECEKAN (INSPECTION)', 'Dicek', 'Visual Control', 'Tidak ada kebocoran oli', '2''', 'Mingguan (Setiap awal Shift 1) note: tidak mengikat hari', 'mingguan', 'assets/images/storage_tank/storage_tank motor dan gearbox.png', 3),
  ('storage_tank', 'baling_baling_agitator', 'Baling-baling Agitator', 'STANDAR PENGECEKAN (INSPECTION)', 'Dicek', 'Visual Control', 'Tidak ada kebocoran oli', '2''', 'Mingguan (Setiap awal Shift 1) note: tidak mengikat hari', 'mingguan', 'assets/images/storage_tank/storage_tank baling baling agitator.png', 4),
  ('storage_tank', 'seal_mainhole', 'Seal mainhole', 'STANDAR PENGECEKAN (INSPECTION)', 'Dicek', 'Visual Control', 'Tidak sobek', '1''', 'Mingguan (Setiap awal Shift 1) note: tidak mengikat hari', 'mingguan', 'assets/images/storage_tank/storage_tank seal mainhole.png', 5),
  ('storage_tank', 'pengunci_tutup_mainhole', 'Pengunci tutup mainhole', 'STANDAR PENGECEKAN (INSPECTION)', 'Dicek', 'Visual Control', 'Tidak kendur, bisa tertutup rapat', '2''', 'Mingguan (Setiap awal Shift 1) note: tidak mengikat hari', 'mingguan', 'assets/images/storage_tank/storage_tank pengunci tutup mainhole.png', 6),
  ('storage_tank', 'clamp_ferrule', 'Clamp Ferrule', 'STANDAR PENGECEKAN (INSPECTION)', 'Dicek', 'Visual Control', 'Tidak kendur, bisa tertutup rapat', '2''', 'Mingguan (Setiap awal Shift 1) note: tidak mengikat hari', 'mingguan', 'assets/images/storage_tank/storage_tank clamp ferrule.png', 7),
  ('mixing_tank', 'body_mixing_tank', 'Body Mixing Tank', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall dan air', 'Bagian luar bersih dari kotoran', '15''', 'Mingguan (Setiap Senin Shift 1) *note: dilakukaan jika ada proses', 'mingguan', 'assets/images/mixing_tank/mixing_tank body mixing tank.png', 1),
  ('mixing_tank', 'jalur_pipa_mixing_tank', 'Jalur Pipa Mixing Tank', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall dan air', 'Bagian luar bersih dari kotoran', '10''', 'Mingguan (Setiap Senin Shift 1) *note: dilakukaan jika ada proses', 'mingguan', 'assets/images/mixing_tank/mixing_tank jalur pipa mixing tank.png', 2),
  ('mixing_tank', 'body_panel_hmi', 'Body Panel HMI', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall lembab dengan Alkohol 70%', 'Bagian luar bersih dari kotoran', '2''', 'Mingguan (Setiap Senin Shift 1) *note: dilakukaan jika ada proses', 'mingguan', 'assets/images/mixing_tank/mixing_tank body panel hmi.png', 3),
  ('mixing_tank', 'agitator', 'Agitator', 'STANDAR PENGECEKAN (INSPECTION)', 'Test Fungsi', 'Visual Control', 'Berfungsi normal', '1''', 'Harian (Setiap Awal Shift 1) *note: dilakukaan jika ada proses', NULL, 'assets/images/mixing_tank/mixing_tank agitator.png', 4),
  ('mixing_tank', 'seal_mainhole', 'Seal mainhole', 'STANDAR PENGECEKAN (INSPECTION)', 'Dicek', 'Visual Control', 'Tidak sobek', '2''', 'Harian (Setiap Awal Shift 1) *note: dilakukaan jika ada proses', NULL, 'assets/images/mixing_tank/mixing_tank seal mainhole.png', 5)
ON CONFLICT ("machine_key", "field_name") DO NOTHING;

-- Storage Tank Tetrapak: clone master_part dari storage_tank (Silverson) --
-- isi Metode/Alat/Standard/Durasi/Pelaksanaan/foto/section/urutan sama persis,
-- cuma unit fisiknya beda. Lihat database/migrations/2026-08-20_add_storage_tank_tetrapak.sql
INSERT INTO "master_part" ("machine_key", "field_name", "label", "section", "metode", "alat", "standard", "durasi", "pelaksanaan", "highlight", "image_path", "urutan")
SELECT 'storage_tank_tetrapak', "field_name", "label", "section", "metode", "alat", "standard", "durasi", "pelaksanaan", "highlight", "image_path", "urutan"
FROM "master_part" WHERE "machine_key" = 'storage_tank'
ON CONFLICT ("machine_key", "field_name") DO NOTHING;

-- Granulator (Compounding)
INSERT INTO "mesin" ("nama_mesin")
SELECT 'Granulator'
WHERE NOT EXISTS (SELECT 1 FROM "mesin" WHERE "nama_mesin" = 'Granulator');

INSERT INTO "master_part" ("machine_key", "field_name", "label", "section", "metode", "alat", "standard", "durasi", "pelaksanaan", "highlight", "image_path", "urutan") VALUES
  ('granulator', 'body_mesin', 'Body Mesin', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Wypall dan air', 'Bagian luar bersih dari kotoran', '10''', 'Harian', NULL, 'assets/images/granulator/granulator body mesin.png', 1),
  ('granulator', 'perforated_mesh', 'Perforated Mesh', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak sobek, terpasang rapat', '1''', 'Harian', NULL, 'assets/images/granulator/granulator perforated mesh.png', 2),
  ('granulator', 'baling_baling_pisau', 'Baling-baling Pisau', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Test Fungsi', 'Visual Control', 'Berfungsi normal, tidak bergesekan dengan mesh', '1''', 'Harian', NULL, 'assets/images/granulator/granulator baling baling pisau.png', 3),
  ('granulator', 'seal_corong', 'Seal Corong', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tidak sobek', '1''', 'Mingguan', 'mingguan', 'assets/images/granulator/granulator seal corong.png', 4)
ON CONFLICT ("machine_key", "field_name") DO NOTHING;

-- Check Weigher (Wrapping dan Pack Cartoning)
INSERT INTO "mesin" ("nama_mesin")
SELECT 'Check Weigher Jinsung 1' WHERE NOT EXISTS (SELECT 1 FROM "mesin" WHERE "nama_mesin" = 'Check Weigher Jinsung 1');
INSERT INTO "mesin" ("nama_mesin")
SELECT 'Check Weigher Jinsung 2' WHERE NOT EXISTS (SELECT 1 FROM "mesin" WHERE "nama_mesin" = 'Check Weigher Jinsung 2');
INSERT INTO "mesin" ("nama_mesin")
SELECT 'Check Weigher Jinsung 3' WHERE NOT EXISTS (SELECT 1 FROM "mesin" WHERE "nama_mesin" = 'Check Weigher Jinsung 3');
INSERT INTO "mesin" ("nama_mesin")
SELECT 'Check Weigher Jinsung 4' WHERE NOT EXISTS (SELECT 1 FROM "mesin" WHERE "nama_mesin" = 'Check Weigher Jinsung 4');
INSERT INTO "mesin" ("nama_mesin")
SELECT 'Check Weigher Jinsung 5' WHERE NOT EXISTS (SELECT 1 FROM "mesin" WHERE "nama_mesin" = 'Check Weigher Jinsung 5');

INSERT INTO "master_part" ("machine_key", "field_name", "label", "section", "metode", "alat", "standard", "durasi", "pelaksanaan", "highlight", "image_path", "urutan") VALUES
  ('check_weigher', 'lengan_rejector', 'Lengan Rejector', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Qulitec dan Air', 'Bersih dari kotoran', '2''', 'Harian', NULL, 'assets/images/check_weigher/check_weigher lengan rejector.png', 1),
  ('check_weigher', 'body_mesin_check_weigher', 'Body Mesin Check Weigher', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Qulitec dan Air', 'Bersih dari kotoran', '5''', 'Harian', NULL, 'assets/images/check_weigher/check_weigher body mesin check weigher.png', 2),
  ('check_weigher', 'vanbelt', 'Vanbelt', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Qulitec dan Air', 'Bersih dan Tidak Lengket', '30''', 'Mingguan/Saat CM Ilapak', 'mingguan', 'assets/images/check_weigher/check_weigher vanbelt.png', 3),
  ('check_weigher', 'belt_check_weigher', 'Belt Check Weigher', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Quilltec', 'Bersih dan Tidak Lengket', '2''', 'Harian', NULL, 'assets/images/check_weigher/check_weigher belt check weigher.png', 4),
  ('check_weigher', 'roller_dan_bearing', 'Roller dan bearing', 'STANDAR PEMBERSIHAN (CLEANING)', 'Disemprot', 'Air Gun', 'Bersih dari kotoran', '30''', 'Mingguan/Saat CM Ilapak', 'mingguan', 'assets/images/check_weigher/check_weigher roller dan bearing.png', 5),
  ('check_weigher', 'pelumasan_roller_dan_bearing', 'Roller dan bearing', 'STANDAR PELUMASAN (LUBRICATING)', 'Dilumasi', 'Grease', 'Terlumasi merata', '30''', 'Mingguan/Saat CM ilapak', 'mingguan', 'assets/images/check_weigher/check_weigher pelumasan roller dan bearing.png', 6),
  ('check_weigher', 'bearing_roller', 'Bearing Roller', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek, kencangkan bila perlu', 'Visual Control', 'Tidak rusak, tidak kendur', '2''', 'Harian', NULL, 'assets/images/check_weigher/check_weigher bearing roller.png', 7),
  ('check_weigher', 'kaki_kaki', 'Kaki-Kaki', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek, kencangkan bila perlu', 'Visual Control', 'Tidak rusak, tidak kendur', '2''', 'Harian', NULL, 'assets/images/check_weigher/check_weigher kaki kaki.png', 8),
  ('check_weigher', 'pengecekan_belt_check_weigher', 'Belt Check Weigher', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek, kencangkan bila perlu', 'Visual Control', 'Tidak sobek', '5''', 'Harian', NULL, 'assets/images/check_weigher/check_weigher pengecekan belt check weigher.png', 9)
ON CONFLICT ("machine_key", "field_name") DO NOTHING;

-- Conveyor SIG (Wrapping dan Pack Cartoning)
INSERT INTO "mesin" ("nama_mesin")
SELECT 'Conveyor SIG 5' WHERE NOT EXISTS (SELECT 1 FROM "mesin" WHERE "nama_mesin" = 'Conveyor SIG 5');
INSERT INTO "mesin" ("nama_mesin")
SELECT 'Conveyor SIG 6' WHERE NOT EXISTS (SELECT 1 FROM "mesin" WHERE "nama_mesin" = 'Conveyor SIG 6');

INSERT INTO "master_part" ("machine_key", "field_name", "label", "section", "metode", "alat", "standard", "durasi", "pelaksanaan", "highlight", "image_path", "urutan") VALUES
  ('conveyor_sig', 'meja', 'Meja', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap kering', 'Bersih dari kotoran', '2''', 'Harian', NULL, 'assets/images/conveyor_sig/conveyor_sig meja.png', 1),
  ('conveyor_sig', 'konveyor_belt_flexible_konveyor', 'Konveyor belt, Flexible Konveyor', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap kering', 'Bersih dari kotoran', '2''', 'Harian', NULL, 'assets/images/conveyor_sig/conveyor_sig konveyor belt flexible konveyor.png', 2),
  ('conveyor_sig', 'badan_konveyor', 'Badan Konveyor', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap kering', 'Bersih dari kotoran', '2''', 'Harian', NULL, 'assets/images/conveyor_sig/conveyor_sig badan konveyor.png', 3),
  ('conveyor_sig', 'sensor_untuk_batch', 'Sensor untuk batch', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap Kain dan Air', 'Bersih dari kotoran', '2''', 'Harian', NULL, 'assets/images/conveyor_sig/conveyor_sig sensor untuk batch.png', 4),
  ('conveyor_sig', 'roller', 'Roller', 'STANDAR PEMBERSIHAN (CLEANING)', 'Dilap', 'Lap Kain dan Air', 'Bersih dari kotoran', '15''', 'Mingguan', 'mingguan', 'assets/images/conveyor_sig/conveyor_sig roller.png', 5),
  ('conveyor_sig', 'rantai_penggerak_utama', 'Rantai Penggerak Utama', 'STANDAR PELUMASAN (LUBRICATING)', 'Disemprot', 'Chain Lube', 'Terlumasi merata', '5''', 'Mingguan', 'mingguan', 'assets/images/conveyor_sig/conveyor_sig rantai penggerak utama.png', 6),
  ('conveyor_sig', 'pengecekan_konveyor_belt_flexible_konveyor', 'Konveyor belt, Flexible Konveyor', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan minimal 4 bar', '1''', 'Harian', NULL, 'assets/images/conveyor_sig/conveyor_sig pengecekan konveyor belt flexible konveyor.png', 7),
  ('conveyor_sig', 'pengecekan_roller', 'Roller', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Dicek', 'Visual Control', 'Tekanan 1.5 - 3 bar', '1''', 'Harian', NULL, 'assets/images/conveyor_sig/conveyor_sig pengecekan roller.png', 8),
  ('conveyor_sig', 'sensor', 'Sensor', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Sensor berfungsi', '5''', 'Mingguan', 'mingguan', 'assets/images/conveyor_sig/conveyor_sig sensor.png', 9),
  ('conveyor_sig', 'control_panel', 'Control Panel', 'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)', 'Tes Fungsi', 'Visual Control', 'Panel berfungsi', '5''', 'Mingguan', 'mingguan', 'assets/images/conveyor_sig/conveyor_sig control panel.png', 10)
ON CONFLICT ("machine_key", "field_name") DO NOTHING;



