<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8" data-admin-dashboard data-stats-endpoint="<?= site_url('api/stats') ?>"
  data-rules-endpoint="<?= site_url('api/rules') ?>" data-notification-id="admin-dashboard-notification">
  <div id="admin-dashboard-notification"
    class="hidden rounded-lg border border-transparent px-4 py-3 text-sm font-medium transition-all duration-200"
    role="status" aria-live="polite"></div>

  <section class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-slate-100">Dashboard Admin</h1>
        <p class="text-sm text-gray-600 dark:text-slate-400">Pantau statistik sistem pakar dan ringkasan rule terbaru.
        </p>
      </div>
      <div class="flex flex-wrap gap-3">
        <a href="<?= site_url('admin/mothers') ?>"
          class="inline-flex items-center rounded-lg border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-600 shadow-sm hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-blue dark:hover:bg-slate-900/50 dark:focus:ring-offset-slate-950">Kelola
          Data Ibu</a>
        <a href="<?= site_url('admin/rules') ?>"
          class="inline-flex items-center rounded-lg border border-blue-600 bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:border-blue-700 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-black/70 dark:focus:ring-offset-slate-950">Kelola
          Rules</a>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" data-stats-grid>
      <div class="col-span-full flex justify-center py-12" data-stats-loader>
        <div class="h-10 w-10 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin" aria-hidden="true">
        </div>
        <span class="sr-only">Memuat statistik...</span>
      </div>
    </div>
  </section>

  <section class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Rule Terbaru</h2>
        <p class="text-sm text-gray-500 dark:text-slate-400">Daftar rule yang terakhir diperbarui pada basis
          pengetahuan.</p>
      </div>
      <button type="button" data-refresh-rules
        class="inline-flex items-center rounded-lg border border-blue-200 px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-blue dark:hover:bg-slate-900/50 dark:focus:ring-offset-slate-950">Muat
        Ulang</button>
    </div>

    <div
      class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm shadow-slate-100 ring-1 ring-gray-100 dark:border-black/70 dark:bg-slate-950/70 dark:shadow-black/40 dark:ring-black/60">
      <div class="border-b border-gray-100 px-6 py-6 dark:border-black/70 md:hidden" data-rules-card-wrapper>
        <div data-rules-cards class="space-y-4">
          <div
            class="rounded-2xl border border-slate-200/80 bg-white/80 p-5 text-sm text-gray-500 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-black/70 dark:bg-slate-950/70 dark:text-slate-400 dark:shadow-black/30 dark:ring-black/60">
            <div class="flex items-center justify-center gap-3">
              <div class="h-6 w-6 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin"
                aria-hidden="true"></div>
              Memuat data rule...
            </div>
          </div>
        </div>
      </div>
      <div class="hidden border-t border-gray-100 dark:border-black/70 md:block">
        <div class="overflow-x-auto">
          <table class="min-w-[64rem] border-collapse border border-black/40 text-left text-sm dark:border-gray-300">
            <thead
              class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-slate-950/70 dark:text-slate-200">
              <tr>
                <th scope="col" class="border border-black/40 px-6 py-3 dark:border-gray-300">ID Rule</th>
                <th scope="col" class="border border-black/40 px-6 py-3 dark:border-gray-300">Nama</th>
                <th scope="col" class="border border-black/40 px-6 py-3 dark:border-gray-300">Status</th>
                <th scope="col" class="border border-black/40 px-6 py-3 text-right dark:border-gray-300">Terakhir
                  Diperbarui</th>
              </tr>
            </thead>
            <tbody data-rules-body class="text-gray-700 dark:text-slate-200">
              <tr data-rules-loader-row>
                <td colspan="5" class="border border-black/40 px-6 py-8 dark:border-gray-300">
                  <div class="flex items-center justify-center gap-3 text-sm text-gray-500 dark:text-slate-400">
                    <div class="h-6 w-6 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin"
                      aria-hidden="true"></div>
                    Memuat data rule...
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('js/admin.js') ?>"></script>
<?= $this->endSection() ?>