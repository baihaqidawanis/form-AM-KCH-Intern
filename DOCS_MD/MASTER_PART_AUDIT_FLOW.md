# Alur Audit Master Part dan Export PDF

Aturan ini berlaku untuk halaman View, Report Harian, Check Sheet Periode, dan Export PDF.

```mermaid
flowchart TD
    A[Admin menambah master part pada T1] --> B[Part aktif untuk form baru]
    B --> C[Operator submit form pada T2]
    C --> D{created_at part <= T2?}
    D -->|Tidak| E[Part tidak muncul pada form historis]
    D -->|Ya| F[Part dan nilai OK/NOK menjadi bagian form]

    F --> G[Admin takeout part pada T3]
    G --> H{T2 < taken_out_at?}
    H -->|Ya| I[Form lama tetap tampilkan part dan nilai audit]
    H -->|Tidak| J[Form baru tidak menampilkan part]

    I --> K[View Form]
    I --> L[Report Harian]
    I --> M[Check Sheet dan Export PDF]
    J --> K
    J --> L
    J --> M

    N[Edit Data pada record] --> O[Satu-satunya jalur perubahan nilai form]
```

## Resolver histori

Sebuah part berlaku pada record hanya jika:

- `master_part.created_at <= form.created_at`; dan
- `master_part.taken_out_at` kosong, atau `master_part.taken_out_at > form.created_at`.

`active_from` tetap dipakai sebagai batas tanggal operasional, tetapi tidak cukup untuk membedakan perubahan pada hari yang sama. Karena itu timestamp `created_at` wajib dipakai agar part yang ditambah siang hari tidak bocor ke form pagi hari.

Export PDF memakai resolver yang sama dengan Report Harian. Tidak boleh mengambil daftar `master_part` aktif saat PDF dibuat.