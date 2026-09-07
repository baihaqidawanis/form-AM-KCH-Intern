# 📊 Flowchart Integrasi Form AM & RTWT Mesin (Plant Pulogadung)

Diagram alur end-to-end terintegrasi dari saat operator menemukan abnormalitas mesin di Form AM hingga penugasan dan penutupan tiket di sistem RTWT Mesin (Closed-Loop).

```mermaid
flowchart TD
    %% TINGKAT 1 (ATAS): FORM AM & LANTAI PRODUKSI
    subgraph S1 ["1. LANTAI PRODUKSI (Form Autonomous Maintenance)"]
        direction LR
        A["Inspeksi Part Mesin"] --> B{"Kondisi Part?"}
        B -->|"OK"| C["AM Selesai (Auto-Approved)"]
        B -->|"NOK"| D["Input Tag, No. WR & Foto Temuan"]
    end

    %% TINGKAT 2 (TENGAH): SISTEM & PENUGASAN TEKNIK
    subgraph S2 ["2. SISTEM & PENUGASAN TEKNIK"]
        direction LR
        E[("Terima Data (API)<br>Tiket RTWT: OPEN")] --> F["Lead Teknik: Search &<br>Collective Assign"] --> G["Status Tiket:<br>ASSIGNED"]
    end

    %% TINGKAT 3 (BAWAH): PERBAIKAN & PENUTUPAN TIKET
    subgraph S3 ["3. PERBAIKAN & VERIFIKASI PENUTUPAN (TEKNIK)"]
        direction LR
        H["Teknisi Perbaiki Mesin<br>(IN PROGRESS)"] --> I["Upload Foto Sesudah<br>& Uraian Tindakan"] --> J{"Verifikasi<br>Teknik?"}
        J -->|"Revisi"| H
        J -->|"Valid"| K["Verifikasi & CLOSE<br>(Status: CLOSED)"] --> L["Dashboard KPI QS<br>(% Closed)"]
    end

    %% ALUR PENGHUBUNG ANTAR-TINGKAT (LURUS KE BAWAH)
    D ==>|"HTTP POST"| E
    G ==>|"Disposisi Kerja"| H

    %% TEMA HITAM PUTIH FORMAL DOKUMEN RESMI
    style S1 fill:#fafafa,stroke:#71717a,stroke-width:1.5px;
    style S2 fill:#fafafa,stroke:#71717a,stroke-width:1.5px;
    style S3 fill:#fafafa,stroke:#71717a,stroke-width:1.5px;

    classDef bw fill:#ffffff,stroke:#18181b,stroke-width:1.5px,color:#09090b;
    classDef bwBold fill:#f4f4f5,stroke:#18181b,stroke-width:2px,color:#09090b,font-weight:bold;

    class A,C,D,F,G,H,I,K,L bw;
    class B,E,J bwBold;
```

---

## 📌 Ringkasan Alur Kunci:
1. **Lantai Produksi (Form AM):** Memicu pembuatan tiket RTWT Mesin saat part bernilai NOK (dilengkapi No. WR & foto temuan).
2. **Collective Assign by Teknik:** Supervisor teknik memfilter kategori masalah mesin lalu menugaskan banyak tiket secara massal (*bulk*) ke teknisi dalam satu aksi.
3. **Syarat Penutupan Mutlak (Closed-Loop):** Tiket HANYA boleh ditutup oleh **Tim Teknik** dengan syarat wajib melampirkan foto sesudah perbaikan dan uraian tindakan korektif.
4. **Pencapaian KPI QS Pulogadung:** Keberhasilan pemeliharaan dinilai dari persentase tiket yang berhasil berstatus **CLOSED**.





