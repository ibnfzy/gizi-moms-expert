<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="bg-white shadow-sm rounded-xl p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Manajemen Rules</h1>
                <p class="text-sm text-gray-500">Kelola rule basis pengetahuan secara terpusat.</p>
            </div>
            <button
                id="addRuleButton"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Tambah Rule
            </button>
        </div>

        <div id="alertWrapper" class="mt-4 hidden">
            <div id="alertBox" class="rounded-lg border px-4 py-3 text-sm"></div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th scope="col" class="px-4 py-3">Nama Rule</th>
                        <th scope="col" class="px-4 py-3">Versi</th>
                        <th scope="col" class="px-4 py-3">Status</th>
                        <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="rulesTableBody" class="divide-y divide-gray-100 text-gray-700">
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Memuat data rules...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div
    id="ruleModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4"
    aria-hidden="true"
>
    <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4">
            <div>
                <h2 id="modalTitle" class="text-lg font-semibold text-gray-900">Tambah Rule</h2>
                <p class="text-sm text-gray-500">Lengkapi formulir berikut untuk menyimpan rule.</p>
            </div>
            <button id="closeModalButton" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <span class="sr-only">Tutup</span>
                &times;
            </button>
        </div>

        <form id="ruleForm" class="px-6 py-6 space-y-5">
            <input type="hidden" id="ruleId" />
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-1">
                    <label for="ruleName" class="mb-1 block text-sm font-medium text-gray-700">Nama Rule</label>
                    <input
                        type="text"
                        id="ruleName"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        placeholder="Contoh: Kebutuhan Kalori"
                        required
                    />
                </div>
                <div class="md:col-span-1">
                    <label for="ruleVersion" class="mb-1 block text-sm font-medium text-gray-700">Versi</label>
                    <input
                        type="text"
                        id="ruleVersion"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        placeholder="Contoh: v1.0"
                        required
                    />
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between">
                    <label for="ruleJson" class="mb-1 block text-sm font-medium text-gray-700">JSON Rule</label>
                    <span id="jsonValidationMessage" class="text-xs text-red-500"></span>
                </div>
                <textarea
                    id="ruleJson"
                    class="block h-48 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder='Contoh: {"condition": "..."}'
                    required
                ></textarea>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <button
                    type="button"
                    id="cancelModalButton"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Simpan Rule
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (() => {
        const apiBaseUrl = '/api/rules';
        const tableBody = document.getElementById('rulesTableBody');
        const addRuleButton = document.getElementById('addRuleButton');
        const modal = document.getElementById('ruleModal');
        const modalTitle = document.getElementById('modalTitle');
        const ruleForm = document.getElementById('ruleForm');
        const ruleIdInput = document.getElementById('ruleId');
        const ruleNameInput = document.getElementById('ruleName');
        const ruleVersionInput = document.getElementById('ruleVersion');
        const ruleJsonInput = document.getElementById('ruleJson');
        const cancelModalButton = document.getElementById('cancelModalButton');
        const closeModalButton = document.getElementById('closeModalButton');
        const alertWrapper = document.getElementById('alertWrapper');
        const alertBox = document.getElementById('alertBox');
        const jsonValidationMessage = document.getElementById('jsonValidationMessage');

        let mode = 'create';
        const ruleStore = new Map();

        const escapeHtml = (value) => {
            if (value === undefined || value === null) {
                return '';
            }
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const toggleModal = (show) => {
            if (show) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        const resetForm = () => {
            ruleForm.reset();
            ruleIdInput.value = '';
            jsonValidationMessage.textContent = '';
        };

        const showAlert = (type, message) => {
            if (! message) {
                alertWrapper.classList.add('hidden');
                alertBox.textContent = '';
                alertBox.className = '';
                return;
            }

            const baseClass = 'rounded-lg border px-4 py-3 text-sm flex items-center gap-2';
            if (type === 'success') {
                alertBox.className = `${baseClass} border-green-200 bg-green-50 text-green-700`;
            } else {
                alertBox.className = `${baseClass} border-red-200 bg-red-50 text-red-700`;
            }

            alertBox.textContent = message;
            alertWrapper.classList.remove('hidden');
        };

        const statusBadge = (isActive) => {
            const active = Boolean(isActive);
            const baseClass = 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold';
            return active
                ? `<span class="${baseClass} bg-green-100 text-green-700">Aktif</span>`
                : `<span class="${baseClass} bg-gray-100 text-gray-600">Tidak Aktif</span>`;
        };

        const renderEmptyState = (message) => {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">${escapeHtml(message)}</td>
                </tr>
            `;
        };

        const renderRules = (rules) => {
            ruleStore.clear();

            if (! Array.isArray(rules) || rules.length === 0) {
                renderEmptyState('Belum ada data rule.');
                return;
            }

            const rows = rules.map((rule) => {
                ruleStore.set(String(rule.id), rule);

                return `
                    <tr class="transition hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">${escapeHtml(rule.name)}</td>
                        <td class="px-4 py-3 text-gray-600">${escapeHtml(rule.version)}</td>
                        <td class="px-4 py-3">${statusBadge(rule.is_active)}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <button
                                type="button"
                                data-action="edit"
                                data-id="${escapeHtml(rule.id)}"
                                class="mr-2 inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100"
                            >
                                Edit
                            </button>
                            <button
                                type="button"
                                data-action="delete"
                                data-id="${escapeHtml(rule.id)}"
                                class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            tableBody.innerHTML = rows;
            attachRowListeners();
        };

        const attachRowListeners = () => {
            tableBody.querySelectorAll('button[data-action="edit"]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-id');
                    const rule = ruleStore.get(String(id));

                    if (! rule) {
                        showAlert('error', 'Data rule tidak ditemukan.');
                        return;
                    }

                    mode = 'edit';
                    modalTitle.textContent = 'Edit Rule';
                    ruleIdInput.value = rule.id ?? '';
                    ruleNameInput.value = rule.name ?? '';
                    ruleVersionInput.value = rule.version ?? '';
                    ruleJsonInput.value = rule.json_rule ?? '';
                    toggleModal(true);
                });
            });

            tableBody.querySelectorAll('button[data-action="delete"]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const id = button.getAttribute('data-id');
                    const rule = ruleStore.get(String(id));

                    if (! rule) {
                        showAlert('error', 'Data rule tidak ditemukan.');
                        return;
                    }

                    const confirmation = confirm(`Hapus rule "${rule.name}"?`);
                    if (! confirmation) {
                        return;
                    }

                    try {
                        const response = await fetch(`${apiBaseUrl}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        const result = await response.json();

                        if (! response.ok || result.status !== true) {
                            throw new Error(result.message || 'Gagal menghapus rule.');
                        }

                        showAlert('success', result.message || 'Rule berhasil dihapus.');
                        await fetchRules();
                    } catch (error) {
                        showAlert('error', error.message || 'Terjadi kesalahan saat menghapus rule.');
                    }
                });
            });
        };

        const fetchRules = async () => {
            try {
                const response = await fetch(apiBaseUrl, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (! response.ok) {
                    throw new Error('Gagal memuat data rules.');
                }

                const result = await response.json();

                if (result.status !== true) {
                    throw new Error(result.message || 'Gagal memuat data rules.');
                }

                renderRules(result.data || []);
            } catch (error) {
                renderEmptyState(error.message || 'Tidak dapat memuat data rules.');
                showAlert('error', error.message || 'Tidak dapat memuat data rules.');
            }
        };

        ruleForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const name = ruleNameInput.value.trim();
            const version = ruleVersionInput.value.trim();
            const jsonRule = ruleJsonInput.value.trim();

            if (! name || ! version || ! jsonRule) {
                showAlert('error', 'Mohon lengkapi seluruh data rule.');
                return;
            }

            try {
                JSON.parse(jsonRule);
                jsonValidationMessage.textContent = '';
            } catch (error) {
                jsonValidationMessage.textContent = 'Format JSON tidak valid.';
                showAlert('error', 'Format JSON rule tidak valid.');
                return;
            }

            const payload = {
                name,
                version,
                json_rule: jsonRule,
            };

            const requestOptions = {
                method: mode === 'edit' ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            };

            let endpoint = apiBaseUrl;
            if (mode === 'edit') {
                const id = ruleIdInput.value;
                endpoint = `${apiBaseUrl}/${id}`;
            }

            try {
                const response = await fetch(endpoint, requestOptions);
                const result = await response.json();

                if (! response.ok || result.status !== true) {
                    throw new Error(result.message || 'Gagal menyimpan rule.');
                }

                showAlert('success', result.message || 'Rule berhasil disimpan.');
                toggleModal(false);
                resetForm();
                mode = 'create';
                modalTitle.textContent = 'Tambah Rule';
                await fetchRules();
            } catch (error) {
                showAlert('error', error.message || 'Terjadi kesalahan saat menyimpan rule.');
            }
        });

        ruleJsonInput.addEventListener('input', () => {
            if (! ruleJsonInput.value) {
                jsonValidationMessage.textContent = '';
                return;
            }

            try {
                JSON.parse(ruleJsonInput.value);
                jsonValidationMessage.textContent = '';
            } catch (error) {
                jsonValidationMessage.textContent = 'Format JSON tidak valid.';
            }
        });

        addRuleButton.addEventListener('click', () => {
            mode = 'create';
            modalTitle.textContent = 'Tambah Rule';
            resetForm();
            toggleModal(true);
        });

        cancelModalButton.addEventListener('click', () => {
            toggleModal(false);
            resetForm();
            mode = 'create';
            modalTitle.textContent = 'Tambah Rule';
        });

        closeModalButton.addEventListener('click', () => {
            toggleModal(false);
            resetForm();
            mode = 'create';
            modalTitle.textContent = 'Tambah Rule';
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && ! modal.classList.contains('hidden')) {
                toggleModal(false);
                resetForm();
                mode = 'create';
                modalTitle.textContent = 'Tambah Rule';
            }
        });

        fetchRules();
    })();
</script>
<?= $this->endSection() ?>
