const escapeHtml = (value) => {
  if (value === undefined || value === null) {
    return "";
  }
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
};

const showNotification = (target, type, message, timeout = 4000) => {
  const element =
    typeof target === "string" ? document.getElementById(target) : target;
  if (!element) {
    return;
  }

  if (!message) {
    element.classList.add("hidden");
    element.textContent = "";
    return;
  }

  const baseClass =
    "rounded-lg border px-4 py-3 text-sm font-medium shadow-sm transition-all duration-200";
  const variantClass =
    type === "success"
      ? "bg-green-100 text-green-800 border-green-200"
      : "bg-red-100 text-red-800 border-red-200";

  element.className = `${baseClass} ${variantClass}`;
  element.textContent = message;
  element.classList.remove("hidden");

  if (timeout) {
    window.clearTimeout(element.__hideTimer);
    element.__hideTimer = window.setTimeout(() => {
      element.classList.add("hidden");
    }, timeout);
  }
};

const getAuthToken = () => {
  const token = window.appConfig?.authToken ?? null;
  if (token) {
    return token;
  }

  try {
    return window.localStorage ? window.localStorage.getItem("jwtToken") : null;
  } catch (error) {
    return null;
  }
};

const fetchJson = async (url, options = {}) => {
  const headers = {
    Accept: "application/json",
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
  const contentType = response.headers.get("content-type") || "";
  let payload = null;

  if (contentType.includes("application/json")) {
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
    const errorMessage = payload?.message || "Permintaan gagal diproses.";
    throw new Error(errorMessage);
  }

  return payload ?? {};
};

const createSpinnerRow = (colspan, message) => `
    <tr>
        <td colspan="${colspan}" class="border border-black px-6 py-8 dark:border-gray-300">
            <div class="flex items-center justify-center gap-3 text-sm text-gray-500 dark:text-slate-400">
                <div class="h-6 w-6 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
                ${escapeHtml(message)}
            </div>
        </td>
    </tr>
`;

const initAdminDashboard = () => {
  const container = document.querySelector("[data-admin-dashboard]");
  if (!container) {
    return;
  }

  const statsEndpoint = container.dataset.statsEndpoint;
  const rulesEndpoint = container.dataset.rulesEndpoint;
  const notificationId = container.dataset.notificationId;
  const statsGrid = container.querySelector("[data-stats-grid]");
  const statsLoader = container.querySelector("[data-stats-loader]");
  const rulesBody = container.querySelector("[data-rules-body]");
  const refreshButton = container.querySelector("[data-refresh-rules]");

  const renderStats = (items) => {
    statsGrid.innerHTML = "";

    if (!Array.isArray(items) || items.length === 0) {
      const emptyCard = document.createElement("div");
      emptyCard.className =
        "col-span-full rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-10 text-center text-sm text-gray-500";
      emptyCard.textContent =
        "Belum ada data statistik yang dapat ditampilkan.";
      statsGrid.appendChild(emptyCard);
      return;
    }

    items.forEach((item) => {
      const card = document.createElement("div");
      card.className =
        "relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100";

      const accent = document.createElement("div");
      accent.className = "absolute inset-x-0 top-0 h-1";
      accent.classList.add(item.accent || "bg-blue-500");
      card.appendChild(accent);

      const content = document.createElement("div");
      content.className = "p-6";
      content.innerHTML = `
                <p class="text-sm font-semibold text-gray-500">${escapeHtml(
                  item.title ?? "Statistik"
                )}</p>
                <div class="mt-3 flex items-end justify-between">
                    <h2 class="text-3xl font-bold text-gray-900">${escapeHtml(
                      item.value ?? "-"
                    )}</h2>
                    <span class="text-xs text-gray-400">${escapeHtml(
                      item.subtitle ?? ""
                    )}</span>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-gray-600">${escapeHtml(
                  item.description ?? ""
                )}</p>
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

    const rows = rules
      .slice(0, 5)
      .map((rule) => {
        const badgeClass =
          rule.status_badge ||
          (rule.is_active
            ? "bg-green-100 text-green-800"
            : "bg-gray-100 text-gray-600");
        const badgeLabel =
          rule.status_label || (rule.is_active ? "Aktif" : "Tidak Aktif");
        return `
                <tr class="transition hover:bg-gray-50">
                    <td class="border border-black px-6 py-4 font-medium text-gray-900 dark:border-gray-300">${escapeHtml(
                      rule.id ?? "-"
                    )}</td>
                    <td class="border border-black px-6 py-4 text-gray-700 dark:border-gray-300">${escapeHtml(
                      rule.name ?? "-"
                    )}</td>
                    <td class="border border-black px-6 py-4 dark:border-gray-300">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}">${escapeHtml(
          badgeLabel
        )}</span>
                    </td>
                    <td class="border border-black px-6 py-4 text-right text-sm text-gray-500 dark:border-gray-300">${escapeHtml(
                      rule.updated_human ?? rule.updated_at ?? "-"
                    )}</td>
                </tr>
            `;
      })
      .join("");

    rulesBody.innerHTML = rows;
  };

  const loadStats = async () => {
    if (statsLoader) {
      if (!statsLoader.isConnected && statsGrid) {
        statsGrid.prepend(statsLoader);
      }
      statsLoader.classList.remove("hidden");
    }
    try {
      const payload = await fetchJson(statsEndpoint);
      const data = payload?.data ?? payload;
      renderStats(Array.isArray(data) ? data : data?.items ?? []);
    } catch (error) {
      renderStats([]);
      showNotification(
        notificationId,
        "error",
        error.message || "Gagal memuat statistik."
      );
    } finally {
      if (statsLoader) {
        statsLoader.classList.add("hidden");
      }
    }
  };

  const loadRules = async () => {
    rulesBody.innerHTML = createSpinnerRow(5, "Memuat data rule...");
    try {
      const payload = await fetchJson(rulesEndpoint);
      const data = payload?.data ?? payload;
      renderRules(Array.isArray(data) ? data : data?.items ?? []);
    } catch (error) {
      rulesBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-6 text-center text-sm text-red-600">${escapeHtml(
                      error.message || "Gagal memuat data rule."
                    )}</td>
                </tr>
            `;
      showNotification(
        notificationId,
        "error",
        error.message || "Gagal memuat data rule."
      );
    }
  };

  if (refreshButton) {
    refreshButton.addEventListener("click", () => {
      loadRules();
    });
  }

  loadStats();
  loadRules();
};

const initAdminRules = () => {
  const container = document.querySelector("[data-admin-rules]");
  if (!container) {
    return;
  }

  const rulesEndpoint = container.dataset.rulesEndpoint;
  const notificationId = container.dataset.notificationId;
  const tableBody = document.getElementById("rulesTableBody");
  const addRuleButton = document.getElementById("addRuleButton");
  const modal = document.getElementById("ruleModal");
  const modalTitle = document.getElementById("modalTitle");
  const ruleForm = document.getElementById("ruleForm");
  const ruleIdInput = document.getElementById("ruleId");
  const ruleNameInput = document.getElementById("ruleName");
  const ruleVersionInput = document.getElementById("ruleVersion");
  const ruleConditionInput = document.getElementById("ruleCondition");
  const ruleRecommendationInput = document.getElementById("ruleRecommendation");
  const ruleCategoryInput = document.getElementById("ruleCategory");
  const ruleStatusInput = document.getElementById("ruleStatus");
  const cancelModalButton = document.getElementById("cancelModalButton");
  const closeModalButton = document.getElementById("closeModalButton");
  const ruleDetailsMessage = document.getElementById("ruleDetailsMessage");

  const ruleStore = new Map();
  let mode = "create";

  const toggleModal = (show) => {
    if (!modal) {
      return;
    }
    if (show) {
      modal.classList.remove("hidden");
      modal.classList.add("flex");
    } else {
      modal.classList.add("hidden");
      modal.classList.remove("flex");
    }
  };

  const resetForm = () => {
    ruleForm.reset();
    ruleIdInput.value = "";
    if (ruleConditionInput) ruleConditionInput.value = "";
    if (ruleRecommendationInput) ruleRecommendationInput.value = "";
    if (ruleCategoryInput) ruleCategoryInput.value = "";
    if (ruleStatusInput) ruleStatusInput.value = "";
    if (ruleDetailsMessage) ruleDetailsMessage.textContent = "";
  };

  const extractRuleDetails = (rule) => {
    if (!rule) {
      return {};
    }

    if (rule.details && typeof rule.details === "object") {
      return rule.details;
    }

    if (rule.json_rule) {
      try {
        const parsed = JSON.parse(rule.json_rule);
        if (parsed && typeof parsed === "object") {
          return parsed;
        }
      } catch (error) {
        console.error("Gagal mengurai detail rule:", error);
      }
    }

    return {};
  };

  const renderRules = (rules) => {
    ruleStore.clear();

    if (!Array.isArray(rules) || rules.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="border border-black px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-300 dark:text-slate-400">Belum ada data rule.</td>
                </tr>
            `;
      return;
    }

    const rows = rules
      .map((rule) => {
        ruleStore.set(String(rule.id), rule);
        const badgeClass = rule.is_active
          ? "bg-green-100 text-green-800"
          : "bg-gray-100 text-gray-600";
        const badgeLabel = rule.is_active ? "Aktif" : "Tidak Aktif";

        return `
                <tr class="transition hover:bg-gray-50">
                    <td class="border border-black px-4 py-3 font-medium text-gray-900 dark:border-gray-300">${escapeHtml(
                      rule.name
                    )}</td>
                    <td class="border border-black px-4 py-3 text-gray-600 dark:border-gray-300">${escapeHtml(
                      rule.version
                    )}</td>
                    <td class="border border-black px-4 py-3 dark:border-gray-300">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}">${badgeLabel}</span>
                    </td>
                    <td class="border border-black px-4 py-3 text-right text-sm dark:border-gray-300">
                        <button type="button" class="mr-2 inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100" data-action="edit" data-id="${escapeHtml(
                          rule.id
                        )}">Edit</button>
                        <button type="button" class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50" data-action="delete" data-id="${escapeHtml(
                          rule.id
                        )}">Hapus</button>
                    </td>
                </tr>
            `;
      })
      .join("");

    tableBody.innerHTML = rows;
    attachRowListeners();
  };

  const attachRowListeners = () => {
    tableBody
      .querySelectorAll('button[data-action="edit"]')
      .forEach((button) => {
        button.addEventListener("click", () => {
          const id = button.getAttribute("data-id");
          const rule = ruleStore.get(String(id));

          if (!rule) {
            showNotification(
              notificationId,
              "error",
              "Data rule tidak ditemukan."
            );
            return;
          }

          mode = "edit";
          modalTitle.textContent = "Edit Rule";
          ruleIdInput.value = rule.id ?? "";
          ruleNameInput.value = rule.name ?? "";
          ruleVersionInput.value = rule.version ?? "";
          const details = extractRuleDetails(rule);
          if (ruleConditionInput) {
            ruleConditionInput.value = details.condition ?? "";
          }
          if (ruleRecommendationInput) {
            ruleRecommendationInput.value = details.recommendation ?? "";
          }
          if (ruleCategoryInput) {
            ruleCategoryInput.value = details.category ?? "";
          }
          if (ruleStatusInput) {
            ruleStatusInput.value = details.status ?? "";
          }
          if (ruleDetailsMessage) {
            ruleDetailsMessage.textContent = "";
          }
          toggleModal(true);
        });
      });

    tableBody
      .querySelectorAll('button[data-action="delete"]')
      .forEach((button) => {
        button.addEventListener("click", async () => {
          const id = button.getAttribute("data-id");
          const rule = ruleStore.get(String(id));

          if (!rule) {
            showNotification(
              notificationId,
              "error",
              "Data rule tidak ditemukan."
            );
            return;
          }

          const confirmation = window.confirm(`Hapus rule "${rule.name}"?`);
          if (!confirmation) {
            return;
          }

          try {
            await fetchJson(`${rulesEndpoint}/${id}`, {
              method: "DELETE",
            });
            showNotification(
              notificationId,
              "success",
              "Rule berhasil dihapus."
            );
            await loadRules();
          } catch (error) {
            showNotification(
              notificationId,
              "error",
              error.message || "Terjadi kesalahan saat menghapus rule."
            );
          }
        });
      });
  };

  const loadRules = async () => {
    tableBody.innerHTML = createSpinnerRow(4, "Memuat data rules...");
    try {
      const payload = await fetchJson(rulesEndpoint);
      const data = payload?.data ?? payload;
      renderRules(Array.isArray(data) ? data : data?.items ?? []);
    } catch (error) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-sm text-red-600">${escapeHtml(
                      error.message || "Gagal memuat data rules."
                    )}</td>
                </tr>
            `;
      showNotification(
        notificationId,
        "error",
        error.message || "Gagal memuat data rules."
      );
    }
  };

  if (addRuleButton) {
    addRuleButton.addEventListener("click", () => {
      mode = "create";
      modalTitle.textContent = "Tambah Rule";
      resetForm();
      toggleModal(true);
    });
  }

  if (cancelModalButton) {
    cancelModalButton.addEventListener("click", () => {
      toggleModal(false);
      resetForm();
      mode = "create";
      modalTitle.textContent = "Tambah Rule";
    });
  }

  if (closeModalButton) {
    closeModalButton.addEventListener("click", () => {
      toggleModal(false);
      resetForm();
      mode = "create";
      modalTitle.textContent = "Tambah Rule";
    });
  }

  window.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !modal.classList.contains("hidden")) {
      toggleModal(false);
      resetForm();
      mode = "create";
      modalTitle.textContent = "Tambah Rule";
    }
  });

  if (ruleForm) {
    ruleForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      const name = ruleNameInput.value.trim();
      const version = ruleVersionInput.value.trim();
      const condition = ruleConditionInput?.value.trim() ?? "";
      const recommendation = ruleRecommendationInput?.value.trim() ?? "";
      const category = ruleCategoryInput?.value.trim() ?? "";
      const status = ruleStatusInput?.value.trim() ?? "";

      if (ruleDetailsMessage) {
        ruleDetailsMessage.textContent = "";
      }

      if (!name || !version || !condition || !recommendation) {
        showNotification(
          notificationId,
          "error",
          "Mohon lengkapi seluruh data rule."
        );
        if (ruleDetailsMessage) {
          ruleDetailsMessage.textContent =
            "Isikan kondisi dan rekomendasi untuk rule.";
        }
        return;
      }

      const payload = {
        name,
        version,
        condition,
        recommendation,
      };

      if (category) {
        payload.category = category;
      }

      if (status) {
        payload.status = status;
      }

      const requestOptions = {
        method: mode === "edit" ? "PUT" : "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      };

      let endpoint = rulesEndpoint;
      if (mode === "edit") {
        endpoint = `${rulesEndpoint}/${ruleIdInput.value}`;
      }

      try {
        await fetchJson(endpoint, requestOptions);
        showNotification(notificationId, "success", "Rule berhasil disimpan.");
        toggleModal(false);
        resetForm();
        mode = "create";
        modalTitle.textContent = "Tambah Rule";
        await loadRules();
      } catch (error) {
        showNotification(
          notificationId,
          "error",
          error.message || "Terjadi kesalahan saat menyimpan rule."
        );
      }
    });
  }

  loadRules();
};

const initAdminMothers = () => {
  const container = document.querySelector("[data-admin-mothers]");
  if (!container) {
    return;
  }

  const baseEndpoint = container.dataset.baseEndpoint;
  const notificationId = container.dataset.notificationId;
  const tableBody = container.querySelector("[data-table-body]");
  const detailModal = document.getElementById("motherDetailModal");
  const emailModal = document.getElementById("motherEmailModal");
  const passwordModal = document.getElementById("motherPasswordModal");
  const emailForm = document.getElementById("motherEmailForm");
  const passwordForm = document.getElementById("motherPasswordForm");
  const emailInput = document.getElementById("motherEmailInput");
  const passwordInput = document.getElementById("motherPasswordInput");

  const detailElements = {
    name: detailModal?.querySelector("[data-detail-name]") || null,
    email: detailModal?.querySelector("[data-detail-email]") || null,
    status: detailModal?.querySelector("[data-detail-status]") || null,
    bb: detailModal?.querySelector("[data-detail-bb]") || null,
    tb: detailModal?.querySelector("[data-detail-tb]") || null,
    umur: detailModal?.querySelector("[data-detail-umur]") || null,
    usiaBayi: detailModal?.querySelector("[data-detail-usia-bayi]") || null,
    laktasi: detailModal?.querySelector("[data-detail-laktasi]") || null,
    aktivitas: detailModal?.querySelector("[data-detail-aktivitas]") || null,
    alergi: detailModal?.querySelector("[data-detail-alergi]") || null,
    preferensi: detailModal?.querySelector("[data-detail-preferensi]") || null,
    riwayat: detailModal?.querySelector("[data-detail-riwayat]") || null,
    inference: detailModal?.querySelector("[data-detail-inference]") || null,
  };

  const motherStore = new Map();
  let activeMotherId = null;

  const toggleModal = (modal, show) => {
    if (!modal) {
      return;
    }

    if (show) {
      modal.classList.remove("hidden");
      modal.classList.add("flex");
    } else {
      modal.classList.add("hidden");
      modal.classList.remove("flex");
    }
  };

  const closeDetailModal = () => {
    toggleModal(detailModal, false);
  };

  const closeEmailModal = () => {
    toggleModal(emailModal, false);
    if (emailForm) {
      delete emailForm.dataset.motherId;
    }
    activeMotherId = null;
  };

  const closePasswordModal = () => {
    toggleModal(passwordModal, false);
    if (passwordForm) {
      delete passwordForm.dataset.motherId;
    }
    if (passwordInput) {
      passwordInput.value = "";
    }
    activeMotherId = null;
  };

  const updateList = (element, items) => {
    if (!element) {
      return;
    }

    if (!Array.isArray(items) || items.length === 0) {
      element.innerHTML = '<li class="text-gray-400">Tidak ada data</li>';
      return;
    }

    element.innerHTML = items
      .map((item) => `<li>${escapeHtml(item)}</li>`)
      .join("");
  };

  const formatValue = (value, suffix = "") => {
    if (value === undefined || value === null || value === "") {
      return "-";
    }

    const text =
      typeof value === "number" && !Number.isNaN(value) ? value : String(value);
    return suffix ? `${text}${suffix}` : text;
  };

  const renderRows = (items) => {
    motherStore.clear();

    if (!Array.isArray(items) || items.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="border border-black px-6 py-6 text-center text-sm text-gray-500 dark:border-gray-300 dark:text-slate-400">Belum ada data ibu.</td>
                </tr>
            `;
      return;
    }

    const rows = items
      .map((mother) => {
        if (mother?.id !== undefined) {
          motherStore.set(String(mother.id), mother);
        }

        const status = mother?.status || {};
        const badgeClass =
          typeof status.badge === "string" && status.badge.trim() !== ""
            ? status.badge
            : "bg-gray-100 text-gray-600";
        const badgeLabel = status.label || "Tidak diketahui";

        return `
                <tr class="transition hover:bg-gray-50">
                    <td class="border border-black px-6 py-4 font-medium text-gray-900 dark:border-gray-300">${escapeHtml(
                      mother?.name ?? "-"
                    )}</td>
                    <td class="border border-black px-6 py-4 text-gray-700 dark:border-gray-300">${escapeHtml(
                      mother?.email ?? "-"
                    )}</td>
                    <td class="border border-black px-6 py-4 text-gray-600 dark:border-gray-300">${escapeHtml(
                      formatValue(mother?.umur)
                    )}</td>
                    <td class="border border-black px-6 py-4 text-gray-600 dark:border-gray-300">${escapeHtml(
                      formatValue(mother?.usia_bayi_bln)
                    )}</td>
                    <td class="border border-black px-6 py-4 dark:border-gray-300">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}">${escapeHtml(
          badgeLabel
        )}</span>
                    </td>
                    <td class="border border-black px-6 py-4 text-right text-sm dark:border-gray-300">
                        <div class="flex flex-wrap justify-end gap-2">
                            <button type="button" class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50" data-action="detail" data-id="${escapeHtml(
                              mother?.id
                            )}">Detail</button>
                            <button type="button" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50" data-action="email" data-id="${escapeHtml(
                              mother?.id
                            )}">Edit Email</button>
                            <button type="button" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50" data-action="password" data-id="${escapeHtml(
                              mother?.id
                            )}">Atur Password</button>
                            <button type="button" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50" data-action="delete" data-id="${escapeHtml(
                              mother?.id
                            )}">Hapus</button>
                        </div>
                    </td>
                </tr>
            `;
      })
      .join("");

    tableBody.innerHTML = rows;
    attachRowHandlers();
  };

  const populateDetail = (data) => {
    if (!data || !detailModal) {
      return;
    }

    const profile = data.profile || {};
    const status = data.status || {};
    const latestInference = data.latest_inference || null;

    if (detailElements.name) {
      detailElements.name.textContent = data.name ?? "-";
    }
    if (detailElements.email) {
      detailElements.email.textContent = data.email ?? "-";
    }
    if (detailElements.status) {
      detailElements.status.textContent = status.label || "Tidak diketahui";
      const badgeClass =
        typeof status.badge === "string" && status.badge.trim() !== ""
          ? status.badge
          : "bg-gray-100 text-gray-600";
      detailElements.status.className = `inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}`;
    }
    if (detailElements.bb) {
      detailElements.bb.textContent = formatValue(profile.bb, " kg");
    }
    if (detailElements.tb) {
      detailElements.tb.textContent = formatValue(profile.tb, " cm");
    }
    if (detailElements.umur) {
      detailElements.umur.textContent = formatValue(profile.umur, " th");
    }
    if (detailElements.usiaBayi) {
      detailElements.usiaBayi.textContent = formatValue(
        profile.usia_bayi_bln,
        " bln"
      );
    }
    if (detailElements.laktasi) {
      detailElements.laktasi.textContent =
        typeof profile.laktasi_tipe === "string" && profile.laktasi_tipe !== ""
          ? profile.laktasi_tipe.toUpperCase()
          : "-";
    }
    if (detailElements.aktivitas) {
      detailElements.aktivitas.textContent =
        typeof profile.aktivitas === "string" && profile.aktivitas !== ""
          ? profile.aktivitas.toUpperCase()
          : "-";
    }

    updateList(detailElements.alergi, profile.alergi || []);
    updateList(detailElements.preferensi, profile.preferensi || []);
    updateList(detailElements.riwayat, profile.riwayat || []);

    if (detailElements.inference) {
      if (!latestInference) {
        detailElements.inference.textContent = "Belum ada riwayat inferensi.";
      } else {
        const timestamp =
          latestInference.created_at_human || latestInference.created_at || "-";
        const statusLabel =
          (latestInference.status && latestInference.status.label) ||
          status.label ||
          "Tidak diketahui";
        detailElements.inference.textContent = `Status ${statusLabel} • Diperbarui ${timestamp}`;
      }
    }
  };

  const handleDetail = async (id) => {
    try {
      const payload = await fetchJson(`${baseEndpoint}/${id}`);
      const data = payload?.data ?? payload;
      populateDetail(data);
      toggleModal(detailModal, true);
    } catch (error) {
      showNotification(
        notificationId,
        "error",
        error.message || "Gagal memuat detail ibu."
      );
    }
  };

  const openEmailModal = (id) => {
    const mother = motherStore.get(String(id));
    if (!mother) {
      showNotification(notificationId, "error", "Data ibu tidak ditemukan.");
      return;
    }

    activeMotherId = String(id);
    if (emailInput) {
      emailInput.value = mother.email ?? "";
    }
    if (emailForm) {
      emailForm.dataset.motherId = String(id);
    }
    toggleModal(emailModal, true);
  };

  const openPasswordModal = (id) => {
    const mother = motherStore.get(String(id));
    if (!mother) {
      showNotification(notificationId, "error", "Data ibu tidak ditemukan.");
      return;
    }

    activeMotherId = String(id);
    if (passwordInput) {
      passwordInput.value = "";
    }
    if (passwordForm) {
      passwordForm.dataset.motherId = String(id);
    }
    toggleModal(passwordModal, true);
  };

  const handleDelete = async (id) => {
    const mother = motherStore.get(String(id));
    if (!mother) {
      showNotification(notificationId, "error", "Data ibu tidak ditemukan.");
      return;
    }

    const confirmation = window.confirm(
      `Hapus data ibu "${mother.name ?? "Tanpa Nama"}"?`
    );
    if (!confirmation) {
      return;
    }

    try {
      await fetchJson(`${baseEndpoint}/${id}`, { method: "DELETE" });
      showNotification(notificationId, "success", "Data ibu berhasil dihapus.");
      await loadMothers();
    } catch (error) {
      showNotification(
        notificationId,
        "error",
        error.message || "Gagal menghapus data ibu."
      );
    }
  };

  const attachRowHandlers = () => {
    tableBody.querySelectorAll("button[data-action]").forEach((button) => {
      button.addEventListener("click", (event) => {
        const action = button.getAttribute("data-action");
        const id = button.getAttribute("data-id");

        if (!id) {
          showNotification(notificationId, "error", "ID ibu tidak valid.");
          return;
        }

        if (action === "detail") {
          handleDetail(id);
        } else if (action === "email") {
          openEmailModal(id);
        } else if (action === "password") {
          openPasswordModal(id);
        } else if (action === "delete") {
          handleDelete(id);
        }
      });
    });
  };

  const loadMothers = async () => {
    tableBody.innerHTML = createSpinnerRow(6, "Memuat data ibu...");
    try {
      const payload = await fetchJson(baseEndpoint);
      const data = payload?.data ?? payload;
      renderRows(Array.isArray(data) ? data : data?.items ?? []);
    } catch (error) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-6 text-center text-sm text-red-600">${escapeHtml(
                      error.message || "Gagal memuat data ibu."
                    )}</td>
                </tr>
            `;
      showNotification(
        notificationId,
        "error",
        error.message || "Gagal memuat data ibu."
      );
    }
  };

  detailModal?.querySelectorAll("[data-close-detail]").forEach((button) => {
    button.addEventListener("click", closeDetailModal);
  });

  emailModal?.querySelectorAll("[data-close-email]").forEach((button) => {
    button.addEventListener("click", closeEmailModal);
  });

  passwordModal?.querySelectorAll("[data-close-password]").forEach((button) => {
    button.addEventListener("click", closePasswordModal);
  });

  [detailModal, emailModal, passwordModal].forEach((modal) => {
    if (!modal) {
      return;
    }
    modal.addEventListener("click", (event) => {
      if (event.target === modal) {
        if (modal === detailModal) {
          closeDetailModal();
        } else if (modal === emailModal) {
          closeEmailModal();
        } else if (modal === passwordModal) {
          closePasswordModal();
        }
      }
    });
  });

  window.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      [detailModal, emailModal, passwordModal].forEach((modal) => {
        if (modal && modal.classList.contains("flex")) {
          if (modal === detailModal) {
            closeDetailModal();
          } else if (modal === emailModal) {
            closeEmailModal();
          } else if (modal === passwordModal) {
            closePasswordModal();
          }
        }
      });
    }
  });

  if (emailForm) {
    emailForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      const id = emailForm.dataset.motherId || activeMotherId;
      const email = emailInput?.value.trim();

      if (!id) {
        showNotification(notificationId, "error", "ID ibu tidak valid.");
        return;
      }

      if (!email) {
        showNotification(notificationId, "error", "Email tidak boleh kosong.");
        return;
      }

      try {
        await fetchJson(`${baseEndpoint}/${id}/email`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ email }),
        });
        closeEmailModal();
        showNotification(
          notificationId,
          "success",
          "Email ibu berhasil diperbarui."
        );
        await loadMothers();
      } catch (error) {
        showNotification(
          notificationId,
          "error",
          error.message || "Gagal memperbarui email ibu."
        );
      }
    });
  }

  if (passwordForm) {
    passwordForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      const id = passwordForm.dataset.motherId || activeMotherId;
      const password = passwordInput?.value.trim();

      if (!id) {
        showNotification(notificationId, "error", "ID ibu tidak valid.");
        return;
      }

      if (!password || password.length < 8) {
        showNotification(
          notificationId,
          "error",
          "Password minimal 8 karakter."
        );
        return;
      }

      try {
        await fetchJson(`${baseEndpoint}/${id}/password`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ password }),
        });
        closePasswordModal();
        showNotification(
          notificationId,
          "success",
          "Password ibu berhasil diperbarui."
        );
      } catch (error) {
        showNotification(
          notificationId,
          "error",
          error.message || "Gagal memperbarui password ibu."
        );
        return;
      }

      await loadMothers();
    });
  }

  loadMothers();
};

document.addEventListener("DOMContentLoaded", () => {
  initAdminDashboard();
  initAdminRules();
  initAdminMothers();
});
