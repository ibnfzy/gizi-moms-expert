<?php
$request     = service('request');
$session     = session();
$currentPath = trim($request->getUri()->getPath(), '/');
$userRole    = $session->get('user_role') ?? 'pakar';

if ($userRole === 'admin') {
    $navigation = [
        [
            'label' => 'Dashboard',
            'href'  => site_url('admin/dashboard'),
            'match' => 'admin/dashboard',
            'icon'  => 'home',
        ],
        [
            'label' => 'Manajemen Data Ibu',
            'href'  => site_url('admin/mothers'),
            'match' => 'admin/mothers',
            'icon'  => 'users',
        ],
        [
            'label' => 'Manajemen Pengguna',
            'href'  => site_url('admin/users'),
            'match' => 'admin/users',
            'icon'  => 'user-cog',
        ],
        [
            'label' => 'Manajemen Rules',
            'href'  => site_url('admin/rules'),
            'match' => 'admin/rules',
            'icon'  => 'document-text',
        ],
    ];

    $tipsText = 'Gunakan halaman manajemen untuk memperbarui akses pengguna dan menjaga data ibu tetap akurat.';
} else {
    $navigation = [
        [
            'label' => 'Dashboard',
            'href'  => site_url('pakar/dashboard'),
            'match' => 'pakar/dashboard',
            'icon'  => 'home',
        ],
        [
            'label' => 'Konsultasi',
            'href'  => site_url('pakar/consultations'),
            'match' => 'pakar/consultations',
            'icon'  => 'chat-bubble',
        ],
        [
            'label' => 'Jadwal Konsultasi',
            'href'  => site_url('pakar/schedules'),
            'match' => 'pakar/schedules',
            'icon'  => 'calendar',
        ],
        [
            'label' => 'Panduan Status',
            'href'  => '#panduan-status',
            'match' => '',
            'type'  => 'modal',
            'icon'  => 'information-circle',
        ],
    ];

    $tipsText = 'Gunakan panel konsultasi dan jadwal untuk memantau percakapan aktif serta menindaklanjuti rekomendasi dari hasil inferensi.';
}

return [
    'items'       => $navigation,
    'currentPath' => $currentPath,
    'tipsText'    => $tipsText,
];
