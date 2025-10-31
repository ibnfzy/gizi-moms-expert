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
  const cardContainer = container.querySelector("[data-rules-cards]");
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

  const setCardLoading = (message) => {
    if (!cardContainer) {
      return;
    }

    cardContainer.innerHTML = `
      <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-5 text-sm text-gray-500 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-black/70 dark:bg-slate-950/70 dark:text-slate-400 dark:shadow-black/30 dark:ring-black/60">
        <div class="flex items-center justify-center gap-3">
          <div class="h-6 w-6 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
          ${escapeHtml(message)}
        </div>
      </div>
    `;
  };

  const setCardMessage = (message, variant = "info") => {
    if (!cardContainer) {
      return;
    }

    const textClass =
      variant === "error"
        ? "text-red-600 dark:text-rose-300"
        : "text-gray-500 dark:text-slate-400";

    cardContainer.innerHTML = `
      <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-5 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-black/70 dark:bg-slate-950/70 dark:shadow-black/30 dark:ring-black/60">
        <p class="text-center text-sm ${textClass}">${escapeHtml(message)}</p>
      </div>
    `;
  };

  const renderRules = (rules) => {
    ruleStore.clear();

    if (!Array.isArray(rules) || rules.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="border border-black/40 px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-300 dark:text-slate-400">Belum ada data rule.</td>
                </tr>
            `;
      setCardMessage("Belum ada data rule.");
      showNotification(notificationId, "success", "");
      return;
    }

    const normalized = rules.map((rule) => {
      ruleStore.set(String(rule.id), rule);
      const statusBadgeClass = rule.is_active
        ? "bg-green-100 text-green-800 dark:bg-emerald-500/20 dark:text-emerald-200"
        : "bg-gray-100 text-gray-600 dark:bg-slate-800/70 dark:text-slate-200";
      const statusBadgeLabel = rule.is_active ? "Aktif" : "Tidak Aktif";
      const commentValue =
        typeof rule.komentar_pakar === "string" ? rule.komentar_pakar.trim() : "";
      const hasComment = commentValue.length > 0;
      const reviewBadgeClass = hasComment
        ? "bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200"
        : "bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200";
      const reviewBadgeLabel = hasComment ? "Butuh Tinjauan" : "Sudah Ditinjau";

      return {
        id: rule.id ?? "",
        name: rule.name ?? "-",
        version: rule.version ?? "-",
        statusBadgeClass,
        statusBadgeLabel,
        reviewBadgeClass,
        reviewBadgeLabel,
        comment: commentValue,
        hasComment,
        updatedAt: rule.updated_human ?? rule.updated_at ?? "-",
      };
    });

    tableBody.innerHTML = normalized
      .map((rule) => `
        <tr
          class="transition hover:bg-gray-50 dark:hover:bg-slate-900/60 ${
            rule.hasComment
              ? "bg-amber-50/70 dark:bg-amber-500/10"
              : ""
          }"
          data-has-comment="${rule.hasComment}">
          <td class="border border-black/40 px-4 py-3 font-medium text-gray-900 dark:text-slate-100 dark:border-gray-300">${escapeHtml(
            rule.name
          )}</td>
          <td class="border border-black/40 px-4 py-3 text-gray-600 dark:text-slate-200 dark:border-gray-300">${escapeHtml(
            rule.version
          )}</td>
          <td class="border border-black/40 px-4 py-3 dark:border-gray-300">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${escapeHtml(
              rule.statusBadgeClass
            )}">${escapeHtml(rule.statusBadgeLabel)}</span>
          </td>
          <td class="border border-black/40 px-4 py-3 align-top text-sm dark:border-gray-300">
            ${
              rule.hasComment
                ? `
                  <div class="space-y-2">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${escapeHtml(
                      rule.reviewBadgeClass
                    )}">${escapeHtml(rule.reviewBadgeLabel)}</span>
                    <p class="rounded-lg border border-amber-200 bg-amber-50/70 px-3 py-2 text-xs leading-relaxed text-amber-900 dark:border-amber-400/40 dark:bg-amber-500/10 dark:text-amber-100">
                      ${escapeHtml(rule.comment)}
                    </p>
                  </div>
                `
                : `
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${escapeHtml(
                    rule.reviewBadgeClass
                  )}">${escapeHtml(rule.reviewBadgeLabel)}</span>
                `
            }
          </td>
          <td class="border border-black/40 px-4 py-3 text-right text-sm dark:text-slate-200 dark:border-gray-300">
            <button type="button" class="mr-2 inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-slate/70 dark:text-slate-300 dark:hover:bg-slate-900/50" data-action="edit" data-id="${escapeHtml(
              String(rule.id)
            )}">Edit</button>
            <button type="button" class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-rose-400/40 dark:text-rose-300 dark:hover:bg-rose-500/10" data-action="delete" data-id="${escapeHtml(
              String(rule.id)
            )}">Hapus</button>
          </td>
        </tr>
      `)
      .join("");

    const commentCount = normalized.filter((rule) => rule.hasComment).length;

    if (commentCount > 0) {
      const message =
        commentCount === 1
          ? "1 rule membutuhkan tinjauan pakar."
          : `${commentCount} rule membutuhkan tinjauan pakar.`;
      showNotification(notificationId, "error", message);
    } else {
      showNotification(notificationId, "success", "");
    }

    if (cardContainer) {
      cardContainer.innerHTML = normalized
        .map((rule) => `
          <div
            class="rounded-2xl border border-slate-200/80 bg-white/80 p-5 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-black/70 dark:bg-slate-950/70 dark:shadow-black/30 dark:ring-black/60 ${
              rule.hasComment
                ? "border-amber-300 bg-amber-50/80 dark:border-amber-400/60 dark:bg-amber-500/10"
                : ""
            }"
            data-has-comment="${rule.hasComment}">
            <div class="text-base font-semibold text-gray-900 dark:text-slate-100">${escapeHtml(
              rule.name
            )}</div>
            <dl class="mt-4 space-y-3">
              <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Versi</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">${escapeHtml(rule.version)}</dd>
              </div>
              <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Status</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${escapeHtml(
                    rule.statusBadgeClass
                  )}">${escapeHtml(rule.statusBadgeLabel)}</span>
                </dd>
              </div>
              <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Diperbarui</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">${escapeHtml(rule.updatedAt)}</dd>
              </div>
              <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Catatan Pakar</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">
                  ${
                    rule.hasComment
                      ? `
                        <div class="space-y-2">
                          <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${escapeHtml(
                            rule.reviewBadgeClass
                          )}">${escapeHtml(rule.reviewBadgeLabel)}</span>
                          <p class="rounded-lg border border-amber-200 bg-amber-50/70 px-3 py-2 text-xs leading-relaxed text-amber-900 dark:border-amber-400/40 dark:bg-amber-500/10 dark:text-amber-100">
                            ${escapeHtml(rule.comment)}
                          </p>
                        </div>
                      `
                      : `
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${escapeHtml(
                          rule.reviewBadgeClass
                        )}">${escapeHtml(rule.reviewBadgeLabel)}</span>
                      `
                  }
                </dd>
              </div>
            </dl>
            <div class="mt-4 flex flex-wrap gap-3">
              <button type="button" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-slate/70 dark:text-slate-300 dark:hover:bg-slate-900/50" data-action="edit" data-id="${escapeHtml(
                String(rule.id)
              )}">Edit</button>
              <button type="button" class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-rose-400/40 dark:text-rose-300 dark:hover:bg-rose-500/10" data-action="delete" data-id="${escapeHtml(
                String(rule.id)
              )}">Hapus</button>
            </div>
          </div>
        `)
        .join("");
    }

    attachActionListeners();
  };

  const attachActionListeners = () => {
    [tableBody, cardContainer].forEach((root) => {
      if (!root) {
        return;
      }

      root.querySelectorAll('button[data-action="edit"]').forEach((button) => {
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

      root.querySelectorAll('button[data-action="delete"]').forEach((button) => {
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
    });
  };

  const loadRules = async () => {
    tableBody.innerHTML = createSpinnerRow(5, "Memuat data rules...");
    setCardLoading("Memuat data rules...");
    try {
      const payload = await fetchJson(rulesEndpoint);
      const data = payload?.data ?? payload;
      renderRules(Array.isArray(data) ? data : data?.items ?? []);
    } catch (error) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-sm text-red-600">${escapeHtml(
                      error.message || "Gagal memuat data rules."
                    )}</td>
                </tr>
            `;
      setCardMessage(error.message || "Gagal memuat data rules.", "error");
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
