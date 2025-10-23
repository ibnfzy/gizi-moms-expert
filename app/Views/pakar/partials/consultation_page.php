<?php
    $selectedId = isset($selectedConsultation['id']) ? (int) $selectedConsultation['id'] : null;
    if ($selectedId === null && $consultations !== []) {
        $selectedId = (int) ($consultations[0]['id'] ?? 0) ?: null;
        if ($selectedId !== null) {
            $selectedConsultation = $consultations[0];
        }
    }

    $feedback    = $feedback ?? null;
    $messageText = $messageText ?? '';

    $statusBadge = static function (?string $code): string {
        return match ($code) {
            'high'     => 'bg-rose-100 text-rose-700',
            'moderate' => 'bg-amber-100 text-amber-700',
            default    => 'bg-emerald-100 text-emerald-700',
        };
    };

    $statusLabel = static function (?string $label): string {
        return esc($label ?? 'Normal');
    };

    $truncate = static function (?string $text, int $limit = 80): string {
        if ($text === null) {
            return 'Belum ada pesan.';
        }

        $clean = trim($text);
        if ($clean === '') {
            return 'Belum ada pesan.';
        }

        if (mb_strlen($clean) <= $limit) {
            return esc($clean);
        }

        $truncated = mb_substr($clean, 0, $limit - 1) . '…';

        return esc($truncated);
    };
?>
<div class="relative">
    <div
        id="consultation-indicator"
        data-consultation-indicator
        class="absolute inset-0 z-10 hidden flex flex-col gap-6 rounded-3xl border border-blue-100 bg-white/90 p-6 text-sm text-blue-700 shadow-lg backdrop-blur-sm"
    >
        <div class="flex items-center gap-3 text-blue-600">
            <span class="inline-flex h-6 w-6 items-center justify-center">
                <span class="h-6 w-6 animate-spin rounded-full border-2 border-blue-500 border-t-transparent"></span>
            </span>
            <p class="text-sm font-semibold">Memuat sesi konsultasi...</p>
        </div>
        <div class="flex flex-1 flex-col gap-6 overflow-hidden lg:flex-row">
            <div class="hidden w-full flex-shrink-0 flex-col gap-4 lg:flex lg:w-80">
                <div class="h-5 w-1/2 rounded bg-blue-100/80 animate-pulse"></div>
                <div class="space-y-3">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <div class="space-y-2 rounded-2xl bg-blue-50/80 p-4 shadow-sm">
                            <div class="h-4 w-2/3 rounded bg-blue-100 animate-pulse"></div>
                            <div class="h-3 w-1/3 rounded bg-blue-100/80 animate-pulse"></div>
                            <div class="h-[0.35rem] w-full rounded bg-blue-100/60 animate-pulse"></div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="flex w-full flex-1 flex-col gap-4">
                <div class="h-6 w-40 rounded bg-blue-100 animate-pulse"></div>
                <div class="space-y-3 rounded-2xl bg-blue-50/80 p-5 shadow-sm">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <div class="flex justify-start">
                            <div class="h-16 w-2/3 rounded-2xl bg-blue-100/80 animate-pulse"></div>
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="rounded-2xl bg-blue-50/80 p-4 shadow-sm">
                    <div class="h-4 w-1/4 rounded bg-blue-100/80 animate-pulse"></div>
                    <div class="mt-3 h-14 rounded-xl bg-blue-100/70 animate-pulse"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex min-h-[70vh] flex-col gap-6 lg:flex-row">
        <aside class="w-full rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 lg:w-80">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Sesi Konsultasi</h2>
                <p class="text-sm text-gray-500">Pilih sesi untuk melihat percakapan.</p>
            </div>
            <div class="max-h-[28rem] overflow-y-auto">
            <?php if ($consultations === []): ?>
                <div class="px-5 py-6 text-sm text-gray-500">
                    Belum ada sesi konsultasi yang terdaftar.
                </div>
            <?php else: ?>
                <ul class="divide-y divide-gray-100" role="list">
                    <?php foreach ($consultations as $item): ?>
                        <?php
                            $itemId      = (int) ($item['id'] ?? 0);
                            $isActive    = $selectedId !== null && $itemId === $selectedId;
                            $buttonClass = $isActive
                                ? 'bg-blue-50/80'
                                : 'hover:bg-gray-50';
                        ?>
                        <li>
                            <button
                                type="button"
                                class="flex w-full flex-col gap-2 px-5 py-4 text-left transition <?= $buttonClass ?>"
                                data-consultation-url="<?= site_url('pakar/consultations') ?>/<?= $itemId ?>"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900"><?= esc($item['mother']['name'] ?? 'Ibu Menyusui') ?></p>
                                        <p class="text-xs text-gray-500"><?= esc($item['updated_human'] ?? '-') ?></p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold <?= $statusBadge($item['mother']['status']['code'] ?? null) ?>">
                                        <?= $statusLabel($item['mother']['status']['label'] ?? null) ?>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600">
                                    <?= $truncate($item['last_message']['text'] ?? null) ?>
                                </p>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        </aside>

        <section class="flex-1 rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <?php if ($selectedId === null || empty($selectedConsultation)): ?>
                <div class="flex h-full flex-col items-center justify-center gap-3 p-8 text-center text-gray-500">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-500">
                        <span class="text-2xl font-semibold">💬</span>
                    </div>
                <p class="text-base font-semibold text-gray-700">Pilih sesi konsultasi</p>
                <p class="text-sm">Mulai percakapan dengan memilih salah satu ibu dari daftar di sebelah kiri.</p>
            </div>
        <?php else: ?>
            <div class="flex h-full flex-col">
                <div class="flex items-start justify-between border-b border-gray-100 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900"><?= esc($selectedConsultation['mother']['name'] ?? 'Ibu Menyusui') ?></h3>
                        <p class="text-sm text-gray-500"><?= esc($selectedConsultation['mother']['email'] ?? 'Email tidak tersedia') ?></p>
                    </div>
                    <div class="text-right text-sm text-gray-500">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= $statusBadge($selectedConsultation['mother']['status']['code'] ?? null) ?>">
                            <?= $statusLabel($selectedConsultation['mother']['status']['label'] ?? null) ?>
                        </span>
                        <p class="mt-1"><?= esc($selectedConsultation['updated_human'] ?? '-') ?></p>
                    </div>
                </div>
                <div class="flex-1 overflow-hidden">
                    <div class="flex h-full flex-col">
                        <div class="flex-1 overflow-y-auto px-6 py-6">
                            <?php if (($selectedConsultation['messages'] ?? []) === []): ?>
                                <div class="flex h-full items-center justify-center text-sm text-gray-400">
                                    Belum ada pesan pada sesi ini.
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($selectedConsultation['messages'] as $message): ?>
                                        <?php
                                            $isSelf       = ! empty($message['is_self']);
                                            $messageClass = $isSelf
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-gray-100 text-gray-800';
                                            $timeClass    = $isSelf
                                                ? 'text-blue-100/80'
                                                : 'text-gray-500';
                                        ?>
                                        <div class="flex <?= $isSelf ? 'justify-end' : 'justify-start' ?>">
                                            <div class="max-w-[70%] rounded-2xl px-4 py-3 text-sm shadow <?= $messageClass ?>">
                                                <p class="whitespace-pre-line"><?= esc($message['text'] ?? '') ?></p>
                                                <p class="mt-2 text-xs <?= $timeClass ?>"><?= esc($message['humanize'] ?? '') ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="border-t border-gray-100 px-6 py-4">
                            <?php if ($feedback): ?>
                                <?php
                                    $feedbackClass = match ($feedback['type'] ?? '') {
                                        'error'   => 'bg-red-100 text-red-800',
                                        'warning' => 'bg-amber-100 text-amber-700',
                                        default   => 'bg-green-100 text-green-800',
                                    };
                                ?>
                                <div class="mb-3 rounded-lg px-3 py-2 text-xs <?= $feedbackClass ?>">
                                    <?= esc($feedback['message'] ?? '') ?>
                                </div>
                            <?php endif; ?>
                            <form
                                data-consultation-form
                                data-submit-url="<?= site_url('pakar/consultations') ?>/<?= $selectedId ?>/messages"
                                class="flex items-end gap-3"
                            >
                                <?= csrf_field() ?>
                                <textarea
                                    name="text"
                                    rows="2"
                                    class="flex-1 resize-none rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Tulis pesan untuk ibu..."
                                ><?= esc($messageText) ?></textarea>
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                >Kirim</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>
