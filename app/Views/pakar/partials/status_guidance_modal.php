<div
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 md:p-6"
    data-status-guidance-modal
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
>
    <div class="absolute inset-0 bg-slate-900/40" data-status-guidance-overlay></div>
    <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 dark:text-slate-200">
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-700">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Panduan Status Pemantauan</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Kenali arti setiap status agar tindak lanjut gizi dapat diberikan secara tepat dan cepat.
                </p>
            </div>
            <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-slate-500 transition hover:border-slate-400 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 dark:border-slate-600 dark:text-slate-400 dark:hover:border-slate-500 dark:hover:text-slate-200 dark:focus:ring-offset-slate-900"
                aria-label="Tutup"
                data-status-guidance-close
            >
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="mt-6 space-y-5 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
            <div class="rounded-xl border border-emerald-200/80 bg-emerald-50/70 p-4 dark:border-emerald-400/30 dark:bg-emerald-500/10">
                <div class="flex items-start gap-3">
                    <span class="mt-1 inline-flex h-3 w-3 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                    <div>
                        <h3 class="text-base font-semibold text-emerald-700 dark:text-emerald-300">Status Normal</h3>
                        <p>Ibu berada pada kondisi stabil dengan kebutuhan gizi yang telah terpenuhi. Lanjutkan pemantauan rutin sesuai jadwal.</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-amber-200/80 bg-amber-50/70 p-4 dark:border-amber-400/30 dark:bg-amber-500/10">
                <div class="flex items-start gap-3">
                    <span class="mt-1 inline-flex h-3 w-3 rounded-full bg-amber-500 dark:bg-amber-300"></span>
                    <div>
                        <h3 class="text-base font-semibold text-amber-700 dark:text-amber-200">Perlu Pemantauan</h3>
                        <p>Terjadi perubahan pola makan atau gejala ringan. Evaluasi kembali rencana gizi dan jadwalkan konsultasi untuk memastikan kebutuhan ibu tetap terpenuhi.</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-rose-200/80 bg-rose-50/70 p-4 dark:border-rose-400/30 dark:bg-rose-500/10">
                <div class="flex items-start gap-3">
                    <span class="mt-1 inline-flex h-3 w-3 rounded-full bg-rose-500 dark:bg-rose-300"></span>
                    <div>
                        <h3 class="text-base font-semibold text-rose-700 dark:text-rose-200">Prioritas Tinggi</h3>
                        <p>Ibu memerlukan tindak lanjut segera karena ditemukannya risiko gizi serius. Hubungi ibu untuk konsultasi mendalam dan susun intervensi khusus.</p>
                    </div>
                </div>
            </div>
            <p class="rounded-lg bg-slate-100/80 px-4 py-3 text-xs text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                Status dihitung secara otomatis dari hasil inferensi terbaru dan akan diperbarui ketika Anda menjalankan evaluasi atau menerima data baru dari ibu.
            </p>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button
                type="button"
                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-400 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-white dark:focus:ring-offset-slate-900"
                data-status-guidance-close
                data-status-guidance-focus
            >Mengerti</button>
        </div>
    </div>
</div>
