-- Jadwal shift adalah metadata historis part dan harus ikut tersnapshot.
ALTER TABLE "form_part_snapshot"
  ADD COLUMN IF NOT EXISTS "shift_schedule" varchar(10) NOT NULL DEFAULT '1';

-- Backfill terbaik untuk snapshot lama. Form baru selalu menyimpan nilai saat submit.
UPDATE "form_part_snapshot" s
SET "shift_schedule" = COALESCE(NULLIF(mp."shift_schedule", ''), '1')
FROM "master_part" mp
WHERE s."machine_key" = mp."machine_key"
  AND s."field_name" = mp."field_name";
