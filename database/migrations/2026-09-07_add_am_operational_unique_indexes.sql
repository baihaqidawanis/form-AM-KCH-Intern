-- Hard guard dinamis untuk satu form AM per mesin/tanggal operasional.
-- NULL shift diperlakukan sebagai Shift 1, sehingga mode satu-shift hanya boleh
-- menyimpan satu form per hari; mode multi-shift boleh menyimpan Shift 1, 2, dan 3.
-- Jalankan setelah audit duplikat bernilai 0 dan kolom shift/operational_date tersedia.

DO $$
DECLARE
    t text;
BEGIN
    FOREACH t IN ARRAY ARRAY[
        'sig', 'joeya', 'unifill_b', 'chimei', 'temach', 'jihcheng',
        'jinsung_1_4', 'jinsung_5', 'best_pack', 'cosmec',
        'fbd_jaw_chuan', 'fbd_glatt', 'supermixer', 'storage_tank',
        'storage_tank_tetrapak', 'mixing_tank', 'granulator',
        'check_weigher', 'conveyor_sig', 'illapak_1_2', 'illapak_3_12'
    ]
    LOOP
        -- Bersihkan nama index unik lama dari migration sebelumnya.
        EXECUTE format('DROP INDEX IF EXISTS public.%I', 'uq_tb_mesin_' || t || '_operational');
        EXECUTE format('DROP INDEX IF EXISTS public.%I', 'uq_tb_mesin_' || t || '_operational_shift');
        EXECUTE format('DROP INDEX IF EXISTS public.%I', 'uq_' || t || '_operational_shift');

        EXECUTE format(
            'CREATE UNIQUE INDEX IF NOT EXISTS %I ON public.%I (mesin, operational_date, COALESCE(shift, ''1''))',
            'uq_tb_mesin_' || t || '_operational_shift',
            'tb_mesin_' || t
        );
    END LOOP;
END $$;