# Workflow & State Diagram
# Mine Permit & Safety Portal

Dokumen ini mengilustrasikan perubahan status (*state machine*) dari sebuah entitas `Submission` di dalam sistem.

## Alur Persetujuan (Approval Workflow)

Setiap pengajuan izin memiliki *lifecycle* yang ketat dan berurutan untuk menjamin akuntabilitas.

```mermaid
stateDiagram-v2
    [*] --> pending_hrga : Submission Created by HRGA/Subcon atau GForm Sync

    pending_hrga --> pending_paramedic : Form & Dokumen Awal Lengkap (SIMPER)
    pending_hrga --> pending_she : Form Pengajuan Non-SIMPER / Monitoring

    state pending_paramedic {
        [*] --> MCU_Review
        MCU_Review --> Fit : Paramedic Verifies
        MCU_Review --> Unfit : Paramedic Verifies
    }
    
    pending_paramedic --> pending_tod : Status MCU = Fit
    pending_paramedic --> pending_hrga : Status MCU = Unfit / Perlu Revisi
    
    state pending_tod {
        [*] --> Technical_Test
        Technical_Test --> Passed : TOD Verifies
        Technical_Test --> Failed : TOD Verifies
    }
    
    pending_tod --> pending_she : Test Passed
    pending_tod --> pending_hrga : Test Failed / Perlu Revisi
    
    state pending_she {
        [*] --> Final_Review
        Final_Review --> Valid : SHE Approves
        Final_Review --> Invalid : SHE Rejects
    }
    
    pending_she --> approved : SHE Final Approval
    pending_she --> pending_hrga : SHE Rejected / Pengajuan Ulang
    
    approved --> [*] : Izin Diterbitkan & Sinkronisasi G-Drive
```

## Deskripsi Status (State)

*   **`pending_hrga`**: Tahap awal atau tahap revisi. HRGA/Subcon melengkapi data pekerja, dokumen dasar, atau memperbaiki pengajuan yang dikembalikan dari tahap verifikasi sebelumnya.
*   **`pending_paramedic`**: Berkas telah masuk untuk verifikasi MCU. Tim Paramedic memeriksa kelayakan kesehatan dan menambahkan catatan medis.
*   **`pending_tod`**: Status lolos MCU. Tim TOD mengunggah hasil tes teori/praktek dan melengkapi penilaian teknis.
*   **`pending_she`**: Status lolos TOD atau kategori tertentu yang langsung masuk ke tahap SHE. Berkas siap untuk review final oleh SHE/KTT.
*   **`approved`**: Pengajuan disetujui penuh. Sistem menyimpan hasil persetujuan, menautkan berkas ke Google Drive, dan menyiapkan notifikasi email.

Penolakan pada tahap Paramedic, TOD, atau SHE tidak menjadi state akhir; status dikembalikan ke `pending_hrga` agar pengajuan dapat diperbaiki. Timestamp `rejected_at` tetap dicatat untuk audit.
