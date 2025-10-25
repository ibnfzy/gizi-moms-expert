<?php

/**
 * @var array<string, string> $statusOptions
 * @var string $createEndpoint
 */
?>
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 md:p-6" data-schedule-create-modal
  role="dialog" aria-modal="true" aria-hidden="true">
  <div class="absolute inset-0 bg-slate-900/40" data-modal-overlay></div>
  <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 dark:text-slate-200">
    <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-700">
      <div>
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Buat Jadwal Konsultasi</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
          Tentukan ibu, waktu konsultasi, serta detail pendukung lainnya.
        </p>
      </div>
      <button type="button"
        class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-slate-500 transition hover:border-slate-400 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 dark:border-slate-600 dark:text-slate-400 dark:hover:border-slate-500 dark:hover:text-slate-200 dark:focus:ring-offset-slate-900"
        aria-label="Tutup" data-modal-dismiss>
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <form class="mt-4 space-y-4" data-schedule-create-form data-submit-url="<?= esc($createEndpoint) ?>">
      <input type="hidden" id="schedule-create-status" name="status" value="pending" data-create-status
        data-default-value="pending">
      <div class="hidden rounded-lg border px-3 py-2 text-sm" data-modal-feedback role="alert"></div>
      <div>
        <label for="schedule-create-mother" class="text-sm font-medium text-slate-700 dark:text-slate-200">Ibu
          Konsultasi</label>
        <select id="schedule-create-mother" name="mother_id" required
          class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
          data-create-mother>
          <option value="">Memuat daftar ibu...</option>
        </select>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Daftar diambil dari profil ibu yang telah terdaftar.
        </p>
      </div>
      <div>
        <label for="schedule-create-datetime" class="text-sm font-medium text-slate-700 dark:text-slate-200">Waktu
          Konsultasi</label>
        <input type="datetime-local" id="schedule-create-datetime" name="scheduled_at" required
          class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
          data-create-datetime>
      </div>
      <div>
        <label for="schedule-create-location" class="text-sm font-medium text-slate-700 dark:text-slate-200">Lokasi
          Konsultasi</label>
        <input type="text" id="schedule-create-location" name="location"
          placeholder="Contoh: Klinik, daring, atau alamat tertentu"
          class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
          data-create-location>
      </div>
      <div>
        <label for="schedule-create-notes" class="text-sm font-medium text-slate-700 dark:text-slate-200">Catatan
          Tambahan</label>
        <textarea id="schedule-create-notes" name="notes" rows="3" placeholder="Catatan untuk persiapan konsultasi"
          class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
          data-create-notes></textarea>
      </div>
      <div class="flex items-center justify-end gap-3 pt-2">
        <button type="button"
          class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-400 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-white dark:focus:ring-offset-slate-900"
          data-modal-dismiss>Batal</button>
        <button type="submit"
          class="inline-flex items-center rounded-lg border border-giziblue bg-giziblue px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:border-blue-600 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-giziblue/70 dark:hover:border-blue-500 dark:hover:bg-blue-500 dark:focus:ring-offset-slate-900"
          data-modal-submit>
          <span>Simpan Jadwal</span>
          <span class="ml-3 hidden text-xs text-slate-200" data-modal-indicator>Memproses...</span>
        </button>
      </div>
    </form>
  </div>
</div>