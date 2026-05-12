# BAB I
# PENDAHULUAN

## 1.1 Latar Belakang Masalah
Di lingkungan industri pertambangan, Keselamatan dan Kesehatan Kerja (K3) merupakan prioritas mutlak yang harus ditegakkan untuk meminimalisasi risiko kecelakaan kerja. Salah satu pilar utama dari penerapan K3 adalah memastikan bahwa setiap personel, baik karyawan internal maupun pihak subkontraktor (Subcon), memiliki perizinan yang sah untuk memasuki area operasional dan mengoperasikan kendaraan atau alat berat. Perizinan ini dikenal dengan istilah *Mine Permit* dan Surat Izin Mengemudi Perusahaan (SIMPER).

Berdasarkan observasi di lapangan, proses administrasi pengajuan dan perpanjangan SIMPER/Mine Permit saat ini masih banyak dilakukan secara manual atau semi-digital menggunakan dokumen fisik dan surel (email) yang tidak terstruktur. Proses verifikasi dokumen memerlukan koordinasi panjang yang melibatkan beberapa departemen: *Human Resources & General Affairs* (HRGA) untuk validasi administrasi, Tim Paramedik (Klinik) untuk verifikasi tes kesehatan dasar, Tim *Training & Operation* (TOD) untuk pengujian kompetensi teknis, hingga Departemen *Safety, Health & Environment* (SHE) untuk persetujuan akhir. 

Pendekatan konvensional ini memunculkan beberapa permasalahan krusial. Pertama, rentan terjadi kehilangan dokumen fisik dan redudansi data di setiap departemen. Kedua, timbulnya *bottleneck* (kemacetan) informasi dimana pihak Subcon tidak dapat melacak secara pasti sejauh mana status dokumen yang mereka ajukan. Ketiga, tidak adanya satu *Single Source of Truth* (sumber kebenaran tunggal) yang dapat diandalkan secara langsung (*real-time*) oleh manajemen untuk mengevaluasi data kelayakan personel, yang pada gilirannya dapat meningkatkan risiko masuknya pekerja yang belum tersertifikasi penuh ke area tambang.

Berdasarkan latar belakang tersebut, dipandang perlu untuk mengembangkan dan mengimplementasikan sebuah sistem digital yang terintegrasi. Sistem yang diusulkan adalah **Mine Permit & Safety Portal System**. Sistem ini dirancang untuk mendigitalisasi, mengotomatisasi alur kerja (*workflow*), dan mensentralisasi seluruh proses verifikasi perizinan mulai dari tahap inisiasi pengajuan hingga tahap penerbitan.

## 1.2 Rumusan Masalah
Merujuk pada latar belakang masalah, maka perumusan masalah dalam penelitian/pengembangan ini adalah:
1. Bagaimana merancang dan membangun sistem informasi portal *Mine Permit* dan SIMPER berbasis *web* yang dapat mengotomatisasi alur persetujuan dokumen (*multi-stage approval*) antar departemen?
2. Bagaimana mengintegrasikan sistem dengan manajemen penyimpanan awan (Cloud Storage) untuk sentralisasi dan integritas arsip lampiran pengajuan?
3. Bagaimana membangun sistem kendali akses (*Role-Based Access Control*) yang aman guna membatasi hak guna sistem berdasarkan kewenangan masing-masing entitas (Subcon, Paramedic, TOD, SHE, dan Admin)?

## 1.3 Batasan Masalah
Agar pembahasan lebih terfokus, batasan masalah pada proyek ini ditetapkan sebagai berikut:
1. Sistem ini dikembangkan berbasis *web* menggunakan *framework* Laravel (PHP) dan sistem manajemen basis data MySQL.
2. Sistem ini hanya menangani proses administrasi verifikasi, mulai dari unggah syarat administrasi, pencatatan hasil *Medical Check Up* (MCU), pencatatan hasil tes praktek/teori, hingga persetujuan akhir (bukan mencakup aplikasi ujian teori daring).
3. Entitas/Peran yang terlibat dibatasi pada lima kategori utama: System Admin, SHE, Paramedic, TOD, dan Subcon/HRGA.
4. Integrasi pihak ketiga hanya terbatas pada pemanfaatan Google Drive API untuk penyimpanan dokumen digital, dan SMTP Relay untuk pengiriman surel otomatis.
5. Akses sistem memerlukan otentikasi login, tanpa ketersediaan registrasi publik independen (akun Subcon dibuatkan oleh Admin).

## 1.4 Tujuan Penelitian/Pengembangan
Tujuan dari penelitian dan pengembangan sistem ini adalah:
1. Menghasilkan sistem portal *Mine Permit* & SIMPER yang mengimplementasikan proses *approval* berantai digital yang valid dan terstruktur.
2. Menyediakan *Single Source of Truth* terkait status kelayakan administratif pekerja tambang melalui *dashboard* sentral.
3. Menciptakan rekam jejak audit (*audit trail*) digital secara utuh atas setiap interaksi dan persetujuan yang dilakukan oleh peninjau (Klinik, TOD, SHE).

## 1.5 Manfaat Penelitian/Pengembangan
### 1.5.1 Manfaat Teoritis
Penelitian ini diharapkan dapat memperkaya kajian keilmuan sistem informasi, khususnya terkait digitalisasi proses birokrasi perizinan berantai di sektor industri kritis (*heavy industry*) dengan mengadopsi integrasi API *cloud storage* (Google Drive) dan manajemen peran hierarkis (RBAC).

### 1.5.2 Manfaat Praktis
1. **Bagi Perusahaan (Manajemen & SHE)**: Meningkatkan validitas dan keterlacakan (*traceability*) data, mengurangi risiko *human error* dalam menerbitkan izin yang berpotensi melanggar K3, serta memotong waktu birokrasi (Efisiensi).
2. **Bagi Subkontraktor / HRGA**: Memberikan transparansi untuk melacak (*tracking*) status pengajuan pekerjanya secara nyata, kapanpun dan di manapun.
3. **Bagi Departemen Pendukung (Paramedic & TOD)**: Mempermudah dalam memberikan *assessment* tanpa harus saling bertukar dan menyimpan arsip kertas secara berlebihan.

## 1.6 Sistematika Penulisan
Penulisan laporan akhir ini disusun secara sistematis menjadi beberapa bab sebagai berikut:
*   **BAB I PENDAHULUAN**: Menguraikan latar belakang masalah, perumusan, batasan, tujuan, manfaat, hingga sistematika penulisan laporan.
*   **BAB II LANDASAN TEORI**: Mengulas teori-teori dasar terkait sistem, perizinan, dan teknologi pendukung (Laravel, MySQL, *Google API*).
*   **BAB III ANALISIS DAN PERANCANGAN SISTEM**: Membahas analisis alur berjalan dan usulan alur baru (melalui *Use Case* dan *Workflow*), serta pemodelan basis data (ERD).
*   **BAB IV IMPLEMENTASI DAN PENGUJIAN**: Memaparkan hasil implementasi antarmuka, fitur-fitur, dan dokumentasi pengujian fungsional aplikasi.
*   **BAB V KESIMPULAN DAN SARAN**: Menyimpulkan hasil akhir implementasi dalam menyelesaikan masalah utama, serta memberikan saran untuk pengembangan tahap lanjutan.
