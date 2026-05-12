# BAB III
# ANALISIS DAN PERANCANGAN SISTEM

## 3.1 Analisis Sistem Berjalan
Pada sistem konvensional (sebelum digitalisasi), alur pengajuan *Mine Permit* melibatkan pergerakan berkas secara fisik (*paper-based*). Subcon harus menyiapkan bundel dokumen KTP, SIM Kepolisian, dan pasfoto, menyerahkannya kepada perwakilan HRGA untuk pencatatan manual. Pekerja kemudian diarahkan menuju klinik untuk diperiksa kesehatannya. Lembar hasil (*fit/unfit*) harus dibawa kembali. Selanjutnya, pekerja dijadwalkan mengikuti uji coba (*test drive*) lapangan oleh instruktur TOD. Rapor penilaian dari TOD diserahkan bersama seluruh bundel dokumen ke meja manajemen SHE. 
Titik lemah dari sistem lama ini adalah:
*   Ketidakpastian waktu respons bagi Subcon.
*   Risiko manipulasi data atau pemalsuan stempel tanda tangan.
*   Penyimpanan lemari arsip fisik yang rentan rusak.

## 3.2 Analisis Kebutuhan Sistem
Berdasarkan kendala sistem berjalan, didefinisikan Kebutuhan Fungsional (F) dan Non-Fungsional (NF).
*   **F-01**: Sistem harus mengakomodasi *login* menggunakan email dan kata sandi, serta menyediakan *quick access login* untuk kebutuhan demo/pengujian.
*   **F-02**: Sistem harus menerapkan *Role-Based Access Control* untuk peran `admin`, `she`, `hrga`, `tod`, `paramedic`, dan `subcon`.
*   **F-03**: Sistem memungkinkan Subcon/HRGA membuat pengajuan awal, melampirkan dokumen, dan memantau status pengajuan miliknya.
*   **F-04**: Sistem membatasi Paramedic untuk memverifikasi MCU dan memberi catatan medis pada tahap yang relevan.
*   **F-05**: Sistem membatasi TOD untuk mengunggah hasil tes teori/praktek pada pengajuan yang sedang menunggu verifikasi teknis.
*   **F-06**: Sistem memampukan SHE memberi keputusan final, sedangkan pengajuan yang perlu perbaikan dikembalikan ke tahap HRGA.
*   **F-07**: Sistem harus menyediakan modul admin untuk pengelolaan kategori, form, pengguna, audit log, integrasi Google, dan workflow email.
*   **NF-01**: Antarmuka sistem harus responsif dan menyesuaikan dengan estetika korporat industrial yang elegan.
*   **NF-02**: Operasi perubahan data kritis wajib menggunakan transaksi atomik dan *pessimistic locking* untuk mencegah konflik penulisan.
*   **NF-03**: Sistem harus stabil saat berinteraksi dengan Google Workspace, termasuk sinkronisasi form, penyimpanan file, dan pengiriman email.
*   **NF-04**: Sistem harus memiliki mekanisme audit trail dan pembatasan login gagal agar aktivitas administratif dapat ditelusuri.

## 3.3 Perancangan Sistem
Pemodelan fungsionalitas sistem divisualisasikan menggunakan spesifikasi diagram.
*(Catatan: Detail diagram selengkapnya merujuk pada dokumen lampiran dalam folder utama laporan).*

### 3.3.1 Use Case Diagram
Merujuk pada dokumen terlampir `USE_CASE_DIAGRAM.md`, terdapat batasan *scope* aktivitas untuk setiap lima aktor:
1.  **Subcon/HRGA**: Bertindak sebagai pemrakarsa (*initiator*). Mengisi data pekerja, menautkan dokumen dasar, dan memantau status pengajuan.
2.  **Paramedic**: Menangani pengajuan `pending_paramedic`, memverifikasi hasil MCU, dan mengembalikan pengajuan ke HRGA bila perlu revisi.
3.  **TOD**: Menangani pengajuan `pending_tod`, menilai hasil tes teori/praktek, dan meneruskan berkas ke tahap SHE jika lengkap.
4.  **SHE/KTT**: Melakukan *cross-verify* data, memberi keputusan final, atau mengembalikan pengajuan untuk perbaikan pada tahap HRGA.
5.  **Admin**: Konfigurator yang melakukan *maintenance* master data (kategori, form, user, audit log), integrasi Google, dan workflow email.

### 3.3.2 Workflow Diagram (Alur Proses)
Sistem ini menggunakan mekanisme *State Machine* (Mesin Kondisi). Transisi berjalan mengikuti jenis pengajuan dan hasil verifikasi:
`pending_hrga` -> `pending_paramedic` -> `pending_tod` -> `pending_she` -> `approved`.

Jika verifikasi pada tahap Paramedic, TOD, atau SHE belum memenuhi syarat, status dikembalikan ke `pending_hrga` agar dapat diperbaiki. Untuk kategori tertentu yang disinkronkan dari Google Form, pengajuan dapat langsung masuk ke `pending_she`.

Setiap kali status berpindah, otorisasi modifikasi dokumen berpindah tangan untuk menghindari konflik wewenang.

## 3.4 Perancangan Basis Data (ERD)
Sistem menggunakan struktur relasional untuk menjamin kecepatan *query* (*Indexing*) dan keutuhan referensi (*Foreign Key*). Rujuk pada dokumen terlampir `ERD_DATABASE.md` untuk gambaran relasional tabel:
*   Tabel **`users`**: Entitas autentikasi, memiliki atribut kunci `role`.
*   Tabel **`categories`** & **`forms`**: Master data untuk pengelompokan kategori pengajuan dan link form pengajuan/monitoring.
*   Tabel **`submissions`**: Entitas transaksional inti. Berelasi satu-ke-banyak dengan tabel berkas, merekam identitas pendaftar (`applicant_nik`, `company_name`) dan jejak status (`paramedic_verified_at`, `approved_at`, `rejected_at`).
*   Tabel **`submission_files`**: Mengakomodir arsitektur *file attachment* untuk berbagai jenis berkas (`file_type`) seperti `mcu`, `sim`, `ktp`, `tes_teori`, dan `tes_praktek`.
*   Tabel **`audit_logs`**, **`auth_login_attempts`**, serta tabel email workflow: Mendukung pelacakan perubahan, pembatasan login gagal, dan pengiriman notifikasi SIMPER.

## 3.5 Perancangan Antarmuka Pengguna (UI/UX)
Filosofi desain yang diterapkan merujuk pada tema ***Enterprise Industrial Dashboard***:
*   **Warna Solid dan Premium**: Menggunakan gradasi warna abu tua/hitam, dan indikator status yang tegas untuk membantu pembacaan cepat di dashboard.
*   **Navigasi Lateral (Sidebar)**: Memudahkan akses menu dengan visibilitas menu yang otomatis menyesuaikan kewenangan *role* yang sedang masuk.
*   **Card-based Layout**: Mengemas informasi detail pendaftar, status berkas, dan linimasa pelacakan status (*Tracking Timeline*) di dalam blok kartu yang mudah dicerna secara visual.
*   **Role-aware Pages**: Halaman dashboard, pengajuan, drive explorer, audit log, dan email workflow hanya menampilkan aksi yang sesuai dengan peran pengguna aktif.
