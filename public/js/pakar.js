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
  initDashboardPage();
  initConsultationPage();
});
