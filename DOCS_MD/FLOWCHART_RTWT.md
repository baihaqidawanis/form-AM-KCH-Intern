# 📊 Flowchart Integrasi Form AM ke Breakdown Management (RTWT QS Pulogadung)

Diagram alur end-to-end dari saat operator menemukan abnormalitas di Form AM hingga verifikasi dan penutupan tiket di Breakdown Management System (BMS).

```mermaid
graph TD
    A["1. Operator Form AM Cek Mesin"] --> B{"Kondisi Part?"}
    B -->|"Kondisi Baik (OK)"| C["Auto-Approved System (Selesai)"]
    B -->|"Kondisi Tidak Baik (NOK)"| D["Input Kendala, Tag, No WR"]
    D --> E["Submit Form AM"]

    E -->|"Integrasi Pipeline API"| F["Tiket RTWT Mesin Produksi"]
    G["2. Karyawan Office Input Manual"] --> H["Tiket RTWT Dept Support (Office)"]

    F --> I["Teknik: Search dan Collective Assign by Category"]
    H --> J["Dept Head / GA: Assign PIC Pelaksana"]

    I --> K["Notifikasi In-App dan Draft Email ke PIC"]
    J --> K

    K --> L["Status: IN PROGRESS (Perbaikan Fisik)"]
    L --> M["Upload Foto Bukti dan Tindakan Penanggulangan"]
    M --> N["Submit untuk Verifikasi"]

    N --> O{"Apa Kategori Temuan?"}
    O -->|"Kategori Productivity / Mesin"| P["Verifikasi dan CLOSED oleh TEKNIK"]
    O -->|"Kategori 5R dan HSE (Safety/K3)"| Q["Verifikasi dan CLOSED oleh QS"]

    P --> R["Status Resmi: CLOSED"]
    Q --> R

    R --> S["Dashboard Penilaian dan Pencapaian KPI QS Pulogadung"]
```

---

## 📌 Ringkasan Alur Kunci:
1. **Form AM (Lantai Produksi):** Memicu tiket RTWT ketika part bernilai NOK.
2. **Dua Scope di BMS:** RTWT Mesin (dari Form AM) dan RTWT Dept Support (dari input Office).
3. **Collective Assign by Teknik:** Memfilter kategori temuan mesin lalu menugaskan banyak tiket secara massal ke teknisi.
4. **Otoritas Penutupan (Closer):**
   - Temuan **Productivity / Mesin** diverifikasi & ditutup oleh **TEKNIK**.
   - Temuan **5R & HSE** diverifikasi & ditutup oleh **QS**.
5. **Penilaian QS:** Dihitung dari seluruh tiket yang sudah berstatus **CLOSED**.
