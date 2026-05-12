# BAB IV
# IMPLEMENTASI DAN PENGUJIAN

## 4.1 Lingkungan Implementasi (Environment)
Untuk memastikan sistem dapat direplikasi dan dijalankan pada peladen (*server*) operasional, berikut adalah spesifikasi perangkat minimal yang diperlukan:
### 4.1.1 Perangkat Keras (Hardware)
*   *Processor*: Dual Core 2.0 GHz (min)
*   *Memory (RAM)*: 2 GB (min), disarankan 4 GB untuk penanganan konkurensi database
*   *Storage*: 20 GB SSD untuk basis data dan aplikasi (Penyimpanan lampiran file dialihkan sepenuhnya ke Google Drive).

### 4.1.2 Perangkat Lunak (Software)
*   Sistem Operasi peladen: Distribusi Linux (Ubuntu/Debian) atau Windows Server.
*   *Web Server*: Nginx / Apache
*   *Engine*: PHP versi 8.3
*   *Database Engine*: MySQL 8.0
*   *Library Tambahan*: Composer untuk *package manager*, Node.js / NPM untuk aset *(opsional)*, serta kredensial Google Workspace untuk integrasi Gmail/Drive.

## 4.2 Implementasi Antarmuka (Interface)
Tahap ini mewujudkan perancangan UI ke dalam bentuk visual berbasis web (*Front-End*).
1.  **Halaman Otentikasi (Login)**: Menggunakan email dan kata sandi, dengan *quick access login* untuk kebutuhan demo/pengujian role. Sistem juga menampilkan pesan *lockout* ketika percobaan masuk terlalu sering gagal.
2.  **Halaman Dashboard Analitik**: Menyajikan ringkasan pengajuan, tren harian dan mingguan, conflict heatmap, dan status pengajuan aktif sehingga Pimpinan (KTT) memperoleh gambaran operasional secara cepat.
3.  **Halaman Manajemen Data (Grid View)**: Menampilkan antrian pengajuan berdasarkan filter `role`. Apabila masuk sebagai *Paramedic*, hanya pengajuan berstatus `pending_paramedic` yang dirender di tabel antrian aktif.
4.  **Halaman Penelusuran Dokumen Google Drive**: Memanfaatkan eksplorasi folder dan file berbasis `folder_id` untuk menelusuri struktur dokumen yang disinkronkan dari penyimpanan Google Drive.
5.  **Halaman Email Workflow**: Menyediakan template, draft, dan pengiriman email SIMPER yang terhubung dengan data pengajuan dan lampiran yang wajib dilengkapi.
6.  **Halaman Ganti Kata Sandi**: Muncul ketika akun masih berstatus wajib ganti sandi, sehingga keamanan autentikasi dapat dipenuhi sebelum akses penuh ke dashboard diberikan.

## 4.3 Implementasi Basis Data dan Logika Sistem
1.  **Proteksi Concurrency Control**:
    Diterapkan metode *Pessimistic Locking* melalui Query Eloquent `lockForUpdate()`. Apabila ada dua akun SHE mencoba memberikan persetujuan (`approve`) secara bersamaan pada ID *Submission* yang sama, koneksi basis data kedua akan ditahan hingga transaksi pertama selesai untuk menghindari kondisi balapan (*race condition*).
2.  **Sinkronisasi Google Form**:
    Endpoint sinkronisasi menerima data pengajuan dari Google Apps Script dengan token rahasia. Setelah kategori ditemukan, sistem membuat `Submission` baru dan menentukan status awal sesuai jenis kategori yang masuk.
3.  **Otomasi Google Drive dan Email**:
    `GoogleDriveService` dipakai untuk membaca folder dan file dari cloud, sedangkan `GoogleMailService` dipakai untuk otorisasi Gmail dan pengiriman email SIMPER berbasis template. Pengiriman dilakukan secara langsung pada request yang berjalan, sehingga hasilnya dapat langsung diketahui oleh admin.
4.  **Pencatatan Audit**:
    Aktivitas penting seperti pengiriman email, penolakan, persetujuan, dan pembaruan data administrasi dicatat ke audit log agar jejak perubahan mudah ditelusuri kembali.

## 4.4 Pengujian Sistem
Fase *Testing* dilakukan dengan skenario **Black-Box Testing**, berfokus pada hasil *input-output* tanpa membedah struktur kodenya kembali:
1.  **Pengujian Otentikasi**: Memasukkan email atau kata sandi tidak valid, serta pengujian akses modul lintas-batas (*Role* Subcon mencoba mengakses area admin). Hasil: Sistem memblokir akses dan menampilkan pesan otorisasi yang sesuai.
2.  **Pengujian Lockout Login**: Melakukan login gagal berulang sampai batas tertentu. Hasil: Sistem mengaktifkan pembatasan sementara sesuai data pada `auth_login_attempts`.
3.  **Pengujian Alur Verifikasi**: Simulasi pengajuan dokumen -> Paramedic menyetujui -> TOD menyetujui -> SHE menolak. Hasil: Status kembali ke `pending_hrga` untuk revisi, dan catatan penolakan tersimpan pada pengajuan.
4.  **Pengujian Upload Dokumen**: Unggah format file *malicious* (contoh `.exe` atau `.php`). Hasil: *Validation Error* muncul berkat kontrol tipe *MIME* yang mengikat.
5.  **Pengujian Google Drive dan Email**: Memicu persetujuan akhir atau pengiriman email workflow, lalu memverifikasi bahwa data folder, lampiran, dan status kirim tersimpan sesuai hasil integrasi Google Workspace.
