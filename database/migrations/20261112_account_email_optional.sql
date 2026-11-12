-- Email is no longer required for accounts. Existing values and uniqueness are preserved.
BEGIN;
ALTER TABLE users ALTER COLUMN email DROP NOT NULL;
COMMIT;
