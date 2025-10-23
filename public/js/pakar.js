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

  const closeDetail = () => {
    if (detailEscapeHandler) {
      document.removeEventListener("keydown", detailEscapeHandler);
      detailEscapeHandler = null;
    }
    if (detailContainer) {
      detailContainer.innerHTML = "";
    }
  };

  const bindDetailModal = () => {
    if (!detailContainer) {
      return;
    }

    const modal = detailContainer.querySelector('[data-modal="mother-detail"]');
    if (!modal) {
      return;
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
  };

  const loadMotherDetail = async (url) => {
    if (!detailContainer) {
      return;
    }

    closeDetail();
    toggleIndicator(detailIndicator, true);

    try {
      const { html, ok, message } = await fetchHtml(url, {
        acceptErrorResponse: true,
      });

      if (!ok) {
        detailContainer.innerHTML = createErrorCard(
          message || "Gagal memuat detail ibu."
        );
        return;
      }

      detailContainer.innerHTML = html;
      bindDetailModal();
    } catch (error) {
      detailContainer.innerHTML = createErrorCard(
        error?.message || "Gagal memuat detail ibu."
      );
    } finally {
      toggleIndicator(detailIndicator, false);
    }
  };

  const bindDashboardActions = () => {
    if (!dataContainer) {
      return;
    }

    const refreshButton = dataContainer.querySelector("[data-dashboard-refresh]");
    if (refreshButton && !refreshButton.dataset.bound) {
      refreshButton.dataset.bound = "true";
      refreshButton.addEventListener("click", async () => {
        const endpoint = refreshButton.dataset.dashboardRefresh;
        let shouldRebind = false;

        toggleIndicator(dashboardIndicator, true);

        try {
          const { html } = await fetchHtml(endpoint);
          dataContainer.innerHTML = html;
          shouldRebind = true;
        } catch (error) {
          dataContainer
            .querySelectorAll("[data-dashboard-error]")
            .forEach((element) => element.remove());

          const message = error?.message || "Gagal memuat data ibu.";
          dataContainer.insertAdjacentHTML(
            "afterbegin",
            `<div data-dashboard-error class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">${escapeHtml(
              message
            )}</div>`
          );
        } finally {
          toggleIndicator(dashboardIndicator, false);
          if (shouldRebind) {
            bindDashboardActions();
          }
        }
      });
    }

    dataContainer
      .querySelectorAll("[data-mother-detail]")
      .forEach((button) => {
        button.addEventListener("click", () => {
          const endpoint = button.dataset.motherDetail;
          loadMotherDetail(endpoint);
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
