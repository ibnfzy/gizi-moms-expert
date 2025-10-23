<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div id="consultation-page" data-pakar-consultation>
    <?= view('pakar/partials/consultation_page', [
        'consultations'        => $consultations,
        'selectedConsultation' => $selectedConsultation,
        'userRole'             => $userRole ?? 'pakar',
    ]) ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script type="module" src="<?= base_url('js/pakar.js') ?>"></script>
<?= $this->endSection() ?>
