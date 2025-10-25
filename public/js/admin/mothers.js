import {
  createSpinnerRow,
  escapeHtml,
  fetchJson,
  showNotification,
} from "./utils.js";

export const initAdminMothers = () => {
  const container = document.querySelector("[data-admin-mothers]");
  if (!container) {
    return;
  }

  const baseEndpoint = container.dataset.baseEndpoint;
  const notificationId = container.dataset.notificationId;
  const tableBody = container.querySelector("[data-table-body]");
  const cardContainer = container.querySelector("[data-card-container]");
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
      element.innerHTML =
        '<li class="text-gray-400 dark:text-slate-500">Tidak ada data</li>';
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

  const renderRows = (items) => {
    motherStore.clear();

    if (!Array.isArray(items) || items.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="border border-black/40 px-6 py-6 text-center text-sm text-gray-500 dark:border-gray-300 dark:text-slate-400">Belum ada data ibu.</td>
                </tr>
            `;
      setCardMessage("Belum ada data ibu.");
      return;
    }

    const normalized = items.map((mother) => {
      if (mother?.id !== undefined) {
        motherStore.set(String(mother.id), mother);
      }

      const status = mother?.status || {};
      const badgeClass =
        typeof status.badge === "string" && status.badge.trim() !== ""
          ? status.badge
          : "bg-gray-100 text-gray-600 dark:bg-slate-800/70 dark:text-slate-200";
      const badgeLabel = status.label || "Tidak diketahui";

      return {
        id: mother?.id ?? "",
        name: mother?.name ?? "-",
        email: mother?.email ?? "-",
        umur: formatValue(mother?.umur),
        usiaBayi: formatValue(mother?.usia_bayi_bln),
        badgeClass,
        badgeLabel,
      };
    });

    tableBody.innerHTML = normalized
      .map((mother) => `
        <tr class="transition hover:bg-gray-50 dark:hover:bg-slate-900/60">
          <td class="border border-black/40 px-6 py-4 font-medium text-gray-900 dark:text-slate-100 dark:border-gray-300">${escapeHtml(
            mother.name
          )}</td>
          <td class="border border-black/40 px-6 py-4 text-gray-700 dark:text-slate-200 dark:border-gray-300">${escapeHtml(
            mother.email
          )}</td>
          <td class="border border-black/40 px-6 py-4 text-gray-600 dark:text-slate-200 dark:border-gray-300">${escapeHtml(
            mother.umur
          )}</td>
          <td class="border border-black/40 px-6 py-4 text-gray-600 dark:text-slate-200 dark:border-gray-300">${escapeHtml(
            mother.usiaBayi
          )}</td>
          <td class="border border-black/40 px-6 py-4 dark:border-gray-300">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${escapeHtml(
              mother.badgeClass
            )}">${escapeHtml(mother.badgeLabel)}</span>
          </td>
          <td class="border border-black/40 px-6 py-4 text-right text-sm dark:text-slate-200 dark:border-gray-300">
            <div class="flex flex-wrap justify-end gap-2">
              <button type="button" class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 dark:border-giziblue/70 dark:text-blue-300 dark:hover:bg-slate-900/50" data-action="detail" data-id="${escapeHtml(
                String(mother.id)
              )}">Detail</button>
              <button type="button" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-slate/70 dark:text-slate-300 dark:hover:bg-slate-900/50" data-action="email" data-id="${escapeHtml(
                String(mother.id)
              )}">Edit Email</button>
              <button type="button" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-slate/70 dark:text-slate-300 dark:hover:bg-slate-900/50" data-action="password" data-id="${escapeHtml(
                String(mother.id)
              )}">Atur Password</button>
              <button type="button" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-rose-400/40 dark:text-rose-300 dark:hover:bg-rose-500/10" data-action="delete" data-id="${escapeHtml(
                String(mother.id)
              )}">Hapus</button>
            </div>
          </td>
        </tr>
      `)
      .join("");

    if (cardContainer) {
      cardContainer.innerHTML = normalized
        .map((mother) => `
          <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-5 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-black/70 dark:bg-slate-950/70 dark:shadow-black/30 dark:ring-black/60">
            <div class="text-base font-semibold text-gray-900 dark:text-slate-100">${escapeHtml(
              mother.name
            )}</div>
            <dl class="mt-4 space-y-3">
              <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Email</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">${escapeHtml(mother.email)}</dd>
              </div>
              <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Umur</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">${escapeHtml(mother.umur)}</dd>
              </div>
              <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Usia Bayi (bln)</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">${escapeHtml(mother.usiaBayi)}</dd>
              </div>
              <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Status</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${escapeHtml(
                    mother.badgeClass
                  )}">${escapeHtml(mother.badgeLabel)}</span>
                </dd>
              </div>
            </dl>
            <div class="mt-4 flex flex-wrap gap-3">
              <button type="button" class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 dark:border-giziblue/70 dark:text-blue-300 dark:hover:bg-slate-900/50" data-action="detail" data-id="${escapeHtml(
                String(mother.id)
              )}">Detail</button>
              <button type="button" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-slate/70 dark:text-slate-300 dark:hover:bg-slate-900/50" data-action="email" data-id="${escapeHtml(
                String(mother.id)
              )}">Edit Email</button>
              <button type="button" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-slate/70 dark:text-slate-300 dark:hover:bg-slate-900/50" data-action="password" data-id="${escapeHtml(
                String(mother.id)
              )}">Atur Password</button>
              <button type="button" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-rose-400/40 dark:text-rose-300 dark:hover:bg-rose-500/10" data-action="delete" data-id="${escapeHtml(
                String(mother.id)
              )}">Hapus</button>
            </div>
          </div>
        `)
        .join("");
    }

    attachActionHandlers();
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
          : "bg-gray-100 text-gray-600 dark:bg-slate-800/70 dark:text-slate-200";
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

  const attachActionHandlers = () => {
    [tableBody, cardContainer].forEach((root) => {
      if (!root) {
        return;
      }

      root.querySelectorAll("button[data-action]").forEach((button) => {
        button.addEventListener("click", () => {
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
    });
  };

  const loadMothers = async () => {
    tableBody.innerHTML = createSpinnerRow(6, "Memuat data ibu...");
    setCardLoading("Memuat data ibu...");
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
      setCardMessage(error.message || "Gagal memuat data ibu.", "error");
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
