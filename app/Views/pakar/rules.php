<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div
    class="space-y-6"
    data-pakar-rules
    data-comment-endpoint-template="<?= esc($commentEndpointTemplate, 'attr') ?>">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm shadow-slate-100 ring-1 ring-gray-100 dark:border-black/70 dark:bg-slate-950/70 dark:shadow-black/40 dark:ring-black/60">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-slate-100">Review Rules Basis Pengetahuan</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400">
                    Tinjau aturan inferensi yang aktif dan berikan catatan profesional Anda.
                </p>
            </div>
            <a
                href="<?= site_url('pakar/dashboard') ?>"
                class="inline-flex items-center justify-center rounded-lg border border-blue-600 bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:border-blue-700 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-black/70 dark:focus:ring-offset-slate-950">
                Kembali ke Dashboard
            </a>
        </div>

        <div
            class="mt-6 hidden rounded-lg border px-4 py-3 text-sm font-medium transition-all duration-200"
            role="status"
            aria-live="polite"
            data-rules-feedback></div>

        <?php if ($rules === []) : ?>
            <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50/80 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300">
                Belum ada rule yang tersedia untuk ditinjau.
            </div>
        <?php else : ?>
            <div class="mt-6 grid gap-5 lg:grid-cols-2" data-rules-list>
                <?php foreach ($rules as $rule) : ?>
                    <article
                        class="flex h-full flex-col justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white/90 p-5 text-sm shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 transition hover:shadow-md dark:border-slate-200/30 dark:bg-slate-950/70 dark:text-slate-200 dark:shadow-black/30 dark:ring-slate-200/30"
                        data-rule-card
                        data-rule-id="<?= esc((string) $rule['id'], 'attr') ?>">
                        <div class="space-y-3">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between gap-3">
                                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                        <?= esc($rule['name']) ?>
                                    </h2>
                                    <span class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:border-slate-200/40 dark:text-slate-300">
                                        Versi <?= esc($rule['version']) ?>
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="h-2 w-2 rounded-full <?= $rule['isActive'] ? 'bg-emerald-500' : 'bg-slate-400 dark:bg-slate-500' ?>"></span>
                                        <?= $rule['isActive'] ? 'Aktif' : 'Tidak Aktif' ?>
                                    </span>
                                    <span>
                                        Efektif: <?= esc($rule['effectiveFrom'] ?? 'Belum ditentukan') ?>
                                    </span>
                                    <span>
                                        Terakhir diperbarui: <?= esc($rule['updatedAt'] ?? 'Belum tersedia') ?>
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="rule-comment-<?= esc((string) $rule['id'], 'attr') ?>">
                                    Komentar Pakar
                                </label>
                                <textarea
                                    id="rule-comment-<?= esc((string) $rule['id'], 'attr') ?>"
                                    data-rule-comment
                                    data-rule-original-comment="<?= esc($rule['komentarPakar'], 'attr') ?>"
                                    rows="4"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-200/40 dark:bg-slate-900/40 dark:text-slate-100"
                                    placeholder="Tuliskan catatan atau masukan untuk rule ini..."><?= esc($rule['komentarPakar']) ?></textarea>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Komentar ini akan membantu tim meninjau kualitas rule secara berkala.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <div class="hidden items-center gap-2 text-xs text-slate-500 dark:text-slate-400" data-rule-indicator>
                                <span class="h-4 w-4 animate-spin rounded-full border-2 border-blue-200 border-t-blue-600" aria-hidden="true"></span>
                                Menyimpan perubahan...
                            </div>
                            <div class="flex flex-1 items-center justify-end gap-3">
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-200/40 dark:text-slate-300 dark:hover:bg-slate-900/50 dark:focus:ring-offset-slate-950"
                                    data-rule-reset>
                                    Atur Ulang
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-lg border border-blue-600 bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:border-blue-700 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-200/40 dark:focus:ring-offset-slate-950"
                                    data-rule-save>
                                    Simpan Komentar
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script type="module" src="<?= base_url('js/pakar.js') ?>"></script>
<?= $this->endSection() ?>
