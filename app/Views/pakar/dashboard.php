<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div
    x-data="pakarDashboard()"
    data-mothers-endpoint="<?= site_url('api/mothers') ?>"
    data-mother-detail-endpoint="<?= site_url('api/mothers') ?>"
    data-notification-id="pakar-dashboard-notification"
    class="space-y-8"
>
    <div
        id="pakar-dashboard-notification"
        class="hidden rounded-lg border border-transparent px-4 py-3 text-sm font-medium transition-all duration-200"
        role="status"
        aria-live="polite"
    ></div>

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard Pakar</h1>
            <p class="text-sm text-gray-600">Pantau kondisi ibu menyusui dan tindak lanjuti kebutuhan gizi mereka.</p>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <div class="hidden items-center gap-2 sm:flex">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                <span>Status terbaru diperbarui otomatis dari hasil inferensi.</span>
            </div>
            <a
                href="<?= site_url('pakar/consultations') ?>"
                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >Kelola Konsultasi</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <template x-for="card in cards" :key="card.key">
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="absolute inset-x-0 top-0 h-1" :class="card.color"></div>
                <div class="p-6">
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500" x-text="card.label"></p>
                    <div class="mt-3 flex items-end justify-between">
                        <h2 class="text-3xl font-bold text-gray-900" x-text="summary[card.key] ?? 0"></h2>
                        <span class="text-xs text-gray-400">Ibu terpantau</span>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-gray-600" x-text="card.description"></p>
                </div>
            </div>
        </template>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
        <div class="border-b border-gray-100 px-6 py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Ibu Menyusui</h2>
                    <p class="text-sm text-gray-500">Status dihitung dari hasil inferensi terbaru.</p>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center rounded-lg border border-blue-200 px-4 py-2 text-sm font-medium text-blue-600 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    @click="fetchMothers()"
                >Muat Ulang</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                    <tr>
                        <th scope="col" class="px-6 py-3">Nama</th>
                        <th scope="col" class="px-6 py-3">Umur</th>
                        <th scope="col" class="px-6 py-3">Usia Bayi</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Terakhir Diperbarui</th>
                        <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    <template x-if="loading">
                        <tr>
                            <td colspan="6" class="px-6 py-8">
                                <div class="flex items-center justify-center gap-3 text-sm text-gray-500">
                                    <div class="h-6 w-6 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
                                    Memuat data ibu menyusui...
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && mothers.length === 0">
                        <tr>
                            <td colspan="6" class="px-6 py-6 text-center text-sm text-gray-500">
                                Belum ada data ibu menyusui yang dapat ditampilkan.
                            </td>
                        </tr>
                    </template>
                    <template x-for="mother in mothers" :key="mother.id">
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900" x-text="mother.name ?? '-' "></td>
                            <td class="px-6 py-4" x-text="formatValue(mother.profile?.umur, ' tahun')"></td>
                            <td class="px-6 py-4" x-text="formatValue(mother.profile?.usia_bayi_bln, ' bln')"></td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="mother.status?.badge ?? 'bg-gray-100 text-gray-600'"
                                    x-text="mother.status?.label ?? 'Normal'"
                                ></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500" x-text="mother.latest_inference?.created_at_human ?? '-' "></td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-blue-600 px-3 py-2 text-xs font-semibold text-blue-600 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                    @click="openDetail(mother)"
                                >Lihat Detail</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <template x-if="detailOpen">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 px-4" x-transition.opacity>
            <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-xl" x-transition.scale @click.outside="closeDetail()">
                <template x-if="detailLoading">
                    <div class="flex min-h-[18rem] items-center justify-center p-10">
                        <div class="h-10 w-10 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
                        <span class="sr-only">Memuat detail ibu...</span>
                    </div>
                </template>
                <template x-if="!detailLoading && selectedMother">
                    <div>
                        <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900" x-text="selectedMother?.name ?? '-' "></h3>
                                <p class="text-sm text-gray-500" x-text="selectedMother?.email ?? 'Email belum tersedia'"></p>
                            </div>
                            <button
                                type="button"
                                class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                                @click="closeDetail()"
                            >
                                <span class="sr-only">Tutup</span>
                                &times;
                            </button>
                        </div>
                        <div class="grid gap-6 px-6 py-6 md:grid-cols-2">
                            <div class="space-y-4">
                                <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Profil Ibu</h4>
                                <dl class="space-y-2 text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Berat Badan</dt>
                                        <dd x-text="formatValue(selectedMother?.profile?.bb, ' kg')"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Tinggi Badan</dt>
                                        <dd x-text="formatValue(selectedMother?.profile?.tb, ' cm')"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Umur</dt>
                                        <dd x-text="formatValue(selectedMother?.profile?.umur, ' tahun')"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Usia Bayi</dt>
                                        <dd x-text="formatValue(selectedMother?.profile?.usia_bayi_bln, ' bulan')"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Tipe Laktasi</dt>
                                        <dd x-text="selectedMother?.profile?.laktasi_tipe ?? '-' "></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Aktivitas</dt>
                                        <dd x-text="selectedMother?.profile?.aktivitas ?? '-' "></dd>
                                    </div>
                                </dl>
                                <div class="space-y-3 text-sm text-gray-600">
                                    <div>
                                        <h5 class="font-medium text-gray-700">Alergi</h5>
                                        <p class="mt-1 rounded-lg bg-gray-50 px-3 py-2" x-text="listValue(selectedMother?.profile?.alergi)"></p>
                                    </div>
                                    <div>
                                        <h5 class="font-medium text-gray-700">Preferensi Makanan</h5>
                                        <p class="mt-1 rounded-lg bg-gray-50 px-3 py-2" x-text="listValue(selectedMother?.profile?.preferensi)"></p>
                                    </div>
                                    <div>
                                        <h5 class="font-medium text-gray-700">Riwayat Kesehatan</h5>
                                        <p class="mt-1 rounded-lg bg-gray-50 px-3 py-2" x-text="listValue(selectedMother?.profile?.riwayat)"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Hasil Inferensi Terbaru</h4>
                                <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-blue-50 to-white p-4">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="selectedMother?.status?.badge ?? 'bg-gray-100 text-gray-600'"
                                            x-text="selectedMother?.status?.label ?? 'Normal'"
                                        ></span>
                                        <span class="text-xs text-gray-400" x-text="selectedMother?.latest_inference?.created_at_human ?? '-' "></span>
                                    </div>
                                    <ul class="mt-4 space-y-2 text-sm text-gray-700" role="list">
                                        <template x-if="(selectedMother?.latest_inference?.recommendations ?? []).length === 0">
                                            <li class="rounded-lg bg-white/80 px-3 py-2 text-gray-500">Belum ada rekomendasi khusus.</li>
                                        </template>
                                        <template x-for="(item, index) in selectedMother?.latest_inference?.recommendations ?? []" :key="index">
                                            <li class="rounded-lg bg-white px-3 py-2 shadow-sm ring-1 ring-gray-100" x-text="item"></li>
                                        </template>
                                    </ul>
                                </div>
                                <div class="rounded-2xl border border-gray-100 bg-white p-4">
                                    <h5 class="text-sm font-medium text-gray-700">Fakta Dasar</h5>
                                    <ul class="mt-3 space-y-2 text-sm text-gray-600">
                                        <template x-for="(value, key) in selectedMother?.latest_inference?.facts ?? {}" :key="key">
                                            <li class="flex justify-between gap-4">
                                                <span class="font-medium text-gray-500" x-text="formatKey(key)"></span>
                                                <span class="text-right" x-text="formatDisplay(value)"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end border-t border-gray-100 px-6 py-4">
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                @click="closeDetail()"
                            >Tutup</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>

<noscript>
    <div class="rounded-lg bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
        Aktifkan JavaScript untuk melihat detail ibu menyusui dan informasi inferensi.
    </div>
</noscript>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('js/pakar.js') ?>"></script>
<?= $this->endSection() ?>
