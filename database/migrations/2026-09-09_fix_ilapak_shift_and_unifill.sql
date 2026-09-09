-- Synchronize upgraded databases with the Ilapak shift configuration.
-- Safe to rerun because the target values are deterministic.
UPDATE master_part
SET shift_schedule = '1,2,3'
WHERE machine_key = 'illapak_1_2'
  AND field_name IN ('position_indicator_sealing_vertical', 'vacum_sliter');

UPDATE master_part
SET shift_schedule = '1,2,3'
WHERE machine_key = 'illapak_3_12'
  AND field_name IN ('position_indicator_sealing_vertical', 'vacum_sliter', 'alarm_temperature');
