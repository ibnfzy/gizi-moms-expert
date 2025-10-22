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

const showNotification = (target, type, message, timeout = 4000) => {
    const element = typeof target === 'string' ? document.getElementById(target) : target;
    if (!element) {
        return;
    }

    if (!message) {
        element.classList.add('hidden');
        element.textContent = '';
        return;
    }

    const baseClass = 'rounded-lg border px-4 py-3 text-sm font-medium shadow-sm transition-all duration-200';
    const variantClass = type === 'success'
        ? 'bg-green-100 text-green-800 border-green-200'
        : 'bg-red-100 text-red-800 border-red-200';

    element.className = `${baseClass} ${variantClass}`;
    element.textContent = message;
    element.classList.remove('hidden');

    if (timeout) {
        window.clearTimeout(element.__hideTimer);
        element.__hideTimer = window.setTimeout(() => {
            element.classList.add('hidden');
        }, timeout);
    }
};

const fetchJson = async (url, options = {}) => {
    const config = {
        headers: {
            Accept: 'application/json',
            ...(options.headers || {}),
        },
        ...options,
    };

    const response = await fetch(url, config);
    const contentType = response.headers.get('content-type') || '';
    let payload = null;

    if (contentType.includes('application/json')) {
        payload = await response.json();
    } else {
        const text = await response.text();
        try {
            payload = JSON.parse(text);
        } catch (error) {
            payload = { message: text };
        }
    }

    if (!response.ok || (payload && payload.status === false)) {
        const errorMessage = payload?.message || 'Permintaan gagal diproses.';
        throw new Error(errorMessage);
    }

    return payload ?? {};
};

const createSpinnerRow = (colspan, message) => `
    <tr>
        <td colspan="${colspan}" class="px-6 py-8">
            <div class="flex items-center justify-center gap-3 text-sm text-gray-500">
                <div class="h-6 w-6 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
                ${escapeHtml(message)}
            </div>
        </td>
    </tr>
`;

const initAdminDashboard = () => {
    const container = document.querySelector('[data-admin-dashboard]');
    if (!container) {
        return;
    }

    const statsEndpoint = container.dataset.statsEndpoint;
    const rulesEndpoint = container.dataset.rulesEndpoint;
    const notificationId = container.dataset.notificationId;
    const statsGrid = container.querySelector('[data-stats-grid]');
    const statsLoader = container.querySelector('[data-stats-loader]');
    const rulesBody = container.querySelector('[data-rules-body]');
    const refreshButton = container.querySelector('[data-refresh-rules]');

    const renderStats = (items) => {
        statsGrid.innerHTML = '';

        if (!Array.isArray(items) || items.length === 0) {
            const emptyCard = document.createElement('div');
            emptyCard.className = 'col-span-full rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-10 text-center text-sm text-gray-500';
            emptyCard.textContent = 'Belum ada data statistik yang dapat ditampilkan.';
            statsGrid.appendChild(emptyCard);
            return;
        }

        items.forEach((item) => {
            const card = document.createElement('div');
            card.className = 'relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100';

            const accent = document.createElement('div');
            accent.className = 'absolute inset-x-0 top-0 h-1';
            accent.classList.add(item.accent || 'bg-blue-500');
            card.appendChild(accent);

            const content = document.createElement('div');
            content.className = 'p-6';
            content.innerHTML = `
                <p class="text-sm font-semibold text-gray-500">${escapeHtml(item.title ?? 'Statistik')}</p>
                <div class="mt-3 flex items-end justify-between">
                    <h2 class="text-3xl font-bold text-gray-900">${escapeHtml(item.value ?? '-')}</h2>
                    <span class="text-xs text-gray-400">${escapeHtml(item.subtitle ?? '')}</span>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-gray-600">${escapeHtml(item.description ?? '')}</p>
            `;
            card.appendChild(content);

            statsGrid.appendChild(card);
        });
    };

    const renderRules = (rules) => {
        if (!Array.isArray(rules) || rules.length === 0) {
            rulesBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-500">
                        Belum ada rule yang dapat ditampilkan.
                    </td>
                </tr>
            `;
            return;
        }

        const rows = rules.slice(0, 5).map((rule) => {
            const badgeClass = (rule.status_badge) || (rule.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600');
            const badgeLabel = rule.status_label || (rule.is_active ? 'Aktif' : 'Tidak Aktif');
            return `
                <tr class="transition hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">${escapeHtml(rule.id ?? '-')}</td>
                    <td class="px-6 py-4 text-gray-700">${escapeHtml(rule.name ?? '-')}</td>
                    <td class="px-6 py-4 text-gray-600">${escapeHtml(rule.category ?? '-')}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}">${escapeHtml(badgeLabel)}</span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm text-gray-500">${escapeHtml(rule.updated_human ?? rule.updated_at ?? '-')}</td>
                </tr>
            `;
        }).join('');

        rulesBody.innerHTML = rows;
    };

    const loadStats = async () => {
        if (statsLoader) {
            if (!statsLoader.isConnected && statsGrid) {
                statsGrid.prepend(statsLoader);
            }
            statsLoader.classList.remove('hidden');
        }
        try {
            const payload = await fetchJson(statsEndpoint);
            const data = payload?.data ?? payload;
            renderStats(Array.isArray(data) ? data : (data?.items ?? []));
        } catch (error) {
            renderStats([]);
            showNotification(notificationId, 'error', error.message || 'Gagal memuat statistik.');
        } finally {
            if (statsLoader) {
                statsLoader.classList.add('hidden');
            }
        }
    };

    const loadRules = async () => {
        rulesBody.innerHTML = createSpinnerRow(5, 'Memuat data rule...');
        try {
            const payload = await fetchJson(rulesEndpoint);
            const data = payload?.data ?? payload;
            renderRules(Array.isArray(data) ? data : (data?.items ?? []));
        } catch (error) {
            rulesBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-6 text-center text-sm text-red-600">${escapeHtml(error.message || 'Gagal memuat data rule.')}</td>
                </tr>
            `;
            showNotification(notificationId, 'error', error.message || 'Gagal memuat data rule.');
        }
    };

    if (refreshButton) {
        refreshButton.addEventListener('click', () => {
            loadRules();
        });
    }

    loadStats();
    loadRules();
};

const initAdminRules = () => {
    const container = document.querySelector('[data-admin-rules]');
    if (!container) {
        return;
    }

    const rulesEndpoint = container.dataset.rulesEndpoint;
    const notificationId = container.dataset.notificationId;
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
    const jsonValidationMessage = document.getElementById('jsonValidationMessage');

    const ruleStore = new Map();
    let mode = 'create';

    const toggleModal = (show) => {
        if (!modal) {
            return;
        }
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

    const renderRules = (rules) => {
        ruleStore.clear();

        if (!Array.isArray(rules) || rules.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada data rule.</td>
                </tr>
            `;
            return;
        }

        const rows = rules.map((rule) => {
            ruleStore.set(String(rule.id), rule);
            const badgeClass = rule.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600';
            const badgeLabel = rule.is_active ? 'Aktif' : 'Tidak Aktif';

            return `
                <tr class="transition hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">${escapeHtml(rule.name)}</td>
                    <td class="px-4 py-3 text-gray-600">${escapeHtml(rule.version)}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}">${badgeLabel}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        <button type="button" class="mr-2 inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100" data-action="edit" data-id="${escapeHtml(rule.id)}">Edit</button>
                        <button type="button" class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50" data-action="delete" data-id="${escapeHtml(rule.id)}">Hapus</button>
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

                if (!rule) {
                    showNotification(notificationId, 'error', 'Data rule tidak ditemukan.');
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

                if (!rule) {
                    showNotification(notificationId, 'error', 'Data rule tidak ditemukan.');
                    return;
                }

                const confirmation = window.confirm(`Hapus rule "${rule.name}"?`);
                if (!confirmation) {
                    return;
                }

                try {
                    await fetchJson(`${rulesEndpoint}/${id}`, {
                        method: 'DELETE',
                    });
                    showNotification(notificationId, 'success', 'Rule berhasil dihapus.');
                    await loadRules();
                } catch (error) {
                    showNotification(notificationId, 'error', error.message || 'Terjadi kesalahan saat menghapus rule.');
                }
            });
        });
    };

    const loadRules = async () => {
        tableBody.innerHTML = createSpinnerRow(4, 'Memuat data rules...');
        try {
            const payload = await fetchJson(rulesEndpoint);
            const data = payload?.data ?? payload;
            renderRules(Array.isArray(data) ? data : (data?.items ?? []));
        } catch (error) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-sm text-red-600">${escapeHtml(error.message || 'Gagal memuat data rules.')}</td>
                </tr>
            `;
            showNotification(notificationId, 'error', error.message || 'Gagal memuat data rules.');
        }
    };

    if (addRuleButton) {
        addRuleButton.addEventListener('click', () => {
            mode = 'create';
            modalTitle.textContent = 'Tambah Rule';
            resetForm();
            toggleModal(true);
        });
    }

    if (cancelModalButton) {
        cancelModalButton.addEventListener('click', () => {
            toggleModal(false);
            resetForm();
            mode = 'create';
            modalTitle.textContent = 'Tambah Rule';
        });
    }

    if (closeModalButton) {
        closeModalButton.addEventListener('click', () => {
            toggleModal(false);
            resetForm();
            mode = 'create';
            modalTitle.textContent = 'Tambah Rule';
        });
    }

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            toggleModal(false);
            resetForm();
            mode = 'create';
            modalTitle.textContent = 'Tambah Rule';
        }
    });

    if (ruleJsonInput) {
        ruleJsonInput.addEventListener('input', () => {
            if (!ruleJsonInput.value) {
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
    }

    if (ruleForm) {
        ruleForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const name = ruleNameInput.value.trim();
            const version = ruleVersionInput.value.trim();
            const jsonRule = ruleJsonInput.value.trim();

            if (!name || !version || !jsonRule) {
                showNotification(notificationId, 'error', 'Mohon lengkapi seluruh data rule.');
                return;
            }

            try {
                JSON.parse(jsonRule);
                jsonValidationMessage.textContent = '';
            } catch (error) {
                jsonValidationMessage.textContent = 'Format JSON tidak valid.';
                showNotification(notificationId, 'error', 'Format JSON rule tidak valid.');
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
                },
                body: JSON.stringify(payload),
            };

            let endpoint = rulesEndpoint;
            if (mode === 'edit') {
                endpoint = `${rulesEndpoint}/${ruleIdInput.value}`;
            }

            try {
                await fetchJson(endpoint, requestOptions);
                showNotification(notificationId, 'success', 'Rule berhasil disimpan.');
                toggleModal(false);
                resetForm();
                mode = 'create';
                modalTitle.textContent = 'Tambah Rule';
                await loadRules();
            } catch (error) {
                showNotification(notificationId, 'error', error.message || 'Terjadi kesalahan saat menyimpan rule.');
            }
        });
    }

    loadRules();
};

document.addEventListener('DOMContentLoaded', () => {
    initAdminDashboard();
    initAdminRules();
});
