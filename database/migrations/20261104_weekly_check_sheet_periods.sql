BEGIN;
ALTER TABLE am_period_signatures DROP CONSTRAINT IF EXISTS am_period_signatures_periode_check;
ALTER TABLE am_period_signatures ADD CONSTRAINT am_period_signatures_periode_check CHECK (periode IN (1, 2, 11, 12, 13, 14));
COMMENT ON COLUMN am_period_signatures.periode IS '1/2: legacy half-month; 11/12/13/14: days 1-7, 8-14, 15-22, 23-end';
COMMIT;
