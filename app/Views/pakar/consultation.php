<?= $this->extend('layouts/main') ?>

<?php
    $selectedId = $selectedConsultation['id'] ?? '';
    $userRole = $userRole ?? 'pakar';
?>

<?= $this->section('content') ?>
<div
    x-data="pakarConsultation()"
    data-messages-endpoint="<?= site_url('api/messages') ?>"
    data-selected-id="<?= esc($selectedId) ?>"
    data-user-role="<?= esc($userRole) ?>"
    data-notification-id="pakar-consultation-notification"
    class="flex min-h-[70vh] flex-col gap-6 lg:flex-row"
>
    <div
        id="pakar-consultation-notification"
        class="hidden w-full rounded-lg border border-transparent px-4 py-3 text-sm font-medium transition-all duration-200"
        role="status"
        aria-live="polite"
    ></div>

    <aside class="w-full rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 lg:w-80">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Sesi Konsultasi</h2>
            <p class="text-sm text-gray-500">Pilih sesi untuk melihat percakapan.</p>
        </div>
        <div class="max-h-[28rem] overflow-y-auto">
            <template x-if="loadingList">
                <div class="flex items-center justify-center gap-3 px-5 py-6 text-sm text-gray-500">
                    <div class="h-5 w-5 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
                    Memuat daftar konsultasi...
                </div>
            </template>
            <template x-if="!loadingList && consultations.length === 0">
                <div class="px-5 py-6 text-sm text-gray-500">
                    Belum ada sesi konsultasi yang terdaftar.
                </div>
            </template>
            <ul class="divide-y divide-gray-100" role="list">
                <template x-for="item in consultations" :key="item.id">
                    <li>
                        <button
                            type="button"
                            class="flex w-full flex-col gap-2 px-5 py-4 text-left transition"
                            :class="item.id === selectedId ? 'bg-blue-50/80' : 'hover:bg-gray-50'"
                            @click="selectConsultation(item.id)"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900" x-text="item.mother?.name ?? 'Ibu Menyusui'"></p>
                                    <p class="text-xs text-gray-500" x-text="item.updated_human ?? '-' "></p>
                                </div>
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold"
                                    :class="statusBadge(item.mother?.status?.code)"
                                    x-text="statusLabel(item.mother?.status?.label)"
                                ></span>
                            </div>
                            <p class="text-xs text-gray-600" x-text="item.last_message?.text ? truncate(item.last_message.text, 80) : 'Belum ada pesan.'"></p>
                        </button>
                    </li>
                </template>
            </ul>
        </div>
    </aside>

    <section class="flex-1 rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
        <template x-if="!selectedId">
            <div class="flex h-full flex-col items-center justify-center gap-3 p-8 text-center text-gray-500">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-500">
                    <span class="text-2xl font-semibold">💬</span>
                </div>
                <p class="text-base font-semibold text-gray-700">Pilih sesi konsultasi</p>
                <p class="text-sm">Mulai percakapan dengan memilih salah satu ibu dari daftar di sebelah kiri.</p>
            </div>
        </template>

        <template x-if="selectedId">
            <div class="flex h-full flex-col">
                <template x-if="loadingMessages">
                    <div class="flex flex-1 items-center justify-center gap-3 text-sm text-gray-500">
                        <div class="h-6 w-6 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
                        Memuat percakapan...
                    </div>
                </template>
                <template x-if="!loadingMessages && selected">
                    <div class="flex h-full flex-col">
                        <div class="flex items-start justify-between border-b border-gray-100 px-6 py-5">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900" x-text="selected.mother?.name ?? 'Ibu Menyusui'"></h3>
                                <p class="text-sm text-gray-500" x-text="selected.mother?.email ?? 'Email tidak tersedia'"></p>
                            </div>
                            <div class="text-right text-sm text-gray-500">
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="statusBadge(selected.mother?.status?.code)"
                                    x-text="statusLabel(selected.mother?.status?.label)"
                                ></span>
                                <p class="mt-1" x-text="selected.updated_human ?? '-' "></p>
                            </div>
                        </div>

                        <div class="flex-1 overflow-hidden">
                            <div class="flex h-full flex-col">
                                <div class="flex-1 overflow-y-auto px-6 py-6" x-ref="messageContainer">
                                    <template x-if="(selected.messages ?? []).length === 0">
                                        <div class="flex h-full items-center justify-center text-sm text-gray-400">
                                            Belum ada pesan pada sesi ini.
                                        </div>
                                    </template>
                                    <div class="space-y-4">
                                        <template x-for="message in selected.messages" :key="message.id ?? Math.random()">
                                            <div :class="message.is_self ? 'flex justify-end' : 'flex justify-start'">
                                                <div
                                                    class="max-w-[70%] rounded-2xl px-4 py-3 text-sm shadow"
                                                    :class="message.is_self ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800'"
                                                >
                                                    <p class="whitespace-pre-line" x-text="message.text"></p>
                                                    <p class="mt-2 text-xs" :class="message.is_self ? 'text-blue-100/80' : 'text-gray-500'" x-text="message.humanize ?? ''"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div class="border-t border-gray-100 px-6 py-4">
                                    <template x-if="feedback">
                                        <div
                                            class="mb-3 rounded-lg px-3 py-2 text-xs"
                                            :class="feedback.type === 'error' ? 'bg-red-100 text-red-800' : feedback.type === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-800'"
                                            x-text="feedback.message"
                                        ></div>
                                    </template>
                                    <div class="flex items-end gap-3">
                                        <textarea
                                            x-model="messageText"
                                            rows="2"
                                            class="flex-1 resize-none rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Tulis pesan untuk ibu..."
                                        ></textarea>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                            @click="sendMessage()"
                                        >Kirim</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('js/pakar.js') ?>"></script>
<?= $this->endSection() ?>
