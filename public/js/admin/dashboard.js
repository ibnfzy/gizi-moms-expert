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
                    <td class="border border-black/40 px-6 py-4 font-medium text-gray-900 dark:text-gray-50 dark:border-gray-300">${escapeHtml(
                      rule.id ?? "-"
                    )}</td>
                    <td class="border border-black/40 px-6 py-4 text-gray-700 dark:text-gray-50 dark:border-gray-300">${escapeHtml(
                      rule.name ?? "-"
                    )}</td>
                    <td class="border border-black/40 px-6 py-4 dark:border-gray-300">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}">${escapeHtml(
          badgeLabel
        )}</span>
                    </td>
                    <td class="border border-black/40 px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-50 dark:border-gray-300">${escapeHtml(
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
