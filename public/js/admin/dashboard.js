import {
  createSpinnerRow,
  escapeHtml,
  fetchJson,
  showNotification,
} from "./utils.js";

export const initAdminDashboard = () => {
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
  const rulesCards = container.querySelector("[data-rules-cards]");

  const renderStats = (items) => {
    statsGrid.innerHTML = "";

    if (!Array.isArray(items) || items.length === 0) {
      const emptyCard = document.createElement("div");
      emptyCard.className =
        "col-span-full rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-10 text-center text-sm text-gray-500 dark:border-black/70 dark:bg-slate-950/70 dark:text-slate-400";
      emptyCard.textContent =
        "Belum ada data statistik yang dapat ditampilkan.";
      statsGrid.appendChild(emptyCard);
      return;
    }

    items.forEach((item) => {
      const card = document.createElement("div");
      card.className =
        "relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 dark:bg-slate-950/70 dark:ring-black/60";

      const accent = document.createElement("div");
      accent.className = "absolute inset-x-0 top-0 h-1";
      accent.classList.add(item.accent || "bg-blue-500");
      card.appendChild(accent);

      const content = document.createElement("div");
      content.className = "p-6";
      content.innerHTML = `
                <p class="text-sm font-semibold text-gray-500 dark:text-slate-400">${escapeHtml(
                  item.title ?? "Statistik"
                )}</p>
                <div class="mt-3 flex items-end justify-between">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-slate-100">${escapeHtml(
                      item.value ?? "-"
                    )}</h2>
                    <span class="text-xs text-gray-400 dark:text-slate-500">${escapeHtml(
                      item.subtitle ?? ""
                    )}</span>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-gray-600 dark:text-slate-400">${escapeHtml(
                  item.description ?? ""
                )}</p>
            `;
      card.appendChild(content);

      statsGrid.appendChild(card);
    });
  };

  const setRulesCardLoading = (message) => {
    if (!rulesCards) {
      return;
    }

    rulesCards.innerHTML = `
      <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-5 text-sm text-gray-500 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-black/70 dark:bg-slate-950/70 dark:text-slate-400 dark:shadow-black/30 dark:ring-black/60">
        <div class="flex items-center justify-center gap-3">
          <div class="h-6 w-6 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
          ${escapeHtml(message)}
        </div>
      </div>
    `;
  };

  const setRulesCardMessage = (message, variant = "info") => {
    if (!rulesCards) {
      return;
    }

    const textClass =
      variant === "error"
        ? "text-red-600 dark:text-rose-300"
        : "text-gray-500 dark:text-slate-400";

    rulesCards.innerHTML = `
      <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-5 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-black/70 dark:bg-slate-950/70 dark:shadow-black/30 dark:ring-black/60">
        <p class="text-center text-sm ${textClass}">${escapeHtml(message)}</p>
      </div>
    `;
  };

  const renderRules = (rules) => {
    if (!Array.isArray(rules) || rules.length === 0) {
      rulesBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-500 dark:text-slate-400">
                        Belum ada rule yang dapat ditampilkan.
                    </td>
                </tr>
            `;
      setRulesCardMessage("Belum ada rule yang dapat ditampilkan.");
      return;
    }

    const visibleRules = rules.slice(0, 5);

    rulesBody.innerHTML = visibleRules
      .map((rule) => {
        const badgeClass =
          rule.status_badge ||
          (rule.is_active
            ? "bg-green-100 text-green-800 dark:bg-emerald-500/20 dark:text-emerald-200"
            : "bg-gray-100 text-gray-600 dark:bg-slate-800/70 dark:text-slate-200");
        const badgeLabel =
          rule.status_label || (rule.is_active ? "Aktif" : "Tidak Aktif");
        return `
                <tr class="transition hover:bg-gray-50 dark:hover:bg-slate-900/60">
                    <td class="border border-black/40 px-6 py-4 font-medium text-gray-900 dark:text-slate-100 dark:border-gray-300">${escapeHtml(
                      rule.id ?? "-"
                    )}</td>
                    <td class="border border-black/40 px-6 py-4 text-gray-700 dark:text-slate-200 dark:border-gray-300">${escapeHtml(
                      rule.name ?? "-"
                    )}</td>
                    <td class="border border-black/40 px-6 py-4 dark:border-gray-300">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}">${escapeHtml(
          badgeLabel
        )}</span>
                    </td>
                    <td class="border border-black/40 px-6 py-4 text-right text-sm text-gray-500 dark:text-slate-400 dark:border-gray-300">${escapeHtml(
                      rule.updated_human ?? rule.updated_at ?? "-"
                    )}</td>
                </tr>
            `;
      })
      .join("");

    if (rulesCards) {
      rulesCards.innerHTML = visibleRules
        .map((rule) => {
          const badgeClass =
            rule.status_badge ||
            (rule.is_active
              ? "bg-green-100 text-green-800 dark:bg-emerald-500/20 dark:text-emerald-200"
              : "bg-gray-100 text-gray-600 dark:bg-slate-800/70 dark:text-slate-200");
          const badgeLabel =
            rule.status_label || (rule.is_active ? "Aktif" : "Tidak Aktif");
          const updatedAt = rule.updated_human ?? rule.updated_at ?? "-";

          return `
            <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-5 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-black/70 dark:bg-slate-950/70 dark:shadow-black/30 dark:ring-black/60">
              <div class="text-base font-semibold text-gray-900 dark:text-slate-100">${escapeHtml(
                rule.name ?? "Rule"
              )}</div>
              <dl class="mt-4 space-y-3">
                <div>
                  <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">ID Rule</dt>
                  <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">${escapeHtml(
                    rule.id ?? "-"
                  )}</dd>
                </div>
                <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                  <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Status</dt>
                  <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}">${escapeHtml(
                      badgeLabel
                    )}</span>
                  </dd>
                </div>
                <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                  <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Terakhir Diperbarui</dt>
                  <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">${escapeHtml(
                    updatedAt
                  )}</dd>
                </div>
              </dl>
            </div>
          `;
        })
        .join("");
    }
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
    setRulesCardLoading("Memuat data rule...");
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
      setRulesCardMessage(error.message || "Gagal memuat data rule.", "error");
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
