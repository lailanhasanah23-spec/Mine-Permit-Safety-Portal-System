# BAB V
# KESIMPULAN DAN SARAN

## 5.1 Kesimpulan
Berdasarkan hasil analisis, perancangan, pengembangan, hingga pengujian terhadap **Sistem Portal Mine Permit dan SIMPER**, dapat ditarik beberapa kesimpulan sebagai berikut:
1.  **Digitalisasi Birokrasi Berhasil Diimplementasikan**: Sistem informasi yang dibangun sukses mereplika sekaligus mengotomatisasi alur birokrasi manual yang rentan kesalahan. Pengawasan berantai pada tahap HRGA, Paramedic, TOD, dan SHE kini terekam secara digital, linier, dan akuntabel di bawah satu atap (*dashboard* tunggal).
2.  **Peningkatan Keamanan Integritas Data**: Penerapan kontrol akses (*Role-Based Access Control*) memastikan prinsip otorisasi *least-privilege*; setiap departemen hanya mengeksekusi validasi sesuai tugas pokok fungsinya. Di sisi basis data, penggunaan transaksi dan penanganan konkurensi yang stabil menghindarkan risiko manipulasi dan asinkronisasi data.
3.  **Sentralisasi Penyimpanan dan Notifikasi Tepat Guna**: Integrasi penyimpanan Google Drive dan workflow email berbasis template membantu mengurangi penumpukan dokumen fisik sekaligus mempercepat distribusi informasi kepada pihak yang terkait.
4.  **Akselerasi Pengambilan Keputusan**: Hadirnya log aktivitas, pembatasan login gagal, dan penanda status berbasis warna memberikan kemudahan bagi level manajemen (KTT) untuk memantau *bottleneck* pengajuan dan menganalisis tren pekerja di area tambang.

## 5.2 Saran
Meskipun tujuan penelitian telah tercapai dan sistem dinyatakan stabil pada tingkat produksi (*production-ready*), terdapat beberapa rekomendasi bagi tahapan pengembangan lebih lanjut untuk menyempurnakan kegunaan sistem di lapangan:
1.  **Pengingat Kadaluwarsa Berbasis WhatsApp**: Diperlukan pengembangan API tambahan untuk menyuntikkan *Gateway* (gerbang) pesan WhatsApp (WA). Notifikasi ini akan sangat fungsional bila diaplikasikan untuk fungsi peringatan pradini, misal 30 hari sebelum SIMPER milik personel habis masa berlakunya (*Expired Date*).
2.  **Aplikasi Pemindai Lapangan (*Mobile Scanner*)**: Pengembangan sistem pemindai Kode *Quick Response* (QR Code) khusus *mobile-native* (Android/iOS) bagi satuan pengamanan (Sekuriti/Checker). Pemindai ini bertujuan merekonsiliasi identitas digital pada sistem ini dengan ID *Badge* fisik yang dikenakan pekerja sesaat sebelum melewati pos penjagaan tambang secara nirsentuh.
3.  **Verifikasi Autentikasi Ganda (*Two-Factor Authentication/2FA*)**: Untuk menutup probabilitas peretasan akibat kelalaian berbagi *password* (*credential sharing*), direkomendasikan penyisipan perlindungan 2FA seperti pengiriman pin *One-Time Password* (OTP) atau verifikasi berbasis aplikasi sebelum *System Admin* maupun *SHE* memproses dokumen final.
4.  **Interoperabilitas Ekosistem ERP**: Pengembangan antar-muka (*interface*) agar sistem ini memiliki kapabilitas berkomunikasi data timbal-balik dengan perangkat lunak HRIS (*Human Resource Information System*) utama perusahan untuk pelacakan masa kerja, status *resign* karyawan, maupun pengintegrasian sanksi poin pelanggaran lalu lintas tambang.
