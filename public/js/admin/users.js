import {
  createSpinnerRow,
  escapeHtml,
  fetchJson,
  showNotification,
} from "./utils.js";

export const initAdminUsers = () => {
  const container = document.querySelector("[data-admin-users]");
  if (!container) {
    return;
  }

  const baseEndpoint = container.dataset.baseEndpoint;
  const notificationId = container.dataset.notificationId;
  const tableBody = container.querySelector("[data-table-body]");
  const cardContainer = container.querySelector("[data-card-container]");
  if (!tableBody) {
    return;
  }
  const createModal = document.getElementById("adminUserCreateModal");
  const editModal = document.getElementById("adminUserEditModal");
  const passwordModal = document.getElementById("adminUserPasswordModal");
  const createForm = document.getElementById("adminUserCreateForm");
  const editForm = document.getElementById("adminUserEditForm");
  const passwordForm = document.getElementById("adminUserPasswordForm");
  const createNameInput = document.getElementById("userCreateName");
  const createEmailInput = document.getElementById("userCreateEmail");
  const createRoleInput = document.getElementById("userCreateRole");
  const createPasswordInput = document.getElementById("userCreatePassword");
  const editIdInput = document.getElementById("userEditId");
  const editNameInput = document.getElementById("userEditName");
  const editEmailInput = document.getElementById("userEditEmail");
  const editRoleInput = document.getElementById("userEditRole");
  const passwordIdInput = document.getElementById("userPasswordId");
  const passwordInput = document.getElementById("userPasswordInput");
  const openCreateButton = container.querySelector("[data-open-create]");

  const userStore = new Map();
  let activeUserId = null;

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

  const resetForm = (form) => {
    if (form) {
      form.reset();
    }
  };

  const closeCreateModal = () => {
    toggleModal(createModal, false);
    resetForm(createForm);
    if (createRoleInput) {
      createRoleInput.value = "admin";
    }
    activeUserId = null;
  };

  const closeEditModal = () => {
    toggleModal(editModal, false);
    resetForm(editForm);
    activeUserId = null;
  };

  const closePasswordModal = () => {
    toggleModal(passwordModal, false);
    if (passwordInput) {
      passwordInput.value = "";
    }
    resetForm(passwordForm);
    activeUserId = null;
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
    userStore.clear();

    if (!Array.isArray(items) || items.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="border border-black/40 px-6 py-6 text-center text-sm text-gray-500 dark:border-gray-300 dark:text-slate-400">Belum ada data pengguna.</td>
                </tr>
            `;
      setCardMessage("Belum ada data pengguna.");
      return;
    }

    const normalized = items.map((user) => {
      if (user?.id !== undefined) {
        userStore.set(String(user.id), user);
      }

      const badgeClass =
        typeof user?.role_badge === "string" && user.role_badge.trim() !== ""
          ? user.role_badge
          : user?.role === "admin"
          ? "bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-200"
          : "bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200";
      const roleLabel =
        typeof user?.role_label === "string" && user.role_label.trim() !== ""
          ? user.role_label
          : user?.role ?? "-";
      const createdAt = user?.created_at_human || user?.created_at || "-";

      return {
        id: user?.id ?? "",
        name: user?.name ?? "-",
        email: user?.email ?? "-",
        badgeClass,
        roleLabel,
        createdAt,
      };
    });

    tableBody.innerHTML = normalized
      .map((user) => `
        <tr class="transition hover:bg-gray-50 dark:hover:bg-slate-900/60">
          <td class="border border-black/40 px-6 py-4 font-medium text-gray-900 dark:text-slate-100 dark:border-gray-300">${escapeHtml(
            user.name
          )}</td>
          <td class="border border-black/40 px-6 py-4 text-gray-700 dark:text-slate-200 dark:border-gray-300">${escapeHtml(
            user.email
          )}</td>
          <td class="border border-black/40 px-6 py-4 dark:border-gray-300">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${escapeHtml(
              user.badgeClass
            )}">${escapeHtml(user.roleLabel)}</span>
          </td>
          <td class="border border-black/40 px-6 py-4 text-gray-600 dark:text-slate-200 dark:border-gray-300">${escapeHtml(
            user.createdAt
          )}</td>
          <td class="border border-black/40 px-6 py-4 text-right text-sm dark:text-slate-200 dark:border-gray-300">
            <div class="flex flex-wrap justify-end gap-2">
              <button type="button" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-slate/70 dark:text-slate-300 dark:hover:bg-slate-900/50" data-action="edit" data-id="${escapeHtml(
                String(user.id)
              )}">Edit</button>
              <button type="button" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-slate/70 dark:text-slate-300 dark:hover:bg-slate-900/50" data-action="password" data-id="${escapeHtml(
                String(user.id)
              )}">Atur Password</button>
              <button type="button" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-rose-400/40 dark:text-rose-300 dark:hover:bg-rose-500/10" data-action="delete" data-id="${escapeHtml(
                String(user.id)
              )}">Hapus</button>
            </div>
          </td>
        </tr>
      `)
      .join("");

    if (cardContainer) {
      cardContainer.innerHTML = normalized
        .map((user) => `
          <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-5 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-black/70 dark:bg-slate-950/70 dark:shadow-black/30 dark:ring-black/60">
            <div class="text-base font-semibold text-gray-900 dark:text-slate-100">${escapeHtml(
              user.name
            )}</div>
            <dl class="mt-4 space-y-3">
              <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Email</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">${escapeHtml(user.email)}</dd>
              </div>
              <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Peran</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${escapeHtml(
                    user.badgeClass
                  )}">${escapeHtml(user.roleLabel)}</span>
                </dd>
              </div>
              <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Dibuat</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-slate-200">${escapeHtml(user.createdAt)}</dd>
              </div>
            </dl>
            <div class="mt-4 flex flex-wrap gap-3">
              <button type="button" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-slate/70 dark:text-slate-300 dark:hover:bg-slate-900/50" data-action="edit" data-id="${escapeHtml(
                String(user.id)
              )}">Edit</button>
              <button type="button" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-slate/70 dark:text-slate-300 dark:hover:bg-slate-900/50" data-action="password" data-id="${escapeHtml(
                String(user.id)
              )}">Atur Password</button>
              <button type="button" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-rose-400/40 dark:text-rose-300 dark:hover:bg-rose-500/10" data-action="delete" data-id="${escapeHtml(
                String(user.id)
              )}">Hapus</button>
            </div>
          </div>
        `)
        .join("");
    }

    attachActionHandlers();
  };

  const handleEdit = (id) => {
    const user = userStore.get(String(id));
    if (!user) {
      showNotification(
        notificationId,
        "error",
        "Data pengguna tidak ditemukan."
      );
      return;
    }

    activeUserId = String(id);
    if (editIdInput) {
      editIdInput.value = String(id);
    }
    if (editNameInput) {
      editNameInput.value = user.name ?? "";
    }
    if (editEmailInput) {
      editEmailInput.value = user.email ?? "";
    }
    if (editRoleInput) {
      editRoleInput.value = user.role ?? "admin";
    }

    toggleModal(editModal, true);
  };

  const handlePassword = (id) => {
    const user = userStore.get(String(id));
    if (!user) {
      showNotification(
        notificationId,
        "error",
        "Data pengguna tidak ditemukan."
      );
      return;
    }

    activeUserId = String(id);
    if (passwordIdInput) {
      passwordIdInput.value = String(id);
    }
    if (passwordInput) {
      passwordInput.value = "";
    }

    toggleModal(passwordModal, true);
  };

  const handleDelete = async (id) => {
    const user = userStore.get(String(id));
    if (!user) {
      showNotification(
        notificationId,
        "error",
        "Data pengguna tidak ditemukan."
      );
      return;
    }

    const confirmation = window.confirm(
      `Hapus pengguna "${user.name ?? "Tanpa Nama"}"?`
    );
    if (!confirmation) {
      return;
    }

    try {
      await fetchJson(`${baseEndpoint}/${id}`, { method: "DELETE" });
      showNotification(notificationId, "success", "Pengguna berhasil dihapus.");
      await loadUsers();
    } catch (error) {
      showNotification(
        notificationId,
        "error",
        error.message || "Gagal menghapus pengguna."
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
            showNotification(notificationId, "error", "ID pengguna tidak valid.");
            return;
          }

          if (action === "edit") {
            handleEdit(id);
          } else if (action === "password") {
            handlePassword(id);
          } else if (action === "delete") {
            handleDelete(id);
          }
        });
      });
    });
  };

  const loadUsers = async () => {
    tableBody.innerHTML = createSpinnerRow(5, "Memuat data pengguna...");
    setCardLoading("Memuat data pengguna...");
    try {
      const payload = await fetchJson(baseEndpoint);
      const data = payload?.data ?? payload;
      renderRows(Array.isArray(data) ? data : data?.items ?? []);
    } catch (error) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-6 text-center text-sm text-red-600">${escapeHtml(
                      error.message || "Gagal memuat data pengguna."
                    )}</td>
                </tr>
            `;
      setCardMessage(error.message || "Gagal memuat data pengguna.", "error");
      showNotification(
        notificationId,
        "error",
        error.message || "Gagal memuat data pengguna."
      );
    }
  };

  if (openCreateButton) {
    openCreateButton.addEventListener("click", () => {
      if (createForm) {
        createForm.reset();
      }
      if (createRoleInput) {
        createRoleInput.value = "admin";
      }
      if (createPasswordInput) {
        createPasswordInput.value = "";
      }
      toggleModal(createModal, true);
      if (createNameInput) {
        createNameInput.focus();
      }
    });
  }

  createModal?.querySelectorAll("[data-close-create]").forEach((button) => {
    button.addEventListener("click", closeCreateModal);
  });
  editModal?.querySelectorAll("[data-close-edit]").forEach((button) => {
    button.addEventListener("click", closeEditModal);
  });
  passwordModal?.querySelectorAll("[data-close-password]").forEach((button) => {
    button.addEventListener("click", closePasswordModal);
  });

  if (createForm) {
    createForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      const name = createNameInput?.value?.trim() ?? "";
      const email = createEmailInput?.value?.trim() ?? "";
      const role = (createRoleInput?.value ?? "").toLowerCase();
      const password = createPasswordInput?.value ?? "";

      if (!name || !email || !role || !password) {
        showNotification(
          notificationId,
          "error",
          "Mohon lengkapi seluruh data pengguna."
        );
        return;
      }

      try {
        await fetchJson(baseEndpoint, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ name, email, role, password }),
        });
        closeCreateModal();
        showNotification(
          notificationId,
          "success",
          "Pengguna berhasil ditambahkan."
        );
        await loadUsers();
      } catch (error) {
        showNotification(
          notificationId,
          "error",
          error.message || "Gagal menambahkan pengguna."
        );
      }
    });
  }

  if (editForm) {
    editForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      const id = editIdInput?.value || activeUserId;
      const name = editNameInput?.value?.trim() ?? "";
      const email = editEmailInput?.value?.trim() ?? "";
      const role = (editRoleInput?.value ?? "").toLowerCase();

      if (!id) {
        showNotification(notificationId, "error", "ID pengguna tidak valid.");
        return;
      }

      if (!name || !email || !role) {
        showNotification(
          notificationId,
          "error",
          "Mohon lengkapi seluruh data pengguna."
        );
        return;
      }

      try {
        await fetchJson(`${baseEndpoint}/${id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ name, email, role }),
        });
        closeEditModal();
        showNotification(
          notificationId,
          "success",
          "Pengguna berhasil diperbarui."
        );
        await loadUsers();
      } catch (error) {
        showNotification(
          notificationId,
          "error",
          error.message || "Gagal memperbarui pengguna."
        );
      }
    });
  }

  if (passwordForm) {
    passwordForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      const id = passwordIdInput?.value || activeUserId;
      const password = passwordInput?.value ?? "";

      if (!id) {
        showNotification(notificationId, "error", "ID pengguna tidak valid.");
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
          "Password pengguna berhasil diperbarui."
        );
        await loadUsers();
      } catch (error) {
        showNotification(
          notificationId,
          "error",
          error.message || "Gagal memperbarui password pengguna."
        );
      }
    });
  }

  loadUsers();
};
