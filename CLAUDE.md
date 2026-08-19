# CLAUDE.md

Panduan konteks buat Claude Code kerja di project ini. Baca [README.md](./README.md) dulu buat gambaran umum, dan [DOCS_MD/TECHNICAL_OVERVIEW.md](./DOCS_MD/TECHNICAL_OVERVIEW.md) buat arsitektur detail (pola tabel, cara nambah mesin baru, dst).

## Ide/Arahan Pengembangan (belum dikerjakan)

### Master data untuk detail part mesin (foto, Metode, Alat, Standard, Durasi, Pelaksanaan)

**Kondisi saat ini**: detail per part (contoh di halaman "Add AM Cosmec" — Body Panel HMI, Body Mesin, dst) itu **hardcoded di view**, bukan dari database:
- `app/views/partials/{mesin}/add.php` — array PHP `$part_details`, `$image_names`, `$sections` (contoh: `app/views/partials/cosmec/add.php:8-77`).
- Foto fisik di `assets/images/{mesin}/`, naming pakai spasi (misal `cosmec body panel hmi.png`).
- Pola sama berulang di ke-17 view mesin (chimei, temach, joeya, jinsung, fbd_glatt, fbd_jaw_chuan, illapak, mixing_tank, storage_tank, supermixer, unifill_b, best_pack, jihcheng, sig, dll).
- Tabel `{mesin}` di DB cuma nyimpen **hasil isian** (kondisi OK/NOK per part per record), sama sekali nggak nyimpen metode/alat/standard/durasi/foto.

**Arah yang diminta user**: jadikan detail part ini **master data yang CRUD-able oleh superadmin** — bisa hapus/ubah foto/ubah teks isian tabel (Metode, Alat, Standard, Durasi, Pelaksanaan) tanpa edit kode. Yang tetap statis/dinamis-per-submission cuma bagian **Kondisi** (radio Baik/Tidak Baik).

**Kenapa ini refactor menengah-besar** (bukan tambahan kecil):
1. Perlu tabel master baru, misal `master_part` (kolom: mesin/machine_key, field_name, label, section, metode, alat, standard, durasi, pelaksanaan, image_path, urutan).
2. Perlu CRUD controller + view admin baru buat superadmin kelola master part, termasuk upload foto yang proper (sekarang taruh file manual).
3. Refactor ke-17 file `add.php` (cek juga `edit_data.php` kalau formatnya serupa) — ganti array hardcoded jadi query ke tabel master berdasarkan `machineKey`.
4. Migrasi data existing (isi hardcoded ke-17 mesin → INSERT ke tabel master) supaya data lama nggak hilang.
5. Logic pengelompokan section & warna highlight "Mingguan"/"Bulanan" (sekarang inferred dari string) idealnya jadi kolom eksplisit di master.
6. Keputusan desain: relasi part→mesin pakai `machineKey` (string, ikut pola `BaseMachineController`) atau `id_mesin` (int, ikut tabel `mesin` yang sudah ada).

**Struktur pendukung yang sudah ada**: `BaseMachineController` ([system/BaseMachineController.php](./system/BaseMachineController.php)) sudah punya konsep `$machineKey` + `$parts` (field→label) yang dilempar ke view — jadi pola "mesin → daftar part" udah ada secara implisit di controller, tinggal diperluas jadi query DB alih-alih array statis di dalam class.

**Status**: belum dieksekusi, masih tahap diskusi arah. Kalau mau lanjut, mulai dari desain skema tabel master dulu sebelum refactor 17 view sekaligus.
