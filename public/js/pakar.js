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

const getAuthToken = () => {
    const token = window.appConfig?.authToken ?? null;
    if (token) {
        return token;
    }

    try {
        return window.localStorage ? window.localStorage.getItem('jwtToken') : null;
    } catch (error) {
        return null;
    }
};

const fetchJson = async (url, options = {}) => {
    const headers = {
        Accept: 'application/json',
        ...(options.headers || {}),
    };

    const token = getAuthToken();
    if (token && !headers.Authorization) {
        headers.Authorization = `Bearer ${token}`;
    }

    const config = {
        ...options,
        headers,
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

const registerPakarComponents = (alpineInstance) => {
    if (!alpineInstance) {
        return;
    }

    alpineInstance.data('pakarDashboard', (config = {}) => ({
        cards: [
            {
                key: 'normal',
                label: 'Status Normal',
                description: 'Ibu dengan kondisi stabil dan kebutuhan gizi terpenuhi.',
                color: 'bg-emerald-500',
            },
            {
                key: 'moderate',
                label: 'Perlu Pemantauan',
                description: 'Perlu pemantauan berkala untuk menyesuaikan pola makan.',
                color: 'bg-amber-500',
            },
            {
                key: 'high',
                label: 'Prioritas Tinggi',
                description: 'Membutuhkan tindak lanjut segera dari pakar gizi.',
                color: 'bg-rose-500',
            },
        ],
        mothers: Array.isArray(config.initialMothers) ? config.initialMothers : [],
        summary: (() => {
            const baseSummary = { normal: 0, moderate: 0, high: 0 };
            if (config.initialSummary && typeof config.initialSummary === 'object') {
                return { ...baseSummary, ...config.initialSummary };
            }
            return baseSummary;
        })(),
        loading: !Array.isArray(config.initialMothers) || config.initialMothers.length === 0,
        detailOpen: false,
        detailLoading: false,
        selectedMother: null,
        init() {
            const dataset = this.$el.dataset;
            this.mothersEndpoint = dataset.mothersEndpoint || '/api/mothers';
            this.motherDetailEndpoint = dataset.motherDetailEndpoint || '/api/mothers';
            this.notificationId = dataset.notificationId;
            if (this.mothers.length > 0) {
                this.computeSummary();
                this.loading = false;
            } else {
                this.fetchMothers();
            }
        },
        async fetchMothers() {
            this.loading = true;
            try {
                const payload = await fetchJson(this.mothersEndpoint);
                const data = payload?.data ?? payload;
                this.mothers = Array.isArray(data) ? data : (data?.items ?? []);
                this.computeSummary();
            } catch (error) {
                this.mothers = [];
                showNotification(this.notificationId, 'error', error.message || 'Gagal memuat data ibu.');
            } finally {
                this.loading = false;
            }
        },
        computeSummary() {
            this.summary = { normal: 0, moderate: 0, high: 0 };
            this.mothers.forEach((mother) => {
                const code = mother?.status?.code ?? 'normal';
                if (Object.prototype.hasOwnProperty.call(this.summary, code)) {
                    this.summary[code] += 1;
                } else {
                    this.summary.normal += 1;
                }
            });
        },
        async openDetail(mother) {
            this.detailOpen = true;
            this.detailLoading = true;
            try {
                if (!mother?.id) {
                    this.selectedMother = mother;
                    return;
                }
                const base = (this.motherDetailEndpoint || '').replace(/\/$/, '');
                const payload = await fetchJson(`${base}/${mother.id}`);
                const data = payload?.data ?? payload;
                this.selectedMother = data && Object.keys(data).length ? data : mother;
            } catch (error) {
                this.selectedMother = mother;
                showNotification(this.notificationId, 'error', error.message || 'Gagal memuat detail ibu.');
            } finally {
                this.detailLoading = false;
            }
        },
        closeDetail() {
            this.detailOpen = false;
            this.detailLoading = false;
            this.selectedMother = null;
        },
        formatValue(value, suffix = '') {
            if (value === null || value === undefined || value === '') {
                return '-';
            }
            return `${value}${suffix}`;
        },
        listValue(values) {
            if (!Array.isArray(values) || values.length === 0) {
                return 'Tidak ada data';
            }
            return values.join(', ');
        },
        formatKey(key) {
            if (!key) {
                return '-';
            }
            return key.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
        },
        formatDisplay(value) {
            if (Array.isArray(value)) {
                return value.join(', ');
            }
            if (value === null || value === undefined || value === '') {
                return '-';
            }
            if (typeof value === 'object') {
                return Object.values(value).join(', ');
            }
            return value;
        },
    });

    alpineInstance.data('pakarConsultation', () => ({
        consultations: [],
        selectedId: null,
        messagesEndpoint: '/api/messages',
        notificationId: null,
        userRole: 'pakar',
        loadingList: true,
        loadingMessages: false,
        messageText: '',
        feedback: null,
        init() {
            const dataset = this.$el.dataset;
            this.messagesEndpoint = dataset.messagesEndpoint || '/api/messages';
            this.notificationId = dataset.notificationId;
            this.userRole = dataset.userRole || 'pakar';
            this.selectedId = dataset.selectedId || null;
            this.fetchConsultations().then(() => {
                if (this.selectedId) {
                    this.fetchMessages(this.selectedId);
                }
            });
        },
        get selected() {
            return this.consultations.find((item) => String(item.id) === String(this.selectedId)) || null;
        },
        async fetchConsultations() {
            this.loadingList = true;
            try {
                const payload = await fetchJson(this.messagesEndpoint);
                const data = payload?.data ?? payload;
                const items = Array.isArray(data)
                    ? data
                    : (data?.items ?? data?.consultations ?? []);
                this.consultations = Array.isArray(items) ? items : [];
                if (!this.selectedId && this.consultations.length > 0) {
                    this.selectedId = this.consultations[0].id;
                }
            } catch (error) {
                this.consultations = [];
                showNotification(this.notificationId, 'error', error.message || 'Gagal memuat daftar konsultasi.');
            } finally {
                this.loadingList = false;
            }
        },
        async fetchMessages(id) {
            if (!id) {
                return;
            }
            this.loadingMessages = true;
            try {
                const url = `${this.messagesEndpoint}?consultation_id=${encodeURIComponent(id)}`;
                const payload = await fetchJson(url);
                const data = payload?.data ?? payload;
                let conversation = null;
                if (Array.isArray(data)) {
                    conversation = { id, messages: data };
                } else if (data?.conversation) {
                    conversation = data.conversation;
                } else {
                    conversation = data;
                }
                if (conversation) {
                    conversation.id = conversation.id ?? id;
                    this.updateConsultation(id, conversation);
                }
            } catch (error) {
                showNotification(this.notificationId, 'error', error.message || 'Gagal memuat percakapan.');
            } finally {
                this.loadingMessages = false;
                this.scrollToBottom();
            }
        },
        updateConsultation(id, conversation) {
            const targetId = conversation?.id ?? id;
            const index = this.consultations.findIndex((item) => String(item.id) === String(targetId));
            const messages = conversation?.messages ?? [];
            if (index === -1) {
                this.consultations.push({ ...conversation, id: targetId, messages });
                return;
            }
            const existing = this.consultations[index];
            const merged = { ...existing, ...conversation, id: targetId };
            merged.messages = Array.isArray(messages) ? messages : (existing.messages ?? []);
            merged.last_message = conversation?.last_message ?? merged.messages?.slice(-1)[0] ?? existing.last_message;
            this.consultations.splice(index, 1, merged);
        },
        selectConsultation(id) {
            this.selectedId = id;
            this.feedback = null;
            this.fetchMessages(id);
        },
        statusBadge(code) {
            switch (code) {
                case 'high':
                    return 'bg-rose-100 text-rose-700';
                case 'moderate':
                    return 'bg-amber-100 text-amber-700';
                default:
                    return 'bg-emerald-100 text-emerald-700';
            }
        },
        statusLabel(label) {
            return label ?? 'Normal';
        },
        truncate(text, maxLength) {
            if (!text) {
                return '';
            }
            return text.length > maxLength ? `${text.slice(0, maxLength)}…` : text;
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messageContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },
        appendMessage(message) {
            if (!this.selected) {
                return;
            }
            this.selected.messages = this.selected.messages ?? [];
            this.selected.messages.push(message);
            this.selected.last_message = message;
            const index = this.consultations.findIndex((item) => String(item.id) === String(this.selectedId));
            if (index !== -1) {
                const updated = { ...this.consultations[index] };
                updated.last_message = message;
                updated.messages = this.selected.messages;
                this.consultations.splice(index, 1, updated);
            }
            this.scrollToBottom();
        },
        replaceMessage(tempId, actualMessage) {
            if (!this.selected || !Array.isArray(this.selected.messages)) {
                return;
            }
            const index = this.selected.messages.findIndex((message) => message.id === tempId);
            if (index !== -1) {
                this.selected.messages.splice(index, 1, actualMessage);
            }
            this.scrollToBottom();
        },
        async sendMessage() {
            if (!this.selectedId) {
                this.feedback = { type: 'warning', message: 'Pilih sesi konsultasi terlebih dahulu.' };
                return;
            }
            if (!this.messageText.trim()) {
                this.feedback = { type: 'warning', message: 'Isi pesan sebelum mengirim.' };
                return;
            }

            const text = this.messageText.trim();
            this.messageText = '';

            const tempMessage = {
                id: Date.now(),
                sender: this.userRole,
                text,
                created_at: new Date().toISOString(),
                humanize: 'Baru saja',
                is_self: true,
            };

            this.appendMessage(tempMessage);

            const headers = { 'Content-Type': 'application/json' };

            try {
                const payload = await fetchJson(this.messagesEndpoint, {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({
                        consultation_id: this.selectedId,
                        text,
                    }),
                });
                const data = payload?.data ?? payload;
                if (data) {
                    const syncedMessage = {
                        id: data.id ?? tempMessage.id,
                        sender: data.sender ?? this.userRole,
                        text: data.text ?? text,
                        created_at: data.created_at ?? tempMessage.created_at,
                        humanize: data.humanize ?? tempMessage.humanize,
                        is_self: true,
                    };
                    this.replaceMessage(tempMessage.id, syncedMessage);
                }
                this.feedback = { type: 'success', message: 'Pesan berhasil dikirim.' };
            } catch (error) {
                this.feedback = { type: 'error', message: error.message || 'Pesan gagal dikirim.' };
            }
        },
    }));
};

if (window.Alpine) {
    registerPakarComponents(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => {
        registerPakarComponents(window.Alpine);
    });
}
