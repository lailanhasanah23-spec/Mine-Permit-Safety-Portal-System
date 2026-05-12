# LAPORAN EKSEKUTIF
# Mine Permit & Safety Portal System

## Ringkasan Singkat
Mine Permit & Safety Portal System adalah aplikasi web berbasis Laravel 11 yang digunakan untuk mengelola pengajuan, verifikasi, dan penerbitan perizinan operasional seperti Mine Permit dan SIMPER. Sistem ini dirancang untuk menggantikan proses manual yang tersebar di berbagai departemen dengan alur digital yang terpusat, terkontrol, dan dapat diaudit.

## Tujuan Utama
Sistem ini dibangun untuk:
* menyatukan data pengajuan dalam satu sumber kebenaran,
* mempercepat alur verifikasi lintas departemen,
* menjaga integritas dokumen dan status pengajuan,
* membatasi akses berdasarkan peran pengguna,
* serta menyediakan jejak audit untuk aktivitas penting.

## Ruang Lingkup Operasional
Fungsi utama sistem mencakup:
* pengajuan awal oleh Subcon atau HRGA,
* verifikasi medis oleh Paramedic,
* verifikasi teknis oleh TOD,
* keputusan akhir oleh SHE/KTT,
* pengelolaan kategori, form, user, audit log, dan workflow email oleh Admin,
* integrasi Google Drive, Gmail, dan sinkronisasi Google Form.

## Hasil Implementasi
Berdasarkan implementasi yang sudah tersedia, sistem telah mendukung:
* Role-Based Access Control untuk peran `admin`, `she`, `hrga`, `tod`, `paramedic`, dan `subcon`,
* alur status pengajuan dari `pending_hrga` hingga `approved`,
* pengembalian revisi ke `pending_hrga` saat verifikasi belum memenuhi syarat,
* penyimpanan dan penelusuran berkas melalui Google Drive,
* pengiriman notifikasi email SIMPER berbasis template,
* serta pencatatan aktivitas penting melalui audit log.

## Dokumen Terkait
* [Laporan Akhir Proyek](LAPORAN_AKHIR_PROYEK.md)
* [ERD Database](ERD_DATABASE.md)
* [Use Case Diagram](USE_CASE_DIAGRAM.md)
* [Workflow Diagram](WORKFLOW_DIAGRAM.md)
* [Bab I - Pendahuluan](laporan_formal/BAB_1_PENDAHULUAN.md)
* [Bab II - Landasan Teori](laporan_formal/BAB_2_LANDASAN_TEORI.md)
* [Bab III - Analisis dan Perancangan](laporan_formal/BAB_3_ANALISIS_DAN_PERANCANGAN.md)
* [Bab IV - Implementasi dan Pengujian](laporan_formal/BAB_4_IMPLEMENTASI_DAN_PENGUJIAN.md)
* [Bab V - Kesimpulan dan Saran](laporan_formal/BAB_5_KESIMPULAN_DAN_SARAN.md)

## Status Dokumen
Dokumen ini dirangkum dari implementasi dan dokumentasi proyek yang tersedia per 06 Mei 2026.