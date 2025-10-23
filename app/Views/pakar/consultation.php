<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div id="consultation-page">
    <?= view('pakar/partials/consultation_page', [
        'consultations'        => $consultations,
        'selectedConsultation' => $selectedConsultation,
        'userRole'             => $userRole ?? 'pakar',
    ]) ?>
</div>
<?= $this->endSection() ?>
