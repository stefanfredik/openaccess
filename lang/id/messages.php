<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Flash Messages
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut digunakan untuk flash messages di seluruh
    | aplikasi. Pesan-pesan ini biasanya digunakan di controllers
    | setelah melakukan operasi CRUD.
    |
    */

    'created' => ':resource berhasil dibuat.',
    'updated' => ':resource berhasil diperbarui.',
    'deleted' => ':resource berhasil dihapus.',
    'restored' => ':resource berhasil dipulihkan.',

    'error' => [
        'delete_has_relations' => 'Tidak dapat menghapus :resource karena masih memiliki data terkait.',
        'unauthorized' => 'Anda tidak memiliki izin untuk melakukan aksi ini.',
        'not_found' => ':resource tidak ditemukan.',
        'self_delete' => 'Tidak dapat menghapus diri sendiri.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama Resources
    |--------------------------------------------------------------------------
    */

    'resources' => [
        'area' => 'Wilayah',
        'pop' => 'POP',
        'router' => 'Router',
        'switch' => 'Switch',
        'olt' => 'OLT',
        'ont' => 'ONT',
        'access_point' => 'Access Point',
        'pole' => 'Tiang',
        'odp' => 'ODP',
        'tower' => 'Tower',
        'cable' => 'Kabel',
        'joint_box' => 'Joint Box',
        'odf' => 'ODF',
        'slack' => 'Slack',
        'splitter' => 'Splitter',
        'server' => 'Server',
        'site' => 'Site',
        'user' => 'Pengguna',
        'company' => 'Perusahaan',
        'connection' => 'Koneksi',
        'cpe' => 'CPE',
    ],
];
