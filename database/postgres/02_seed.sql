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
  ('49', 'Unifill A')
ON CONFLICT DO NOTHING;
SELECT setval(pg_get_serial_sequence('"mesin"', 'id'), COALESCE((SELECT MAX("id") FROM "mesin"), 1));

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

-- line (39 baris)
INSERT INTO "line" ("id", "line_name", "area_id") OVERRIDING SYSTEM VALUE VALUES
  ('1', 'Line_A', '1'),
  ('2', 'Line_B', '2'),
  ('3', 'Compounding', '3'),
  ('4', 'Minor', '3'),
  ('5', 'Others', '3'),
  ('6', 'Sodbic', '3'),
  ('7', 'Line_B', '1'),
  ('8', 'Line_A', '2'),
  ('9', 'Line_C', '1'),
  ('12', 'Line_D', '1'),
  ('13', 'Line_C', '2'),
  ('14', 'Line_D', '2'),
  ('15', 'Line_E', '2'),
  ('16', 'Line_E', '1'),
  ('17', 'Line_F', '1'),
  ('18', 'Line_F', '2'),
  ('19', 'Line_G', '1'),
  ('20', 'Line_G', '2'),
  ('21', 'Line_H', '1'),
  ('22', 'Line_H', '2'),
  ('23', 'Line_J', '1'),
  ('24', 'Line_J', '2'),
  ('26', 'Line_K', '2'),
  ('27', 'Line_K', '1'),
  ('28', 'Line_L', '1'),
  ('29', 'Line_L', '2'),
  ('30', 'Line_M', '1'),
  ('31', 'Line_M', '2'),
  ('32', 'Line_N', '1'),
  ('33', 'Line_N', '2'),
  ('35', 'Line_S', '1'),
  ('36', 'Line_S', '2'),
  ('37', 'Line_T', '1'),
  ('38', 'Line_T', '2'),
  ('39', 'Line_R', '2'),
  ('43', 'None', '1'),
  ('44', 'Kemas 1', '2'),
  ('45', 'Kemas 2', '2'),
  ('46', 'Filling', '1')
ON CONFLICT DO NOTHING;
SELECT setval(pg_get_serial_sequence('"line"', 'id'), COALESCE((SELECT MAX("id") FROM "line"), 1));

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
INSERT INTO "users" ("nama", "email", "username", "password", "account_status", "user_role_id")
VALUES ('Super Admin', 'admin@localhost', 'superadmin', '$2y$10$XT.XFKi3xXDkv12zaWU5VuWnVbmMORonOFJbVe/mOVsXWq2VGfLuy', 'Active', 1)
ON CONFLICT DO NOTHING;

