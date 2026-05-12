# LAPORAN AKHIR PROYEK: MINE PERMIT & SAFETY PORTAL SYSTEM

## BAB I: PENDAHULUAN

### 1.1 Latar Belakang
Keselamatan kerja (*Occupational Health and Safety*) merupakan pilar utama dalam industri pertambangan. Proses administrasi perizinan pekerja (*Mine Permit*) dan izin mengemudi internal (SIMPER) yang sebelumnya masih bersifat manual seringkali menghadapi kendala di lapangan:
1.  **Redundansi Data**: Dokumen fisik yang menumpuk dan sering tercecer.
2.  **Silo Antar Departemen**: Sulitnya melacak progres verifikasi antara tim HRGA, Paramedik, TOD, dan SHE.
3.  **Inkonsistensi Validasi**: Risiko terbitnya izin bagi personel yang belum memenuhi standar medis atau teknis akibat *human error*.

**Mine Permit & Safety Portal** hadir untuk mendigitalisasi seluruh proses tersebut, memastikan setiap personel yang beroperasi di lapangan telah memenuhi standar kualifikasi medis dan teknis yang ketat secara transparan dan akuntabel.

### 1.2 Maksud dan Tujuan
Pengembangan sistem ini memiliki maksud dan tujuan yang terukur untuk mendukung operasional perusahaan:
*   **Sentralisasi Data**: Membangun satu sumber data terpercaya (*Single Source of Truth*) untuk seluruh dokumen keselamatan pekerja.
*   **Otomasi Alur Kerja**: Mempercepat proses verifikasi antar departemen melalui sistem *multi-stage approval* digital.
*   **Transparansi & Akuntabilitas**: Memberikan visibilitas penuh (*Real-time Dashboard*) kepada manajemen terhadap status pengajuan izin dan jejak audit (*Audit Trail*) aktivitas pengguna.
*   **Integritas Dokumen**: Memastikan dokumen yang diterbitkan adalah sah dan tersinkronisasi dengan ekosistem penyimpanan awan korporat (Google Drive).

### 1.3 Ruang Lingkup
Sistem ini mencakup fungsionalitas utama dalam siklus hidup perizinan:
1.  **Manajemen Pengajuan (Submission Management)**: Pendaftaran awal, pengunggahan dokumen dasar oleh Subcon/HRGA, dan sinkronisasi data dari Google Form sesuai kategori pengajuan.
2.  **Verifikasi Berantai (Multi-Stage Approval)**: Alur validasi spesifik oleh Paramedic, TOD, dan SHE, termasuk alur revisi yang dikembalikan ke HRGA.
3.  **Manajemen Dokumen Cloud**: Integrasi Google Drive untuk penyimpanan hierarkis dan penelusuran folder berkas.
4.  **Analytics, Audit, dan Email**: Dashboard agregasi, pelacakan riwayat interaksi sistem, audit log, serta workflow email berbasis template.

### 1.4 Sistematika Penulisan
Dokumen ini didukung oleh beberapa dokumen teknis terpisah:
*   [ERD Database](ERD_DATABASE.md): Penjelasan struktur relasi tabel.
*   [Use Case Diagram](USE_CASE_DIAGRAM.md): Penjelasan interaksi aktor sistem.
*   [Workflow Diagram](WORKFLOW_DIAGRAM.md): Penjelasan transisi status pengajuan.

---

## BAB II: LANDASAN TEKNIS

### 2.1 Teknologi Inti (Tech Stack)
Sistem dikembangkan menggunakan teknologi terkini yang difokuskan pada stabilitas *enterprise*:
*   **Backend**: Laravel 11 (PHP 8.3+) dengan arsitektur MVC.
*   **Database**: MySQL 8.0 dengan dukungan integritas relasional tinggi.
*   **Frontend**: Vanilla CSS dipadukan dengan desain industrial berbasis komponen untuk menjaga performa dan konsistensi visual.
*   **Infrastruktur & Cloud**: Integrasi Gmail API untuk pengiriman email operasional, Google Drive REST API V3 untuk penyimpanan berkas, dan Google Form untuk sinkronisasi data pengajuan.

### 2.2 Arsitektur Sistem & Robustness
Sistem dibangun sebagai *Production-Ready Application* dengan penanganan khusus:
*   **Atomic Transactions (Database)**: Seluruh operasi *Create-Update-Delete* kompleks (seperti saat *approval* SHE dan sinkronisasi lampiran) dibungkus menggunakan `DB::transaction()` untuk mencegah data *orphan*.
*   **Concurrency Handling**: Menggunakan `lockForUpdate()` (Pessimistic Locking) untuk mencegah anomali data ketika dua admin memproses pengajuan (*submission*) yang sama di waktu bersamaan.
*   **Dynamic URL Parsing**: Menggunakan deteksi URL otomatis (skema HTTPS vs HTTP) untuk memastikan kelancaran aset saat diakses secara lokal maupun di Domain Produksi.

### 2.3 Keamanan & RBAC (Role-Based Access Control)
Manajemen otorisasi diterapkan secara granular (*strict access*):
*   **Admin**: Kendali penuh sistem (manajemen form, kategori, user, audit log, email workflow, dan integrasi Google).
*   **SHE / KTT**: Hak *veto* dan otoritas akhir (*final review*) penerbitan.
*   **Paramedic**: Akses spesifik untuk verifikasi MCU dan pencatatan catatan medis.
*   **TOD**: Akses spesifik untuk evaluasi tes praktek dan teori.
*   **Subcon / HRGA**: Akses terisolasi, hanya dapat membuat, memantau, dan merevisi pengajuan milik perusahaan sendiri.

---

## BAB III: IMPLEMENTASI SISTEM

### 3.1 Alur Kerja Operasional (End-to-End Workflow)
Implementasi alur berjalan sejalan dengan [Workflow Diagram](WORKFLOW_DIAGRAM.md):
1.  **Submission (`pending_hrga`)**: Subcon atau HRGA membuat data awal dan melengkapi dokumen dasar.
2.  **Medical Checkup (`pending_paramedic`)**: Paramedic memverifikasi kelayakan kesehatan.
3.  **Technical Test (`pending_tod`)**: TOD mengunggah bukti kompetensi dan hasil tes.
4.  **Final Review (`pending_she`)**: SHE mengecek seluruh riwayat di atas dan menentukan persetujuan akhir atau pengembalian revisi.
5.  **Closing (`approved`)**: Sistem mengeksekusi integrasi Google Drive dan notifikasi email untuk menutup pengajuan yang disetujui.

### 3.2 Fitur Unggulan
*   **Quick Access Login**: Fitur otentikasi otomatis untuk kebutuhan demo/pengujian role, selain login normal memakai email dan kata sandi.
*   **Enterprise Dashboard**: Visualisasi analitik komprehensif terkait jumlah pengajuan, status aktif, tren, dan kondisi konflik data.
*   **Google Drive Explorer UI**: Antarmuka *file manager* terintegrasi di dalam portal untuk menelusuri folder pengajuan tanpa harus keluar dari sistem admin.
*   **Automated Audit Log**: Modul yang mencatat setiap rincian aktivitas pengguna (waktu, IP, role, aksi) untuk menjaga integritas dan keterlacakan data.

---

## BAB IV: KESIMPULAN DAN SARAN

### 4.1 Kesimpulan
Proyek **Mine Permit & Safety Portal** telah mencapai fase akhir pengembangan dengan status **Stable & Production-Ready**. Migrasi dari proses birokrasi manual menuju ekosistem digital terbukti dapat mereduksi waktu pemrosesan (*lead time*), meningkatkan konsistensi (*Single Source of Truth*), dan memberikan perlindungan hukum yang kuat bagi perusahaan melalui sistem persetujuan digital yang akuntabel.

### 4.2 Saran Pengembangan
Rekomendasi untuk skalabilitas sistem (Fase 2):
1.  **Integrasi WhatsApp Gateway**: Memfasilitasi notifikasi pengingat *expired date* SIMPER secara personal kepada pekerja maupun HRGA.
2.  **Verifikasi Lapangan (QR Code App)**: Pembuatan modul atau aplikasi *mobile* sederhana untuk Security / Checker lapangan dalam memindai validitas Mine Permit pekerja di area tambang.
3.  **Integrasi Sistem Absensi / ERP**: Sinkronisasi data keaktifan pekerja (*resign*, cuti) secara *real-time* ke portal keselamatan.

---
**Dibuat oleh**: Antigravity AI Coding Assistant
**Tanggal**: 06 Mei 2026
**Status**: Dokumen Final - Release v1.0
