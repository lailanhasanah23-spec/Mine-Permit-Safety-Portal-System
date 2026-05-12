# Use Case Diagram
# Mine Permit & Safety Portal

Dokumen ini memetakan interaksi fungsional antara Aktor (pengguna sistem) dan *Use Case* (fitur sistem) dalam **Mine Permit & Safety Portal**.

## Diagram Use Case

```mermaid
usecaseDiagram
    actor Subcon as "Subcon / HRGA"
    actor Paramedic as "Paramedic"
    actor TOD as "TOD"
    actor SHE as "SHE / KTT"
    actor Admin as "System Admin"

    package "Authentication & Security" {
        usecase "Masuk Sistem\n(Email & Password)" as UC0
        usecase "Quick Access Login\n(Demo Role)" as UC0B
        usecase "Ganti Kata Sandi" as UC0C
    }

    package "Submission Management" {
        usecase "Buat Pengajuan Baru" as UC1
        usecase "Unggah Dokumen Dasar\n(KTP, SIM, Foto)" as UC2
        usecase "Pantau Status Pengajuan" as UC3
    }

    package "Verification & Processing" {
        usecase "Verifikasi MCU & Fitness" as UC4
        usecase "Unggah Hasil MCU" as UC5
        usecase "Verifikasi Tes Teknis" as UC6
        usecase "Unggah Hasil Tes (Teori/Praktek)" as UC7
        usecase "Approval Akhir (Final Review)" as UC8
        usecase "Terbitkan Izin / SIMPER" as UC9
    }

    package "System Administration" {
        usecase "Kelola Kategori & Form" as UC10
        usecase "Kelola User & Role" as UC11
        usecase "Lihat Audit Log" as UC12
        usecase "Akses G-Drive Explorer" as UC13
        usecase "Kelola Email Workflow" as UC14
        usecase "Sinkronisasi Google Form" as UC15
    }

    Subcon --> UC0
    Subcon --> UC0B
    Subcon --> UC0C
    Subcon --> UC1
    Subcon --> UC2
    Subcon --> UC3

    Paramedic --> UC0
    Paramedic --> UC3
    Paramedic --> UC4
    Paramedic --> UC5

    TOD --> UC0
    TOD --> UC3
    TOD --> UC6
    TOD --> UC7

    SHE --> UC0
    SHE --> UC3
    SHE --> UC8
    SHE --> UC9

    Admin --> UC0
    Admin --> UC10
    Admin --> UC11
    Admin --> UC12
    Admin --> UC13
    Admin --> UC14
    Admin --> UC15
    Admin --> UC3
    Admin --> UC0C
```

## Deskripsi Aktor dan Peran

1.  **Subcon / HRGA**: Aktor yang membuat pengajuan awal, melengkapi dokumen dasar, dan memantau status pengajuan milik perusahaannya sendiri.
2.  **Paramedic**: Tim medis klinik yang memverifikasi hasil *Medical Check Up* (MCU), mengisi catatan medis, dan menentukan apakah pengajuan dilanjutkan ke TOD atau dikembalikan untuk revisi.
3.  **TOD (Training & Operation Dept)**: Tim penilai kompetensi teknis yang mengunggah hasil tes teori dan praktek, lalu meneruskan pengajuan ke SHE bila syarat telah lengkap.
4.  **SHE (Safety, Health & Environment)**: Otoritas akhir yang melakukan peninjauan final, menyetujui atau mengembalikan pengajuan, dan mengendalikan penerbitan izin/SIMPER.
5.  **System Admin**: Mengelola master data, akun pengguna, audit log, integrasi Google Workspace, email workflow, dan akses dashboard operasional.
