# ✅ Daftar Test Case & Status — Form AM

> Semua case di bawah **otomatis** (PHPUnit/Playwright), dijalankan ulang & dicek hasilnya **14 Agustus 2026** sebelum dokumen ini ditulis — bukan asumsi. Cara jalanin ulang: lihat [`README.md`](./README.md) di folder ini. Case yang butuh mata manusia (gak diotomasi) ada di [`../DOCS_MD/TESTING.md`](../DOCS_MD/TESTING.md) bagian "Bagian B — Manual".
>
> **Hasil run terakhir: 38/38 PHPUnit ✅, 1/1 Playwright ✅ — semua lolos, 401 assertion.**

## Auth (`tests/Feature/AuthTest.php`)

| # | Case | Status |
|---|---|---|
| 1 | Login sukses & bisa akses Home — role Administrator | ✅ |
| 2 | Login sukses & bisa akses Home — role Manager | ✅ |
| 3 | Login sukses & bisa akses Home — role Supervisor | ✅ |
| 4 | Login sukses & bisa akses Home — role Staff/Operator | ✅ |
| 5 | Login gagal dengan password salah — tetap di halaman login | ✅ |

## Lockout (`tests/Feature/LockoutTest.php`)

| # | Case | Status |
|---|---|---|
| 6 | 3x salah password berturut-turut → akun terkunci (`Blocked`), pesan jelas, password benar pun tetap ditolak selama terkunci (pakai akun throwaway, gak ganggu akun lain) | ✅ |

## Registrasi (`tests/Feature/RegistrationTest.php`)

| # | Case | Status |
|---|---|---|
| 7 | Registrasi akun baru sukses (row masuk DB), status otomatis `Pending`, login langsung setelah daftar ditolak dengan pesan "akun belum aktif" | ✅ |

## RBAC (`tests/Feature/RbacTest.php`)

| # | Case | Status |
|---|---|---|
| 8 | Manager **tidak bisa** akses halaman "Add" (403) | ✅ |
| 9 | Manager **bisa** delete record siapapun (sesuai URS 3.1) | ✅ |
| 10 | Operator **tidak bisa** `edit_data` record milik orang lain (403) | ✅ |
| 11 | Operator **bisa** `edit_data` record miliknya sendiri | ✅ |

## CRUD Mesin — Lifecycle Penuh (`tests/Feature/MachineCrudTest.php`)

| # | Case | Status |
|---|---|---|
| 12 | Chimei (single-mesin, hidden input): submit 1 NOK → view (badge NOK + teks kendala) → edit_data (prefill benar + gambar part muncul) → export PDF valid | ✅ |
| 13 | Illapak 1-2 (multi-mesin, dropdown): submit 1 NOK → view → edit_data (+ gambar part) → export PDF valid | ✅ |
| 14 | SIG (field tambahan `value_tekanan_angin`): submit 1 NOK → nilai extra field muncul benar di view, gambar part muncul di edit_data (termasuk kasus antistatic 3-opsi) | ✅ |

## Alur Approval (`tests/Feature/ApprovalFlowTest.php`)

| # | Case | Status |
|---|---|---|
| 15 | Approve manual record NOK → status berubah jadi `Approved` di list | ✅ |
| 16 | Reject manual record NOK → status berubah jadi `Not Approved` di list | ✅ |

## Export (`tests/Feature/ExportFormatsTest.php`)

| # | Case | Status |
|---|---|---|
| 17 | Export CSV/Word/Excel **tidak crash** walau list mesin kosong (0 record) — regresi guard bug Round 37 | ✅ |
| 18 | Export CSV & Excel valid & berisi data yang benar saat ada record | ✅ |

## Audit Trail (`tests/Feature/AuditTrailTest.php`)

| # | Case | Status |
|---|---|---|
| 19 | Submit record → tercatat di Audit Trail dengan action `add`; buka `view` record itu → **tidak** ikut tercatat (by design, noise reduction) | ✅ |

## Keamanan — XSS (`tests/Feature/XssEscapingTest.php`)

| # | Case | Status |
|---|---|---|
| 20 | Teks kendala berisi payload `<script>` → tampil ter-escape (teks biasa) di `view` & `edit_data`, **tidak** tereksekusi | ✅ |

## Smoke Test — Semua 17 Modul + Halaman Infra (`tests/Feature/SmokeTest.php`)

| # | Case | Status |
|---|---|---|
| 21-37 | `list2` & `add` tiap 17 modul mesin (SIG, Joeya, Illapak 1-2, Illapak 3-12, Unifill B, Chimei, Temach, Jihcheng, Jinsung 1-4, Jinsung 5, Best Pack, Cosmec, FBD Jaw Chuan, FBD Glatt, Supermixer, Storage Tank, Mixing Tank) — HTTP 200, nol error/warning/deprecated bocor ke halaman | ✅ (17/17) |
| 38 | Halaman infra (Home, Approval, Users, Roles, Tag, Audit Trail, Panduan) — HTTP 200, nol error | ✅ |

## E2E Browser — Session & Draft (`tests/e2e/session-timeout.spec.js`)

| # | Case | Status |
|---|---|---|
| 39 | Idle timeout: modal peringatan muncul, form yang lagi diisi ke-draft otomatis ke `localStorage`, auto-logout beneran mengakhiri sesi (bukan cuma redirect kosmetik — regresi guard bug CSRF-token-hilang Round 37) | ✅ |
| 40 | Login ulang setelah timeout → notice "draft ditemukan" muncul → klik restore → isian form balik persis seperti sebelum timeout | ✅ |

---

## Yang BELUM Ada Test Otomatis (sengaja, atau belum sempat)

| Case | Kenapa belum otomatis | Ada di manual checklist? |
|---|---|---|
| Reset password lewat email | Butuh SMTP beneran jalan, gak praktis diotomasi | ✅ `DOCS_MD/TESTING.md` §1 |
| Tampilan visual tombol 403 (benar-benar hilang dari UI, bukan cuma ditolak backend) | Otomatis cuma cek status code, bukan cek visual | ✅ `DOCS_MD/TESTING.md` §3 |
| Idle timeout di device produksi asli (tablet layar sentuh) | Test Playwright pakai browser desktop simulasi, bukan hardware asli | ✅ `DOCS_MD/TESTING.md` §2 |
| Filter dropdown mesin visual (tidak "reset" tampilan) | Perlu screenshot/visual check | ✅ `DOCS_MD/TESTING.md` §4 |

---

*Cara jalanin ulang semua case di atas: `vendor/bin/phpunit --testdox` + `npm run test:e2e` (dari root project). Update tabel ini kalau ada test baru ditambahkan atau ada yang berubah statusnya.*
