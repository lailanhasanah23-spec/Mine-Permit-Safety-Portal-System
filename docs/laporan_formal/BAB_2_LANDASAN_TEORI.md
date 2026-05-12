# BAB II
# LANDASAN TEORI

## 2.1 Tinjauan Pustaka
Dalam perancangan sistem ini, beberapa konsep dasar operasional pertambangan diadopsi sebagai referensi utama.

### 2.1.1 Keselamatan dan Kesehatan Kerja (K3) Pertambangan
Keselamatan dan Kesehatan Kerja (K3) adalah segala kegiatan untuk menjamin dan melindungi keselamatan dan kesehatan tenaga kerja melalui upaya pencegahan kecelakaan kerja dan penyakit akibat kerja. Di sektor pertambangan yang memiliki risiko fatalitas tinggi, kaidah K3 diatur secara ketat oleh hukum ketenagakerjaan dan regulasi Kementerian ESDM.

### 2.1.2 Mine Permit dan SIMPER
1.  ***Mine Permit*** (Izin Tambang): Dokumen identitas atau kartu tanda izin masuk berupa ID khusus (*badge*) yang wajib dimiliki oleh semua orang yang akan memasuki area pertambangan aktif.
2.  **SIMPER** (Surat Izin Mengemudi Perusahaan): Dokumen legalitas internal yang dikeluarkan oleh perusahaan tambang, memberikan izin kepada karyawannya untuk mengoperasikan sarana transportasi (LV) maupun unit alat berat (A2B) di kawasan tambang, yang diselaraskan dengan spesifikasi keterampilan (*grade*) pekerja.

Penerbitan SIMPER mewajibkan personel untuk memiliki Surat Izin Mengemudi (SIM) Kepolisian Republik Indonesia, status layak medis (*Fit to Work*), dan lulus ujian kompetensi perusahaan.

## 2.2 Konsep Sistem Informasi
Sistem Informasi adalah kombinasi terorganisasi antara manusia, perangkat keras, perangkat lunak, jaringan komunikasi, dan sumber data yang mengumpulkan, mengubah, dan menyebarkan informasi dalam sebuah institusi.
Konsep digitalisasi perizinan menggunakan prinsip *Workflow Automation*, yang merupakan desain, eksekusi, dan otomatisasi proses yang didasarkan pada aturan (*rule-based*) agar tugas (verifikasi dan validasi) dapat diestafetkan dari satu pihak ke pihak lain tanpa intervensi penyuratan fisik.

## 2.3 Teknologi Pendukung
### 2.3.1 Framework Laravel
Laravel adalah kerangka kerja (*framework*) berbasis bahasa pemrograman PHP yang *open-source* dan bersifat gratis, diciptakan oleh Taylor Otwell. Laravel mengadopsi pola arsitektur Model-View-Controller (MVC) yang memisahkan logika aplikasi (Controller), akses data (Model), dan presentasi antarmuka (View). Kelebihan Laravel antara lain:
*   Mendukung *Object-Relational Mapping* (ORM) yang bernama Eloquent, memudahkan manipulasi tabel secara *object-oriented*.
*   Mendukung penanganan CSRF (*Cross-Site Request Forgery*) *Protection* secara bawaan untuk keamanan pengiriman data.
*   Sistem perutean (*routing*) yang sangat ekspresif.

### 2.3.2 MySQL
MySQL merupakan Sistem Manajemen Basis Data Relasional (*Relational Database Management System*/RDBMS) berbasis *Structured Query Language* (SQL). Sifat rasional berarti data disimpan dalam tabel-tabel terpisah yang terhubung (*relationships*) untuk optimalisasi pencarian dan pencegahan redudansi (*normalization*). MySQL mendukung mekanisme keutuhan data seperti `ACID Transactions` dan penanganan konkurensi (seperti *Pessimistic Locking*) yang vital dalam aplikasi multitingkat.

### 2.3.3 Antarmuka Pengguna Berbasis Web (HTML & CSS)
*   **HTML (HyperText Markup Language)**: Bahasa markah dasar penyusun struktur statis dokumen web.
*   **CSS (Cascading Style Sheets)**: Bahasa pemformatan yang mendefinisikan estetika presentasi (warna, *layout*, transisi). Dalam sistem ini, arsitektur *Vanilla CSS* dan desain kustom (tanpa library Bootstrap/Tailwind) digunakan untuk menghasilkan antarmuka bergaya "Industrial Enterprise", menjaga struktur kode yang lebih bersih dan meminimalisasi latensi akibat file *library* berukuran besar.

### 2.3.4 Google Drive API dan OAuth
Sistem ini menggunakan Google Drive API v3 untuk mengatasi pengelolaan limitasi disk lokal (*server storage*). Aplikasi web bertindak sebagai jembatan yang mengirim file-file lampiran sensitif pekerja (seperti hasil KTP, MCU, nilai ujian) ke *folder* rahasia di penyimpanan *Cloud* korporasi (Google Workspace). Koneksi terjalin menggunakan protokol OAuth 2.0 dan token akses yang secara periodik disegarkan (*refresh token*), memastikan integritas privasi data Subcon tidak dapat bocor secara publik.

### 2.3.5 Role-Based Access Control (RBAC)
RBAC adalah pendekatan pengendalian akses yang membatasi hak pengguna berdasarkan peran yang terdefinisi di sistem. Pada portal ini, peran dipisahkan menjadi `admin`, `she`, `hrga`, `tod`, `paramedic`, dan `subcon`.

Penerapannya di sistem ini dilakukan melalui middleware dan pengecekan role pada controller, sehingga:
*   **Admin** dapat mengelola master data, akun pengguna, audit log, integrasi Google, dan modul administrasi.
*   **SHE** memiliki hak verifikasi akhir, persetujuan pengajuan, serta akses ke workflow email dan pengawasan dokumen.
*   **HRGA/Subcon** menangani pembuatan dan revisi pengajuan pada tahap awal.
*   **Paramedic** dan **TOD** hanya mengakses tahapan verifikasi yang relevan dengan tugasnya.

### 2.3.6 Audit Trail, Lockout Login, dan Transaksi Basis Data
Audit trail adalah mekanisme pencatatan aktivitas penting agar seluruh perubahan dapat ditelusuri kembali. Pada sistem ini, audit trail digunakan untuk mencatat perubahan data yang krusial seperti pengajuan, persetujuan, penolakan, dan pengiriman email.

Untuk menjaga keamanan autentikasi, sistem juga menerapkan pembatasan percobaan login melalui tabel `auth_login_attempts`. Saat percobaan login gagal terlalu banyak, akun akan dibatasi sementara agar mencegah brute-force.

Di sisi integritas data, transaksi database dan *pessimistic locking* dipakai agar proses yang sensitif, seperti verifikasi dan persetujuan pengajuan, tidak menghasilkan duplikasi atau kondisi balapan (*race condition*).

### 2.3.7 Integrasi Email dan Sinkronisasi Formulir
Selain penyimpanan dokumen, sistem juga memiliki alur pengiriman email berbasis template dan sinkronisasi data dari Google Form. Template email dipakai untuk pengiriman notifikasi SIMPER, sedangkan endpoint sinkronisasi menerima payload dari Google Apps Script untuk membuat pengajuan secara otomatis.

Mekanisme ini memastikan data yang masuk dari kanal eksternal tetap konsisten dengan master kategori, status awal, dan lampiran yang dipersyaratkan.

## 2.4 Ringkasan Keterkaitan Teori dan Implementasi
Secara ringkas, teori-teori pada bab ini diterapkan langsung pada sistem sebagai berikut:
*   **Laravel + MVC** dipakai untuk memisahkan tampilan, logika bisnis, dan akses data.
*   **MySQL + transaksi** dipakai untuk menjaga konsistensi data pengajuan dan audit.
*   **RBAC** dipakai untuk membatasi hak akses berdasarkan peran pengguna.
*   **Google Workspace** dipakai untuk penyimpanan file, otorisasi, dan pengiriman email operasional.
*   **Audit trail** dipakai untuk memastikan seluruh aktivitas penting dapat ditelusuri kembali.
