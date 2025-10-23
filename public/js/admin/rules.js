import {
  createSpinnerRow,
  escapeHtml,
  fetchJson,
  showNotification,
} from "./utils.js";

export const initAdminRules = () => {
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
                    <td colspan="4" class="border border-black/40 px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-300 dark:text-slate-400">Belum ada data rule.</td>
                </tr>
            `;
      return;
    }

    const rows = rules
      .map((rule) => {
        ruleStore.set(String(rule.id), rule);
        const badgeClass = rule.is_active
          ? "bg-green-100 text-green-800 dark:bg-emerald-500/20 dark:text-emerald-200"
          : "bg-gray-100 text-gray-600 dark:bg-slate-800/70 dark:text-slate-200";
        const badgeLabel = rule.is_active ? "Aktif" : "Tidak Aktif";

        return `
                <tr class="transition hover:bg-gray-50 dark:hover:bg-slate-900/60">
                    <td class="border border-black/40 px-4 py-3 font-medium text-gray-900 dark:text-slate-100 dark:border-gray-300">${escapeHtml(
                      rule.name
                    )}</td>
                    <td class="border border-black/40 px-4 py-3 text-gray-600 dark:text-slate-200 dark:border-gray-300">${escapeHtml(
                      rule.version
                    )}</td>
                    <td class="border border-black/40 px-4 py-3 dark:border-gray-300">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}">${badgeLabel}</span>
                    </td>
                    <td class="border border-black/40 px-4 py-3 text-right text-sm dark:text-slate-200 dark:border-gray-300">
                        <button type="button" class="mr-2 inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-black/70 dark:text-slate-300 dark:hover:bg-slate-900/50" data-action="edit" data-id="${escapeHtml(
                          rule.id
                        )}">Edit</button>
                        <button type="button" class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-rose-400/40 dark:text-rose-300 dark:hover:bg-rose-500/10" data-action="delete" data-id="${escapeHtml(
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
