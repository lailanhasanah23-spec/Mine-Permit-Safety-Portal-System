<?php

return [
    // Folder spreadsheet monitoring per kategori, contoh:
    // storage/app/private/monitoring/pengajuan_commisioning.xlsx
    'base_dir' => storage_path('app/private/monitoring'),

    // Urutan prioritas ekstensi file yang akan dicari.
    'extensions' => ['xlsx', 'xls', 'csv'],

    // Jumlah sampel baris yang ditampilkan di dashboard monitoring admin.
    'sample_limit' => 8,
];
