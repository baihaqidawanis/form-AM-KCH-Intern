-- Hapus SEMUA data submission AM testing (18 mesin + kendala-nya) sekaligus,
-- reset ID balik ke 1. TIDAK nyentuh data master (mesin, roles, tag,
-- kategori/korelasi/klasifikasi, master_part, users, audit_log).
-- CASCADE otomatis ikut kosongin tabel kendala_* yang FK-related.
TRUNCATE TABLE
  "tb_mesin_sig", "kendala_sig",
  "tb_mesin_joeya", "kendala_joeya",
  "tb_mesin_illapak_1_2", "kendala_illapak_1_2",
  "tb_mesin_illapak_3_12", "kendala_illapak_3_12",
  "tb_mesin_unifill_b", "kendala_unifill_b",
  "tb_mesin_chimei", "kendala_chimei",
  "tb_mesin_temach", "kendala_temach",
  "tb_mesin_jihcheng", "kendala_jihcheng",
  "tb_mesin_jinsung_1_4", "kendala_jinsung_1_4",
  "tb_mesin_jinsung_5", "kendala_jinsung_5",
  "tb_mesin_best_pack", "kendala_best_pack",
  "tb_mesin_cosmec", "kendala_cosmec",
  "tb_mesin_fbd_jaw_chuan", "kendala_fbd_jaw_chuan",
  "tb_mesin_fbd_glatt", "kendala_fbd_glatt",
  "tb_mesin_supermixer", "kendala_supermixer",
  "tb_mesin_storage_tank", "kendala_storage_tank",
  "tb_mesin_storage_tank_tetrapak", "kendala_storage_tank_tetrapak",
  "tb_mesin_mixing_tank", "kendala_mixing_tank"
  RESTART IDENTITY;
