-- Proteksi "Super Admin" (disetujui mentor): cuma boleh ADA 1 akun super admin
-- di seluruh sistem, dan gak ada Administrator lain (sesama role_id=1) yang
-- bisa ubah role/status/hapus akun super admin ini. Administrator biasa tetap
-- bebas saling kelola sesama Administrator (bukan super admin).
--
-- Pakai kolom boolean + partial unique index (bukan cuma hardcode string
-- username di kode) -- ini dijaga di level DATABASE: kalau ada yang nyoba
-- bikin baris ke-2 dengan is_super_admin=true (via SQL langsung sekalipun),
-- Postgres nolak duluan karena unique index-nya cuma ngizinin 1 baris TRUE.
ALTER TABLE "users" ADD COLUMN IF NOT EXISTS "is_super_admin" boolean NOT NULL DEFAULT false;
CREATE UNIQUE INDEX IF NOT EXISTS "uq_users_single_super_admin"
  ON "users" ((is_super_admin))
  WHERE is_super_admin = true;

-- Tandai akun 'superadmin' seed awal sebagai satu-satunya super admin.
UPDATE "users" SET "is_super_admin" = true WHERE "username" = 'superadmin';
