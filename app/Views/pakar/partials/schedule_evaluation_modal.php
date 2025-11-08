<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 md:p-6"
    data-schedule-evaluation-modal role="dialog" aria-modal="true" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/40" data-modal-overlay></div>
    <div
        class="relative z-10 w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 dark:text-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-700">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100" data-modal-title>Evaluasi Konsultasi</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400" data-modal-schedule></p>
            </div>
            <button type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-slate-500 transition hover:border-slate-400 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 dark:border-slate-600 dark:text-slate-400 dark:hover:border-slate-500 dark:hover:text-slate-200 dark:focus:ring-offset-slate-900"
                aria-label="Tutup" data-modal-dismiss>
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form class="mt-4 space-y-4" data-schedule-evaluation-form>
            <div class="hidden rounded-lg border px-3 py-2 text-sm" data-modal-feedback role="alert"></div>
            <div>
                <label for="schedule-evaluation-summary"
                    class="text-sm font-medium text-slate-700 dark:text-slate-200">Ringkasan Evaluasi</label>
                <textarea id="schedule-evaluation-summary" name="evaluation[summary]" rows="4" required
                    class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                    data-modal-summary></textarea>
            </div>
            <label class="flex items-start gap-3">
                <input type="checkbox" id="schedule-evaluation-follow-up" name="evaluation[follow_up]" value="1"
                    class="mt-1 h-4 w-4 rounded border-slate-300 text-giziblue focus:ring-giziblue dark:border-slate-600 dark:bg-slate-900"
                    data-modal-follow-up>
                <span class="text-sm text-slate-600 dark:text-slate-300">
                    <span class="font-medium text-slate-700 dark:text-slate-200">Tandai tindak lanjut diperlukan</span>
                    <span class="block text-xs text-slate-500 dark:text-slate-400">Centang jika ibu membutuhkan penjadwalan ulang
                        atau aksi lanjutan.</span>
                </span>
            </label>
            <div class="border-t border-slate-200 pt-4 dark:border-slate-700">
                <div class="flex items-baseline justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Data Ibu</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Perbarui profil ibu saat evaluasi selesai.</p>
                    </div>
                </div>
                <div
                    class="mt-4 hidden rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-300"
                    data-modal-mother-summary>
                    <div class="text-[0.7rem] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Data ibu saat ini
                    </div>
                    <dl class="mt-3 grid gap-2 text-sm">
                        <div class="flex items-start justify-between gap-3" data-modal-mother-item="bb">
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Berat Badan</dt>
                            <dd class="text-sm font-medium text-slate-700 dark:text-slate-100" data-modal-mother-value>—</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3" data-modal-mother-item="tb">
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Tinggi Badan</dt>
                            <dd class="text-sm font-medium text-slate-700 dark:text-slate-100" data-modal-mother-value>—</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3" data-modal-mother-item="umur">
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Usia Ibu</dt>
                            <dd class="text-sm font-medium text-slate-700 dark:text-slate-100" data-modal-mother-value>—</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3" data-modal-mother-item="usia_bayi_bln">
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Usia Bayi</dt>
                            <dd class="text-sm font-medium text-slate-700 dark:text-slate-100" data-modal-mother-value>—</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3" data-modal-mother-item="laktasi_tipe">
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Tipe Laktasi</dt>
                            <dd class="text-sm font-medium text-slate-700 dark:text-slate-100" data-modal-mother-value>—</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3" data-modal-mother-item="aktivitas">
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Aktivitas Harian</dt>
                            <dd class="text-sm font-medium text-slate-700 dark:text-slate-100" data-modal-mother-value>—</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3" data-modal-mother-item="alergi">
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Alergi</dt>
                            <dd class="text-sm font-medium text-slate-700 dark:text-slate-100" data-modal-mother-value>—</dd>
                        </div>
                    </dl>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="schedule-mother-bb" class="text-sm font-medium text-slate-700 dark:text-slate-200">Berat Badan
                            (kg)</label>
                        <input type="number" step="0.1" id="schedule-mother-bb" name="mother[bb]"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            placeholder="Misal: 55" data-modal-mother-bb>
                    </div>
                    <div>
                        <label for="schedule-mother-tb" class="text-sm font-medium text-slate-700 dark:text-slate-200">Tinggi Badan
                            (cm)</label>
                        <input type="number" step="0.1" id="schedule-mother-tb" name="mother[tb]"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            placeholder="Misal: 160" data-modal-mother-tb>
                    </div>
                    <div>
                        <label for="schedule-mother-umur" class="text-sm font-medium text-slate-700 dark:text-slate-200">Usia Ibu
                            (tahun)</label>
                        <input type="number" id="schedule-mother-umur" name="mother[umur]"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            placeholder="Misal: 28" data-modal-mother-umur>
                    </div>
                    <div>
                        <label for="schedule-mother-usia-bayi" class="text-sm font-medium text-slate-700 dark:text-slate-200">Usia
                            Bayi (bulan)</label>
                        <input type="number" id="schedule-mother-usia-bayi" name="mother[usia_bayi_bln]"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            placeholder="Misal: 6" data-modal-mother-usia-bayi>
                    </div>
                    <div>
                        <label for="schedule-mother-laktasi" class="text-sm font-medium text-slate-700 dark:text-slate-200">Tipe
                            Laktasi</label>
                        <select id="schedule-mother-laktasi" name="mother[laktasi_tipe]"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            data-modal-mother-laktasi>
                            <option value="">Pilih tipe laktasi</option>
                            <option value="eksklusif">Eksklusif</option>
                            <option value="parsial">Parsial</option>
                        </select>
                    </div>
                    <div>
                        <label for="schedule-mother-aktivitas"
                            class="text-sm font-medium text-slate-700 dark:text-slate-200">Aktivitas Harian</label>
                        <select id="schedule-mother-aktivitas" name="mother[aktivitas]"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            data-modal-mother-aktivitas>
                            <option value="">Pilih tingkat aktivitas</option>
                            <option value="ringan">Ringan</option>
                            <option value="sedang">Sedang</option>
                            <option value="berat">Berat</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="schedule-mother-alergi" class="text-sm font-medium text-slate-700 dark:text-slate-200">Alergi
                            (pisahkan dengan koma)</label>
                        <input type="text" id="schedule-mother-alergi" name="mother[alergi]"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            placeholder="Misal: Kacang, Telur" data-modal-mother-alergi data-allergy-input>
                        <small class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Pisahkan alergi dengan koma (,)</small>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-400 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-white dark:focus:ring-offset-slate-900"
                    data-modal-dismiss>Batal</button>
                <button type="submit"
                    class="inline-flex items-center rounded-lg border border-giziblue bg-giziblue px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:border-blue-600 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-giziblue/70 dark:hover:border-blue-500 dark:hover:bg-blue-500 dark:focus:ring-offset-slate-900"
                    data-modal-submit>
                    <span>Simpan Evaluasi</span>
                    <span id="schedule-evaluation-indicator" class="ml-3 hidden text-xs text-slate-200"
                        data-modal-indicator>Memproses...</span>
                </button>
            </div>
        </form>
    </div>
</div>