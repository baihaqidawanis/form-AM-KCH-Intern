-- Rename 17 tabel mesin jadi prefix "tb_mesin_" biar gampang dibedain dari
-- tabel master lain (mesin, kategori, dst). Tabel kendala_* TIDAK ikut
-- berubah -- cuma tabel utama per modul. URL/menu/nama folder view TIDAK
-- berubah (tetap /chimei, /sig, dst) -- lihat BaseMachineController::sqlTable().
ALTER TABLE IF EXISTS "sig" RENAME TO "tb_mesin_sig";
ALTER TABLE IF EXISTS "joeya" RENAME TO "tb_mesin_joeya";
ALTER TABLE IF EXISTS "illapak_1_2" RENAME TO "tb_mesin_illapak_1_2";
ALTER TABLE IF EXISTS "illapak_3_12" RENAME TO "tb_mesin_illapak_3_12";
ALTER TABLE IF EXISTS "unifill_b" RENAME TO "tb_mesin_unifill_b";
ALTER TABLE IF EXISTS "chimei" RENAME TO "tb_mesin_chimei";
ALTER TABLE IF EXISTS "temach" RENAME TO "tb_mesin_temach";
ALTER TABLE IF EXISTS "jihcheng" RENAME TO "tb_mesin_jihcheng";
ALTER TABLE IF EXISTS "jinsung_1_4" RENAME TO "tb_mesin_jinsung_1_4";
ALTER TABLE IF EXISTS "jinsung_5" RENAME TO "tb_mesin_jinsung_5";
ALTER TABLE IF EXISTS "best_pack" RENAME TO "tb_mesin_best_pack";
ALTER TABLE IF EXISTS "cosmec" RENAME TO "tb_mesin_cosmec";
ALTER TABLE IF EXISTS "fbd_jaw_chuan" RENAME TO "tb_mesin_fbd_jaw_chuan";
ALTER TABLE IF EXISTS "fbd_glatt" RENAME TO "tb_mesin_fbd_glatt";
ALTER TABLE IF EXISTS "supermixer" RENAME TO "tb_mesin_supermixer";
ALTER TABLE IF EXISTS "storage_tank" RENAME TO "tb_mesin_storage_tank";
ALTER TABLE IF EXISTS "mixing_tank" RENAME TO "tb_mesin_mixing_tank";
