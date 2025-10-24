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

const extractErrorMessage = (raw, fallback) => {
  if (!raw) {
    return fallback;
  }

  const trimmed = raw.trim();
  if (trimmed === "") {
    return fallback;
  }

  if (trimmed.startsWith("{")) {
    try {
      const payload = JSON.parse(trimmed);
      if (payload && typeof payload.message === "string" && payload.message.trim() !== "") {
        return payload.message.trim();
      }
    } catch (error) {
      // Ignore JSON parse errors and fallback to HTML parsing.
    }
  }

  const container = document.createElement("div");
  container.innerHTML = trimmed;
  const text = container.textContent?.trim();

  return text && text !== "" ? text : fallback;
};

const fetchHtml = async (url, options = {}) => {
  if (!url) {
    throw new Error("Endpoint tidak tersedia.");
  }

  const { acceptErrorResponse = false, headers = {}, ...fetchOptions } = options;

  const config = {
    credentials: "same-origin",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      ...headers,
    },
    ...fetchOptions,
  };

  if (config.body instanceof FormData && config.headers) {
    delete config.headers["Content-Type"];
  }

  const response = await fetch(url, config);
  const text = await response.text();
  const message = extractErrorMessage(
    text,
    response.statusText || "Permintaan gagal diproses."
  );

  if (!response.ok && !acceptErrorResponse) {
    throw new Error(message);
  }

  return { html: text, ok: response.ok, message };
};

const fetchJson = async (url, options = {}) => {
  if (!url) {
    throw new Error("Endpoint tidak tersedia.");
  }

  const { acceptErrorResponse = false, headers = {}, ...fetchOptions } = options;

  const config = {
    credentials: "same-origin",
    headers: {
      "Accept": "application/json",
      "Content-Type": "application/json",
      ...headers,
    },
    ...fetchOptions,
  };

  const response = await fetch(url, config);
  const text = await response.text();
  let data = null;

  if (text && text.trim() !== "") {
    try {
      data = JSON.parse(text);
    } catch (error) {
      // Ignore JSON parse errors and fallback to message extraction.
    }
  }

  const message = extractErrorMessage(
    text,
    response.statusText || "Permintaan gagal diproses."
  );

  if (!response.ok && !acceptErrorResponse) {
    throw new Error(message);
  }

  return { ok: response.ok, data, message };
};

const createErrorCard = (message) =>
  `<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">${escapeHtml(
    message
  )}</div>`;

const createErrorPanel = (message) =>
  `<div class="flex h-full items-center justify-center rounded-2xl border border-red-200 bg-red-50 p-6 text-center text-sm text-red-700">${escapeHtml(
    message
  )}</div>`;

const initSchedulePage = () => {
  const page = document.querySelector("[data-pakar-schedules]");
  if (!page) {
    return;
  }

  const rowUrlTemplate = page.dataset.rowUrlTemplate || "";
  const feedbackElement = page.querySelector("[data-schedule-feedback]");
  const filterForm = page.querySelector("[data-schedule-filter-form]");
  const resetButton = page.querySelector("[data-schedule-filter-reset]");
  const statusSelect = filterForm?.querySelector("[name='status']");
  const modal = document.querySelector("[data-schedule-evaluation-modal]");
  const modalForm = modal?.querySelector("[data-schedule-evaluation-form]");
  const modalFeedback = modal?.querySelector("[data-modal-feedback]");
  const summaryField = modal?.querySelector("[data-modal-summary]");
  const followUpField = modal?.querySelector("[data-modal-follow-up]");
  const modalTitle = modal?.querySelector("[data-modal-title]");
  const modalSchedule = modal?.querySelector("[data-modal-schedule]");
  const modalOverlay = modal?.querySelector("[data-modal-overlay]");
  const modalCloseButtons = modal?.querySelectorAll("[data-modal-dismiss]") || [];

  const successFeedbackClasses = [
    "border-emerald-200",
    "bg-emerald-50",
    "text-emerald-700",
    "dark:border-emerald-400/40",
    "dark:bg-emerald-400/10",
    "dark:text-emerald-200",
  ];

  const errorFeedbackClasses = [
    "border-red-200",
    "bg-red-50",
    "text-red-700",
    "dark:border-red-400/40",
    "dark:bg-red-500/10",
    "dark:text-red-200",
  ];

  let feedbackTimeout = null;
  let escapeHandler = null;

  const hideFeedback = () => {
    if (!feedbackElement) {
      return;
    }

    if (feedbackTimeout) {
      window.clearTimeout(feedbackTimeout);
      feedbackTimeout = null;
    }

    feedbackElement.classList.add("hidden");
    feedbackElement.textContent = "";
    feedbackElement.classList.remove(
      ...successFeedbackClasses,
      ...errorFeedbackClasses
    );
  };

  const showFeedback = (type, message) => {
    if (!feedbackElement || !message) {
      return;
    }

    if (feedbackTimeout) {
      window.clearTimeout(feedbackTimeout);
    }

    feedbackElement.textContent = message;
    feedbackElement.classList.remove("hidden");
    feedbackElement.classList.remove(
      ...successFeedbackClasses,
      ...errorFeedbackClasses
    );

    if (type === "success") {
      feedbackElement.classList.add(...successFeedbackClasses);
    } else {
      feedbackElement.classList.add(...errorFeedbackClasses);
    }

    feedbackTimeout = window.setTimeout(() => {
      hideFeedback();
    }, 6000);
  };

  const parseApiMessage = (xhr) => {
    if (!xhr) {
      return "";
    }

    const text = xhr.responseText || "";
    const contentType = xhr.getResponseHeader
      ? xhr.getResponseHeader("Content-Type") || ""
      : "";

    if (contentType.includes("application/json")) {
      try {
        const payload = JSON.parse(text);
        if (
          payload &&
          typeof payload.message === "string" &&
          payload.message.trim() !== ""
        ) {
          return payload.message.trim();
        }
      } catch (error) {
        // Ignore JSON parse errors and fallback to HTML parsing.
      }
    }

    return extractErrorMessage(
      text,
      xhr.statusText || "Permintaan gagal diproses."
    );
  };

  const clearModalFeedback = () => {
    if (!modalFeedback) {
      return;
    }

    modalFeedback.classList.add("hidden");
    modalFeedback.textContent = "";
    modalFeedback.classList.remove(
      ...successFeedbackClasses,
      ...errorFeedbackClasses
    );
  };

  const showModalFeedback = (type, message) => {
    if (!modalFeedback || !message) {
      return;
    }

    modalFeedback.textContent = message;
    modalFeedback.classList.remove("hidden");
    modalFeedback.classList.remove(
      ...successFeedbackClasses,
      ...errorFeedbackClasses
    );

    if (type === "success") {
      modalFeedback.classList.add(...successFeedbackClasses);
    } else {
      modalFeedback.classList.add(...errorFeedbackClasses);
    }
  };

  const closeModal = () => {
    if (!modal) {
      return;
    }

    modal.classList.add("hidden");
    modal.classList.remove("flex");
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("overflow-hidden");

    if (modalForm) {
      modalForm.reset();
      modalForm.removeAttribute("hx-put");
      delete modalForm.dataset.scheduleId;
    }

    if (modalSchedule) {
      modalSchedule.textContent = "";
    }

    clearModalFeedback();

    if (escapeHandler) {
      document.removeEventListener("keydown", escapeHandler);
      escapeHandler = null;
    }
  };

  const openModal = (options = {}) => {
    if (!modal || !modalForm) {
      return;
    }

    const {
      scheduleId = "",
      evaluationUrl = "",
      name = "",
      datetime = "",
      summary = "",
      followUp = false,
    } = options;

    if (!scheduleId || !evaluationUrl) {
      return;
    }

    modal.classList.remove("hidden");
    modal.classList.add("flex");
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("overflow-hidden");

    modalForm.setAttribute("hx-put", evaluationUrl);
    modalForm.dataset.scheduleId = scheduleId;

    clearModalFeedback();

    if (modalTitle) {
      modalTitle.textContent = name
        ? `Evaluasi • ${name}`
        : "Evaluasi Konsultasi";
    }

    if (modalSchedule) {
      const parts = [];
      if (name) {
        parts.push(name);
      }
      if (datetime) {
        parts.push(datetime);
      }
      modalSchedule.textContent = parts.join(" • ");
    }

    if (summaryField) {
      summaryField.value = summary || "";
      window.requestAnimationFrame(() => {
        summaryField.focus();
        const length = summaryField.value.length;
        summaryField.setSelectionRange(length, length);
      });
    }

    if (followUpField) {
      followUpField.checked = Boolean(followUp);
    }

    if (escapeHandler) {
      document.removeEventListener("keydown", escapeHandler);
    }

    escapeHandler = (event) => {
      if (event.key === "Escape") {
        event.preventDefault();
        closeModal();
      }
    };

    document.addEventListener("keydown", escapeHandler);
  };

  const refreshRow = async (scheduleId) => {
    if (!scheduleId || !rowUrlTemplate) {
      return false;
    }

    const target = document.getElementById(`schedule-row-${scheduleId}`);
    if (!target) {
      return false;
    }

    const url = rowUrlTemplate.replace("__id__", encodeURIComponent(scheduleId));

    try {
      const { html, ok, message } = await fetchHtml(url, {
        acceptErrorResponse: true,
      });

      if (!ok) {
        showFeedback("error", message || "Gagal memperbarui jadwal.");
        return false;
      }

      if (html && html.trim() !== "") {
        target.outerHTML = html;
      }

      return true;
    } catch (error) {
      showFeedback("error", error?.message || "Gagal memperbarui jadwal.");
      return false;
    }
  };

  if (modalOverlay) {
    modalOverlay.addEventListener("click", (event) => {
      event.preventDefault();
      closeModal();
    });
  }

  modalCloseButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      event.preventDefault();
      closeModal();
    });
  });

  if (modalForm) {
    modalForm.addEventListener("htmx:beforeRequest", () => {
      clearModalFeedback();
    });
  }

  if (resetButton && statusSelect && filterForm) {
    resetButton.addEventListener("click", (event) => {
      event.preventDefault();
      statusSelect.value = "";
      page.dataset.currentStatus = "";
      hideFeedback();
      if (window.htmx) {
        window.htmx.trigger(filterForm, "submit");
      }
    });
  }

  if (statusSelect) {
    statusSelect.addEventListener("change", () => {
      page.dataset.currentStatus = statusSelect.value || "";
    });
  }

  if (filterForm) {
    filterForm.addEventListener("htmx:afterRequest", () => {
      if (statusSelect) {
        page.dataset.currentStatus = statusSelect.value || "";
      }
    });
  }

  page.addEventListener("click", (event) => {
    const target = event.target;
    if (!(target instanceof Element)) {
      return;
    }

    const button = target.closest("[data-schedule-evaluation-button]");
    if (!button) {
      return;
    }

    event.preventDefault();

    const scheduleId = button.dataset.scheduleId || "";
    const evaluationUrl = button.dataset.scheduleEvaluationUrl || "";

    if (!scheduleId || !evaluationUrl) {
      return;
    }

    openModal({
      scheduleId,
      evaluationUrl,
      name: button.dataset.scheduleName || "",
      datetime: button.dataset.scheduleDatetime || "",
      summary: button.dataset.evaluationSummary || "",
      followUp: button.dataset.evaluationFollowUp === "1",
    });
  });

  page.addEventListener("htmx:afterRequest", async (event) => {
    const { detail } = event;
    if (!detail) {
      return;
    }

    const trigger = detail.elt;
    if (!trigger || trigger.dataset?.scheduleRefresh !== "true") {
      return;
    }

    const scheduleId = trigger.dataset.scheduleId || "";
    const message = parseApiMessage(detail.xhr);
    const successful =
      typeof detail.successful === "boolean"
        ? detail.successful
        : detail.xhr?.status >= 200 && detail.xhr?.status < 300;

    if (successful) {
      if (trigger === modalForm) {
        clearModalFeedback();
        closeModal();
      }

      const refreshed = scheduleId ? await refreshRow(scheduleId) : true;

      if (refreshed) {
        const successMessage = message || "Data jadwal berhasil diperbarui.";
        showFeedback("success", successMessage);
      }
    } else if (trigger === modalForm) {
      showModalFeedback("error", message || "Evaluasi gagal disimpan.");
    } else if (message) {
      showFeedback("error", message);
    }
  });

  page.addEventListener("htmx:responseError", (event) => {
    const { detail } = event;
    if (!detail) {
      return;
    }

    const trigger = detail.elt;
    if (!trigger || trigger.dataset?.scheduleRefresh !== "true") {
      return;
    }

    const message = parseApiMessage(detail.xhr);

    if (trigger === modalForm) {
      showModalFeedback("error", message || "Evaluasi gagal disimpan.");
    } else if (message) {
      showFeedback("error", message);
    }
  });
};

const initDashboardPage = () => {
  const page = document.querySelector("[data-pakar-dashboard]");
  if (!page) {
    return;
  }

  const dataContainer = document.getElementById("dashboard-data");
  const dashboardIndicator = document.getElementById("dashboard-loading");
  const detailIndicator = document.getElementById("mother-detail-loading");
  const detailContainer = document.getElementById("mother-detail-container");

  let detailEscapeHandler = null;
  let currentDetailUrl = null;
  let currentMotherId = null;

  const successFeedbackClasses = [
    "border-emerald-200",
    "bg-emerald-50",
    "text-emerald-700",
  ];
  const errorFeedbackClasses = [
    "border-red-200",
    "bg-red-50",
    "text-red-700",
  ];

  const toggleIndicator = (indicator, show) => {
    if (!indicator) {
      return;
    }
    if (show) {
      indicator.classList.remove("hidden");
    } else {
      indicator.classList.add("hidden");
    }
  };

  const clearDashboardErrors = () => {
    if (!dataContainer) {
      return;
    }
    dataContainer
      .querySelectorAll("[data-dashboard-error]")
      .forEach((element) => element.remove());
  };

  const showDashboardError = (message) => {
    if (!dataContainer) {
      return;
    }
    clearDashboardErrors();
    dataContainer.insertAdjacentHTML(
      "afterbegin",
      `<div data-dashboard-error class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">${escapeHtml(
        message
      )}</div>`
    );
  };

  const applyInferenceFeedback = (modal, type, message) => {
    if (!modal) {
      return;
    }

    const wrapper = modal.querySelector(
      "[data-inference-feedback-wrapper]"
    );
    const target = modal.querySelector("[data-inference-feedback]");

    if (!wrapper || !target) {
      return;
    }

    target.classList.remove(...successFeedbackClasses, ...errorFeedbackClasses);

    if (!type || !message) {
      wrapper.classList.add("hidden");
      target.textContent = "";
      return;
    }

    target.textContent = message;
    wrapper.classList.remove("hidden");

    if (type === "success") {
      target.classList.add(...successFeedbackClasses);
    } else {
      target.classList.add(...errorFeedbackClasses);
    }
  };

  const setButtonLoading = (button, loading, loadingText = "Memproses...") => {
    if (!button) {
      return;
    }

    if (loading) {
      if (button.dataset.originalText === undefined) {
        button.dataset.originalText = button.textContent || "";
      }
      button.disabled = true;
      button.setAttribute("aria-busy", "true");
      button.textContent = loadingText;
      return;
    }

    button.disabled = false;
    button.removeAttribute("aria-busy");
    if (button.dataset.originalText !== undefined) {
      button.textContent = button.dataset.originalText;
      delete button.dataset.originalText;
    }
  };

  const closeDetail = (options = {}) => {
    const { preserveUrl = false } = options;

    if (detailEscapeHandler) {
      document.removeEventListener("keydown", detailEscapeHandler);
      detailEscapeHandler = null;
    }

    if (!preserveUrl) {
      currentDetailUrl = null;
      currentMotherId = null;
    }

    if (detailContainer) {
      detailContainer.innerHTML = "";
    }
  };

  const reloadDashboardData = async () => {
    if (!dataContainer) {
      return false;
    }

    const refreshButton = dataContainer.querySelector(
      "[data-dashboard-refresh]"
    );
    const endpoint = refreshButton?.dataset.dashboardRefresh;

    if (!endpoint) {
      return false;
    }

    let shouldRebind = false;

    toggleIndicator(dashboardIndicator, true);

    try {
      const { html } = await fetchHtml(endpoint);
      clearDashboardErrors();
      dataContainer.innerHTML = html;
      shouldRebind = true;
      return true;
    } catch (error) {
      showDashboardError(error?.message || "Gagal memuat data ibu.");
      return false;
    } finally {
      toggleIndicator(dashboardIndicator, false);
      if (shouldRebind) {
        bindDashboardActions();
      }
    }
  };

  const bindDetailModal = (options = {}) => {
    if (!detailContainer) {
      return;
    }

    const modal = detailContainer.querySelector('[data-modal="mother-detail"]');
    if (!modal) {
      return;
    }

    const detailUrlAttr = modal.getAttribute("data-detail-url");
    if (detailUrlAttr) {
      currentDetailUrl = detailUrlAttr;
    }

    const motherIdAttr = modal.getAttribute("data-mother-id");
    currentMotherId = motherIdAttr && motherIdAttr !== "" ? motherIdAttr : null;

    if (detailEscapeHandler) {
      document.removeEventListener("keydown", detailEscapeHandler);
    }

    const handleEscape = (event) => {
      if (event.key === "Escape") {
        event.preventDefault();
        closeDetail();
      }
    };

    detailEscapeHandler = handleEscape;
    document.addEventListener("keydown", handleEscape);

    modal.querySelectorAll("[data-close-mother-detail]").forEach((button) => {
      button.addEventListener("click", (event) => {
        event.preventDefault();
        closeDetail();
      });
    });

    modal.addEventListener("click", (event) => {
      if (event.target === modal) {
        closeDetail();
      }
    });

    const { feedback } = options;
    if (feedback && feedback.type && feedback.message) {
      applyInferenceFeedback(modal, feedback.type, feedback.message);
    } else {
      applyInferenceFeedback(modal);
    }

    const inferenceButton = modal.querySelector("[data-run-inference]");
    if (inferenceButton) {
      inferenceButton.addEventListener("click", async (event) => {
        event.preventDefault();

        const endpoint =
          inferenceButton.dataset.inferenceEndpoint || "";
        const motherId =
          inferenceButton.dataset.motherId || currentMotherId;

        applyInferenceFeedback(modal);

        if (!endpoint) {
          applyInferenceFeedback(
            modal,
            "error",
            "Endpoint inferensi tidak tersedia."
          );
          return;
        }

        if (!motherId) {
          applyInferenceFeedback(
            modal,
            "error",
            "Data ibu tidak valid untuk inferensi."
          );
          return;
        }

        setButtonLoading(inferenceButton, true);

        try {
          const { ok, data, message } = await fetchJson(endpoint, {
            method: "POST",
            body: JSON.stringify({ mother_id: Number(motherId) }),
            acceptErrorResponse: true,
          });

          if (!ok || !data || data.status !== true) {
            const errorMessage =
              (data && data.message) ||
              message ||
              "Gagal menjalankan inferensi.";
            applyInferenceFeedback(modal, "error", errorMessage);
            return;
          }

          const detailUrl = currentDetailUrl;
          const reloadFailedFeedback = {
            type: "error",
            message:
              "Inferensi berhasil, tetapi dashboard gagal diperbarui. Silakan muat ulang halaman secara manual.",
          };

          const dashboardUpdated = await reloadDashboardData();

          if (detailUrl) {
            const detailOptions = dashboardUpdated
              ? {}
              : { feedback: reloadFailedFeedback };
            await loadMotherDetail(detailUrl, detailOptions);
          } else if (!dashboardUpdated) {
            applyInferenceFeedback(
              modal,
              reloadFailedFeedback.type,
              reloadFailedFeedback.message
            );
          }
        } catch (error) {
          applyInferenceFeedback(
            modal,
            "error",
            error?.message || "Gagal menjalankan inferensi."
          );
        } finally {
          if (inferenceButton?.isConnected) {
            setButtonLoading(inferenceButton, false);
          }
        }
      });
    }
  };

  const loadMotherDetail = async (url, options = {}) => {
    if (!detailContainer || !url) {
      return;
    }

    closeDetail({ preserveUrl: true });
    currentDetailUrl = url;

    toggleIndicator(detailIndicator, true);

    try {
      const { html, ok, message } = await fetchHtml(url, {
        acceptErrorResponse: true,
      });

      if (!ok) {
        detailContainer.innerHTML = createErrorCard(
          message || "Gagal memuat detail ibu."
        );
        currentDetailUrl = null;
        currentMotherId = null;
        return;
      }

      detailContainer.innerHTML = html;
      bindDetailModal(options);
    } catch (error) {
      detailContainer.innerHTML = createErrorCard(
        error?.message || "Gagal memuat detail ibu."
      );
      currentDetailUrl = null;
      currentMotherId = null;
    } finally {
      toggleIndicator(detailIndicator, false);
    }
  };

  const bindDashboardActions = () => {
    if (!dataContainer) {
      return;
    }

    const refreshButton = dataContainer.querySelector(
      "[data-dashboard-refresh]"
    );
    if (refreshButton && !refreshButton.dataset.dashboardRefreshBound) {
      refreshButton.dataset.dashboardRefreshBound = "true";
      refreshButton.addEventListener("click", async (event) => {
        event.preventDefault();
        await reloadDashboardData();
      });
    }

    dataContainer
      .querySelectorAll("[data-mother-detail]")
      .forEach((button) => {
        if (button.dataset.motherDetailBound) {
          return;
        }

        button.dataset.motherDetailBound = "true";
        button.addEventListener("click", (event) => {
          event.preventDefault();
          const endpoint = button.dataset.motherDetail;
          if (endpoint) {
            loadMotherDetail(endpoint);
          }
        });
      });
  };

  bindDashboardActions();
  bindDetailModal();
};

const initConsultationPage = () => {
  const container = document.querySelector("[data-pakar-consultation]");
  if (!container) {
    return;
  }

  const toggleIndicator = (show) => {
    const indicator = container.querySelector("[data-consultation-indicator]");
    if (!indicator) {
      return;
    }
    if (show) {
      indicator.classList.remove("hidden");
    } else {
      indicator.classList.add("hidden");
    }
  };

  const bindConsultationInteractions = () => {
    container.querySelectorAll("[data-consultation-url]").forEach((button) => {
      button.addEventListener("click", (event) => {
        event.preventDefault();
        const endpoint = button.dataset.consultationUrl;
        loadConsultation(endpoint);
      });
    });

    const form = container.querySelector("[data-consultation-form]");
    if (form) {
      form.addEventListener("submit", (event) => {
        event.preventDefault();
        const endpoint = form.dataset.submitUrl;
        const formData = new FormData(form);
        loadConsultation(endpoint, { method: "POST", body: formData });
      });
    }
  };

  const loadConsultation = async (url, options = {}) => {
    toggleIndicator(true);

    try {
      const requestOptions = { ...options, acceptErrorResponse: true };
      const { html, message } = await fetchHtml(url, requestOptions);

      if (!html || html.trim() === "") {
        container.innerHTML = createErrorPanel(
          message || "Gagal memuat percakapan."
        );
      } else {
        container.innerHTML = html;
      }
    } catch (error) {
      container.innerHTML = createErrorPanel(
        error?.message || "Gagal memuat percakapan."
      );
    } finally {
      toggleIndicator(false);
      bindConsultationInteractions();
    }
  };

  bindConsultationInteractions();
};

document.addEventListener("DOMContentLoaded", () => {
  initSchedulePage();
  initDashboardPage();
  initConsultationPage();
});
