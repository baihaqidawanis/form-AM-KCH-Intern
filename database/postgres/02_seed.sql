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

