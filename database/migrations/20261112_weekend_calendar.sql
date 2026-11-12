-- Run once on the Form AM database after pulling the calendar feature.
-- Existing manual holidays remain holidays. No AM record is changed.
-- Saturday/Sunday defaults begin on 2026-11-12 in OperationalCalendar.php.
-- Signed documents created before this policy keep their previous calendar behavior.
BEGIN;
ALTER TABLE operational_calendar
  ADD COLUMN IF NOT EXISTS is_working_day boolean NOT NULL DEFAULT false;
COMMIT;
