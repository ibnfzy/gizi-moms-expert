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

if (window.htmx) {
  document.body.addEventListener("htmx:configRequest", (event) => {
    const token = getAuthToken();
    if (!token) {
      return;
    }

    const headers = event.detail?.headers;
    if (!headers) {
      return;
    }

    if (!headers.Authorization) {
      headers.Authorization = `Bearer ${token}`;
    }
  });
}

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

  const token = getAuthToken();
  if (token && config.headers && !config.headers.Authorization) {
    config.headers.Authorization = `Bearer ${token}`;
  }

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

  const token = getAuthToken();
  if (token && config.headers && !config.headers.Authorization) {
    config.headers.Authorization = `Bearer ${token}`;
  }

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

let statusModalInitialized = false;

const initStatusGuidanceModal = () => {
  if (statusModalInitialized) {
    return;
  }

  const statusModal = document.querySelector("[data-status-guidance-modal]");
  if (!statusModal) {
    return;
  }

  statusModalInitialized = true;

  const statusOverlay = statusModal.querySelector(
    "[data-status-guidance-overlay]"
  );
  let statusEscapeHandler = null;
  let statusLastFocus = null;

  const closeStatusModal = () => {
    statusModal.classList.add("hidden");
    statusModal.classList.remove("flex");
    statusModal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("overflow-hidden");

    if (statusEscapeHandler) {
      document.removeEventListener("keydown", statusEscapeHandler);
      statusEscapeHandler = null;
    }

    if (statusLastFocus && typeof statusLastFocus.focus === "function") {
      statusLastFocus.focus();
    }

    statusLastFocus = null;
  };

  const openStatusModal = (trigger) => {
    statusLastFocus =
      trigger && trigger instanceof HTMLElement
        ? trigger
        : document.activeElement instanceof HTMLElement
        ? document.activeElement
        : null;

    statusModal.classList.remove("hidden");
    statusModal.classList.add("flex");
    statusModal.setAttribute("aria-hidden", "false");
    document.body.classList.add("overflow-hidden");

    if (statusEscapeHandler) {
      document.removeEventListener("keydown", statusEscapeHandler);
    }

    const handleEscape = (event) => {
      if (event.key === "Escape") {
        event.preventDefault();
        closeStatusModal();
      }
    };

    statusEscapeHandler = handleEscape;
    document.addEventListener("keydown", handleEscape);

    const focusTarget = statusModal.querySelector(
      "[data-status-guidance-focus]"
    );
    if (focusTarget) {
      focusTarget.focus();
    }
  };

  document.addEventListener("click", (event) => {
    const closeTrigger = event.target.closest(
      "[data-status-guidance-close]"
    );
    if (closeTrigger) {
      event.preventDefault();
      closeStatusModal();
      return;
    }

    const openTrigger = event.target.closest("[data-status-guidance-open]");
    if (!openTrigger) {
      return;
    }

    event.preventDefault();
    openStatusModal(openTrigger);
  });

  if (statusOverlay) {
    statusOverlay.addEventListener("click", (event) => {
      event.preventDefault();
      closeStatusModal();
    });
  }
};

const initSchedulePage = () => {
  const page = document.querySelector("[data-pakar-schedules]");
  if (!page) {
    return;
  }

  const rowUrlTemplate = page.dataset.rowUrlTemplate || "";
  const tableUrl = page.dataset.tableUrl || "";
  const mothersUrl = page.dataset.mothersUrl || "";
  const createUrl = page.dataset.createUrl || "";

  const feedbackElement = page.querySelector("[data-schedule-feedback]");
  const filterForm = page.querySelector("[data-schedule-filter-form]");
  const resetButton = page.querySelector("[data-schedule-filter-reset]");
  const statusSelect = filterForm?.querySelector("[name='status']");

  const tableContainer = document.getElementById("schedule-table");
  const tableIndicator = document.getElementById("schedule-table-indicator");

  const evaluationModal = document.querySelector(
    "[data-schedule-evaluation-modal]"
  );
  const evaluationForm = evaluationModal?.querySelector(
    "[data-schedule-evaluation-form]"
  );
  const evaluationFeedback = evaluationModal?.querySelector(
    "[data-modal-feedback]"
  );
  const summaryField = evaluationModal?.querySelector("[data-modal-summary]");
  const followUpField = evaluationModal?.querySelector(
    "[data-modal-follow-up]"
  );
  const motherBbField = evaluationModal?.querySelector(
    "[data-modal-mother-bb]"
  );
  const motherTbField = evaluationModal?.querySelector(
    "[data-modal-mother-tb]"
  );
  const motherUmurField = evaluationModal?.querySelector(
    "[data-modal-mother-umur]"
  );
  const motherUsiaBayiField = evaluationModal?.querySelector(
    "[data-modal-mother-usia-bayi]"
  );
  const motherLaktasiField = evaluationModal?.querySelector(
    "[data-modal-mother-laktasi]"
  );
  const motherAktivitasField = evaluationModal?.querySelector(
    "[data-modal-mother-aktivitas]"
  );
  const motherAllergyField = evaluationModal?.querySelector(
    "[data-modal-mother-alergi]"
  );
  const motherSummarySection = evaluationModal?.querySelector(
    "[data-modal-mother-summary]"
  );
  const motherSummaryItems = {};

  if (evaluationModal) {
    evaluationModal
      .querySelectorAll("[data-modal-mother-item]")
      .forEach((element) => {
        const key = element.getAttribute("data-modal-mother-item");
        if (!key) {
          return;
        }

        motherSummaryItems[key] = {
          container: element,
          value: element.querySelector("[data-modal-mother-value]"),
        };
      });
  }
  const evaluationTitle = evaluationModal?.querySelector("[data-modal-title]");
  const evaluationSchedule = evaluationModal?.querySelector(
    "[data-modal-schedule]"
  );
  const evaluationOverlay = evaluationModal?.querySelector(
    "[data-modal-overlay]"
  );
  const evaluationCloseButtons =
    evaluationModal?.querySelectorAll("[data-modal-dismiss]") || [];
  const evaluationSubmitButton = evaluationForm?.querySelector(
    "[data-modal-submit]"
  );
  const evaluationIndicator = evaluationForm?.querySelector(
    "[data-modal-indicator]"
  );

  const createModal = document.querySelector("[data-schedule-create-modal]");
  const createForm = createModal?.querySelector("[data-schedule-create-form]");
  const createFeedback = createModal?.querySelector("[data-modal-feedback]");
  const createMotherSelect = createModal?.querySelector("[data-create-mother]");
  const createDatetime = createModal?.querySelector("[data-create-datetime]");
  const createStatus = createModal?.querySelector("[data-create-status]");
  const createOverlay = createModal?.querySelector("[data-modal-overlay]");
  const createCloseButtons =
    createModal?.querySelectorAll("[data-modal-dismiss]") || [];
  const createSubmitButton = createModal?.querySelector("[data-modal-submit]");
  const createIndicator = createModal?.querySelector("[data-modal-indicator]");
  const createOpenButtons =
    page.querySelectorAll("[data-schedule-create-open]") || [];

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

  const allergyHighlightClasses = [
    "border-emerald-300",
    "bg-emerald-50",
    "focus:border-emerald-400",
    "focus:ring-emerald-200",
    "dark:border-emerald-400/60",
    "dark:bg-emerald-400/10",
    "dark:focus:border-emerald-300",
    "dark:focus:ring-emerald-300/40",
  ];

  const allowedStatuses = new Set([
    "pending",
    "confirmed",
    "completed",
    "cancelled",
  ]);

  let feedbackTimeout = null;
  let evaluationEscapeHandler = null;
  let createEscapeHandler = null;
  let mothersLoaded = false;
  let mothersLoading = false;

  const toggleTableIndicator = (show) => {
    if (!tableIndicator) {
      return;
    }
    tableIndicator.classList.toggle("hidden", !show);
  };

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

  const toggleEvaluationIndicator = (show) => {
    if (evaluationIndicator) {
      evaluationIndicator.classList.toggle("hidden", !show);
    }

    if (!evaluationSubmitButton) {
      return;
    }

    evaluationSubmitButton.disabled = show;

    if (show) {
      evaluationSubmitButton.setAttribute("aria-busy", "true");
    } else {
      evaluationSubmitButton.removeAttribute("aria-busy");
    }
  };

  const clearEvaluationFeedback = () => {
    if (!evaluationFeedback) {
      return;
    }

    evaluationFeedback.classList.add("hidden");
    evaluationFeedback.textContent = "";
    evaluationFeedback.classList.remove(
      ...successFeedbackClasses,
      ...errorFeedbackClasses
    );
  };

  const showEvaluationFeedback = (type, message) => {
    if (!evaluationFeedback || !message) {
      return;
    }

    evaluationFeedback.textContent = message;
    evaluationFeedback.classList.remove("hidden");
    evaluationFeedback.classList.remove(
      ...successFeedbackClasses,
      ...errorFeedbackClasses
    );

    if (type === "success") {
      evaluationFeedback.classList.add(...successFeedbackClasses);
    } else {
      evaluationFeedback.classList.add(...errorFeedbackClasses);
    }
  };

  const formatWithUnit = (value, unit) => {
    if (value === null || value === undefined) {
      return "";
    }

    const text = value.toString().trim();
    if (text === "") {
      return "";
    }

    return unit ? `${text} ${unit}` : text;
  };

  const motherLaktasiLabels = {
    eksklusif: "Eksklusif",
    parsial: "Parsial",
  };

  const motherAktivitasLabels = {
    ringan: "Ringan",
    sedang: "Sedang",
    berat: "Berat",
  };

  const formatMotherSummaryValue = (key, rawValue) => {
    if (rawValue === null || rawValue === undefined) {
      return "";
    }

    const value = rawValue.toString().trim();
    if (value === "") {
      return "";
    }

    switch (key) {
      case "bb":
        return formatWithUnit(value, "kg");
      case "tb":
        return formatWithUnit(value, "cm");
      case "umur":
        return formatWithUnit(value, "tahun");
      case "usia_bayi_bln":
        return formatWithUnit(value, "bulan");
      case "laktasi_tipe": {
        const normalized = value.toLowerCase();
        return motherLaktasiLabels[normalized] || value;
      }
      case "aktivitas": {
        const normalized = value.toLowerCase();
        return motherAktivitasLabels[normalized] || value;
      }
      case "alergi":
        return value;
      default:
        return value;
    }
  };

  const updateMotherSummary = (motherData = {}) => {
    let hasValue = false;

    Object.entries(motherSummaryItems).forEach(([key, refs]) => {
      if (!refs?.container || !refs?.value) {
        return;
      }

      const formatted = formatMotherSummaryValue(key, motherData[key]);
      if (formatted) {
        refs.container.classList.remove("hidden");
        refs.value.textContent = formatted;
        hasValue = true;
      } else {
        refs.container.classList.add("hidden");
        refs.value.textContent = "—";
      }
    });

    if (motherSummarySection) {
      motherSummarySection.classList.toggle("hidden", !hasValue);
    }
  };

  updateMotherSummary();

  const updateAllergyFormatting = () => {
    if (!motherAllergyField) {
      return;
    }

    const value = motherAllergyField.value || "";
    const hasComma = value.includes(",");

    allergyHighlightClasses.forEach((className) => {
      motherAllergyField.classList.toggle(className, hasComma);
    });
  };

  const closeEvaluationModal = () => {
    if (!evaluationModal) {
      return;
    }

    evaluationModal.classList.add("hidden");
    evaluationModal.classList.remove("flex");
    evaluationModal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("overflow-hidden");

    if (evaluationForm) {
      evaluationForm.reset();
      delete evaluationForm.dataset.scheduleId;
      delete evaluationForm.dataset.evaluationUrl;
    }

    updateMotherSummary();
    updateAllergyFormatting();

    if (evaluationSchedule) {
      evaluationSchedule.textContent = "";
    }

    clearEvaluationFeedback();
    toggleEvaluationIndicator(false);

    if (evaluationEscapeHandler) {
      document.removeEventListener("keydown", evaluationEscapeHandler);
      evaluationEscapeHandler = null;
    }
  };

  const openEvaluationModal = (options = {}) => {
    if (!evaluationModal || !evaluationForm) {
      return;
    }

    const {
      scheduleId = "",
      evaluationUrl = "",
      name = "",
      datetime = "",
      summary = "",
      followUp = false,
      mother = {},
    } = options;

    if (!scheduleId || !evaluationUrl) {
      return;
    }

    evaluationModal.classList.remove("hidden");
    evaluationModal.classList.add("flex");
    evaluationModal.setAttribute("aria-hidden", "false");
    document.body.classList.add("overflow-hidden");

    evaluationForm.dataset.scheduleId = scheduleId;
    evaluationForm.dataset.evaluationUrl = evaluationUrl;

    clearEvaluationFeedback();
    toggleEvaluationIndicator(false);

    if (evaluationTitle) {
      evaluationTitle.textContent = name
        ? `Evaluasi • ${name}`
        : "Evaluasi Konsultasi";
    }

    if (evaluationSchedule) {
      const parts = [];
      if (name) {
        parts.push(name);
      }
      if (datetime) {
        parts.push(datetime);
      }
      evaluationSchedule.textContent = parts.join(" • ");
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

    const setFieldValue = (field, value) => {
      if (!field) {
        return;
      }

      field.value = value ?? "";
    };

    setFieldValue(motherBbField, mother.bb ?? "");
    setFieldValue(motherTbField, mother.tb ?? "");
    setFieldValue(motherUmurField, mother.umur ?? "");
    setFieldValue(motherUsiaBayiField, mother.usia_bayi_bln ?? "");
    setFieldValue(motherLaktasiField, mother.laktasi_tipe ?? "");
    setFieldValue(motherAktivitasField, mother.aktivitas ?? "");
    setFieldValue(motherAllergyField, mother.alergi ?? "");

    updateMotherSummary(mother);
    updateAllergyFormatting();

    if (evaluationEscapeHandler) {
      document.removeEventListener("keydown", evaluationEscapeHandler);
    }

    evaluationEscapeHandler = (event) => {
      if (event.key === "Escape") {
        event.preventDefault();
        closeEvaluationModal();
      }
    };

    document.addEventListener("keydown", evaluationEscapeHandler);
  };

  const toggleCreateIndicator = (show) => {
    if (!createIndicator) {
      return;
    }
    createIndicator.classList.toggle("hidden", !show);
  };

  const clearCreateFeedback = () => {
    if (!createFeedback) {
      return;
    }

    createFeedback.classList.add("hidden");
    createFeedback.textContent = "";
    createFeedback.classList.remove(
      ...successFeedbackClasses,
      ...errorFeedbackClasses
    );
  };

  const showCreateFeedback = (type, message) => {
    if (!createFeedback || !message) {
      return;
    }

    createFeedback.textContent = message;
    createFeedback.classList.remove("hidden");
    createFeedback.classList.remove(
      ...successFeedbackClasses,
      ...errorFeedbackClasses
    );

    if (type === "success") {
      createFeedback.classList.add(...successFeedbackClasses);
    } else {
      createFeedback.classList.add(...errorFeedbackClasses);
    }
  };

  const resetCreateForm = () => {
    if (createForm) {
      createForm.reset();
    }

    if (createStatus && createStatus.dataset.defaultValue) {
      createStatus.value = createStatus.dataset.defaultValue;
    }

    toggleCreateIndicator(false);
    clearCreateFeedback();

    if (createMotherSelect) {
      if (mothersLoaded) {
        createMotherSelect.disabled = false;
        createMotherSelect.value = "";
      } else {
        createMotherSelect.disabled = true;
        createMotherSelect.innerHTML =
          '<option value="">Memuat daftar ibu...</option>';
      }
    }
  };

  const fillMotherOptions = (records) => {
    if (!createMotherSelect) {
      return;
    }

    const options = ['<option value="">Pilih ibu</option>'];

    records.forEach((mother) => {
      const id = Number(mother?.id ?? 0);
      if (!Number.isInteger(id) || id <= 0) {
        return;
      }

      const nameLabel = escapeHtml(mother?.name || "Tanpa Nama");
      const emailLabel = mother?.email
        ? ` (${escapeHtml(mother.email)})`
        : "";

      options.push(`<option value="${id}">${nameLabel}${emailLabel}</option>`);
    });

    createMotherSelect.innerHTML = options.join("");
    createMotherSelect.disabled = options.length <= 1;
  };

  const loadMotherOptions = async () => {
    if (!createMotherSelect || !mothersUrl) {
      return false;
    }

    mothersLoading = true;
    createMotherSelect.disabled = true;
    createMotherSelect.innerHTML =
      '<option value="">Memuat daftar ibu...</option>';

    try {
      const { ok, data, message } = await fetchJson(mothersUrl, {
        acceptErrorResponse: true,
      });

      if (!ok || !data || data.status !== true) {
        const errorMessage =
          (data && data.message) ||
          message ||
          "Gagal memuat daftar ibu.";
        showCreateFeedback("error", errorMessage);
        createMotherSelect.innerHTML =
          '<option value="">Gagal memuat data ibu</option>';
        return false;
      }

      const records = Array.isArray(data.data) ? data.data : [];

      if (records.length === 0) {
        createMotherSelect.innerHTML =
          '<option value="">Belum ada ibu yang dapat dijadwalkan</option>';
        showCreateFeedback(
          "error",
          "Belum ada data ibu yang dapat dipilih. Tambahkan ibu terlebih dahulu."
        );
        return false;
      }

      fillMotherOptions(records);
      mothersLoaded = true;
      clearCreateFeedback();
      return true;
    } catch (error) {
      showCreateFeedback(
        "error",
        error?.message || "Gagal memuat daftar ibu."
      );
      createMotherSelect.innerHTML =
        '<option value="">Gagal memuat data ibu</option>';
      return false;
    } finally {
      mothersLoading = false;
      if (createMotherSelect && mothersLoaded) {
        createMotherSelect.disabled = false;
      }
    }
  };

  const closeCreateModal = () => {
    if (!createModal) {
      return;
    }

    createModal.classList.add("hidden");
    createModal.classList.remove("flex");
    createModal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("overflow-hidden");

    if (createEscapeHandler) {
      document.removeEventListener("keydown", createEscapeHandler);
      createEscapeHandler = null;
    }

    resetCreateForm();
  };

  const openCreateModal = () => {
    if (!createModal || !createForm) {
      return;
    }

    resetCreateForm();

    createModal.classList.remove("hidden");
    createModal.classList.add("flex");
    createModal.setAttribute("aria-hidden", "false");
    document.body.classList.add("overflow-hidden");

    if (createEscapeHandler) {
      document.removeEventListener("keydown", createEscapeHandler);
    }

    createEscapeHandler = (event) => {
      if (event.key === "Escape") {
        event.preventDefault();
        closeCreateModal();
      }
    };

    document.addEventListener("keydown", createEscapeHandler);

    if (!mothersLoaded && !mothersLoading) {
      loadMotherOptions().then((success) => {
        if (success && createMotherSelect?.isConnected) {
          createMotherSelect.disabled = false;
          createMotherSelect.focus();
        }
      });
    } else if (createMotherSelect && !createMotherSelect.disabled) {
      createMotherSelect.focus();
    }
  };

  const reloadTable = async () => {
    if (!tableContainer || !tableUrl) {
      return false;
    }

    toggleTableIndicator(true);

    try {
      const url = new URL(tableUrl, window.location.origin);
      const status = page.dataset.currentStatus || "";
      if (status) {
        url.searchParams.set("status", status);
      }

      const { html } = await fetchHtml(url.toString(), {
        acceptErrorResponse: true,
      });

      if (typeof html === "string") {
        tableContainer.innerHTML = html;
      }

      return true;
    } catch (error) {
      console.error(error);
      return false;
    } finally {
      toggleTableIndicator(false);
    }
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

  if (evaluationOverlay) {
    evaluationOverlay.addEventListener("click", (event) => {
      event.preventDefault();
      closeEvaluationModal();
    });
  }

  evaluationCloseButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      event.preventDefault();
      closeEvaluationModal();
    });
  });

  if (motherAllergyField) {
    motherAllergyField.addEventListener("input", updateAllergyFormatting);
    updateAllergyFormatting();
  }

  if (evaluationForm) {
    evaluationForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      clearEvaluationFeedback();

      const scheduleId = evaluationForm.dataset.scheduleId || "";
      const evaluationUrl = evaluationForm.dataset.evaluationUrl || "";

      if (!scheduleId || !evaluationUrl) {
        showEvaluationFeedback(
          "error",
          "Data evaluasi tidak tersedia. Silakan tutup dan buka kembali."
        );
        return;
      }

      const formData = new FormData(evaluationForm);
      const summaryValue = formData.get("evaluation[summary]");
      const summary =
        typeof summaryValue === "string" ? summaryValue.trim() : "";

      if (summary === "") {
        showEvaluationFeedback("error", "Ringkasan evaluasi wajib diisi.");
        if (summaryField) {
          summaryField.focus();
        }
        return;
      }

      const followUp = formData.get("evaluation[follow_up]") === "1";

      const getFieldValue = (name) => {
        const value = formData.get(name);
        return typeof value === "string" ? value.trim() : "";
      };

      const motherPayload = {
        bb: getFieldValue("mother[bb]"),
        tb: getFieldValue("mother[tb]"),
        umur: getFieldValue("mother[umur]"),
        usia_bayi_bln: getFieldValue("mother[usia_bayi_bln]"),
        laktasi_tipe: getFieldValue("mother[laktasi_tipe]"),
        aktivitas: getFieldValue("mother[aktivitas]"),
        alergi: getFieldValue("mother[alergi]"),
      };

      Object.keys(motherPayload).forEach((key) => {
        motherPayload[key] = motherPayload[key] === "" ? null : motherPayload[key];
      });

      const payload = {
        evaluation: {
          summary,
          follow_up: followUp,
        },
        mother: motherPayload,
      };

      toggleEvaluationIndicator(true);

      try {
        const { ok, data, message } = await fetchJson(evaluationUrl, {
          method: "PUT",
          body: JSON.stringify(payload),
          acceptErrorResponse: true,
        });

        if (!ok || !data || data.status !== true) {
          const errorMessage =
            (data && data.message) || message || "Evaluasi gagal disimpan.";
          showEvaluationFeedback("error", errorMessage);
          return;
        }

        const successMessage =
          (data && data.message) || "Evaluasi berhasil disimpan.";

        closeEvaluationModal();

        const refreshed = await refreshRow(scheduleId);

        if (refreshed) {
          showFeedback("success", successMessage);
        } else {
          showFeedback(
            "error",
            `${successMessage} Namun tabel gagal diperbarui, silakan muat ulang halaman.`
          );
        }
      } catch (error) {
        showEvaluationFeedback(
          "error",
          error?.message || "Evaluasi gagal disimpan."
        );
      } finally {
        toggleEvaluationIndicator(false);
      }
    });
  }

  if (createOverlay) {
    createOverlay.addEventListener("click", (event) => {
      event.preventDefault();
      closeCreateModal();
    });
  }

  createCloseButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      event.preventDefault();
      closeCreateModal();
    });
  });

  createOpenButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      event.preventDefault();
      openCreateModal();
    });
  });

  if (createForm) {
    createForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      clearCreateFeedback();

      if (!createUrl) {
        showCreateFeedback(
          "error",
          "Endpoint pembuatan jadwal tidak tersedia."
        );
        return;
      }

      const formData = new FormData(createForm);
      const motherIdValue = formData.get("mother_id");
      const scheduledAtValue = formData.get("scheduled_at");
      const statusValue =
        (formData.get("status") || createStatus?.dataset.defaultValue || "")
          .toString()
          .trim();

      const motherId = Number(motherIdValue);
      if (!Number.isInteger(motherId) || motherId <= 0) {
        showCreateFeedback("error", "Pilih ibu terlebih dahulu.");
        if (createMotherSelect) {
          createMotherSelect.focus();
        }
        return;
      }

      const scheduledAtRaw =
        typeof scheduledAtValue === "string" ? scheduledAtValue.trim() : "";
      if (!scheduledAtRaw) {
        showCreateFeedback("error", "Tentukan waktu konsultasi.");
        if (createDatetime) {
          createDatetime.focus();
        }
        return;
      }

      if (!allowedStatuses.has(statusValue)) {
        showCreateFeedback("error", "Status jadwal tidak valid.");
        if (createStatus) {
          createStatus.focus();
        }
        return;
      }

      let normalizedDatetime = scheduledAtRaw.replace("T", " ");
      if (normalizedDatetime.length === 16) {
        normalizedDatetime = `${normalizedDatetime}:00`;
      }

      const locationValue =
        typeof formData.get("location") === "string"
          ? formData.get("location").trim()
          : "";
      const notesValue =
        typeof formData.get("notes") === "string"
          ? formData.get("notes").trim()
          : "";

      const payload = {
        mother_id: motherId,
        scheduled_at: normalizedDatetime,
        status: statusValue,
      };

      if (locationValue !== "") {
        payload.location = locationValue;
      }

      if (notesValue !== "") {
        payload.notes = notesValue;
      }

      toggleCreateIndicator(true);
      setButtonLoading(createSubmitButton, true, "Menyimpan...");
      if (createMotherSelect) {
        createMotherSelect.disabled = true;
      }

      try {
        const { ok, data, message } = await fetchJson(createUrl, {
          method: "POST",
          body: JSON.stringify(payload),
          acceptErrorResponse: true,
        });

        if (!ok || !data || data.status !== true) {
          const errorMessage =
            (data && data.message) || message || "Gagal membuat jadwal.";
          showCreateFeedback("error", errorMessage);
          return;
        }

        const successMessage =
          (data && data.message) || "Jadwal berhasil dibuat.";

        closeCreateModal();

        const refreshed = await reloadTable();

        if (refreshed) {
          showFeedback("success", successMessage);
        } else {
          showFeedback(
            "error",
            `${successMessage} Namun tabel gagal diperbarui, silakan muat ulang halaman.`
          );
        }
      } catch (error) {
        showCreateFeedback("error", error?.message || "Gagal membuat jadwal.");
      } finally {
        toggleCreateIndicator(false);
        if (createSubmitButton?.isConnected) {
          setButtonLoading(createSubmitButton, false);
        }
        if (createMotherSelect?.isConnected) {
          createMotherSelect.disabled = !mothersLoaded;
        }
      }
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

    const getDatasetValue = (key) => {
      const value = button.dataset[key];
      return typeof value === "string" ? value.trim() : "";
    };

    openEvaluationModal({
      scheduleId,
      evaluationUrl,
      name: getDatasetValue("scheduleName"),
      datetime: getDatasetValue("scheduleDatetime"),
      summary: getDatasetValue("evaluationSummary"),
      followUp: getDatasetValue("evaluationFollowUp") === "1",
      mother: {
        bb: getDatasetValue("motherBb"),
        tb: getDatasetValue("motherTb"),
        umur: getDatasetValue("motherUmur"),
        usia_bayi_bln: getDatasetValue("motherUsiaBayi"),
        laktasi_tipe: getDatasetValue("motherLaktasi"),
        aktivitas: getDatasetValue("motherAktivitas"),
        alergi: getDatasetValue("motherAlergi"),
      },
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
      const refreshed = scheduleId ? await refreshRow(scheduleId) : true;

      if (refreshed) {
        const successMessage = message || "Data jadwal berhasil diperbarui.";
        showFeedback("success", successMessage);
      }
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

    if (message) {
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

  const updateDetailFeedback = (modal, type, message) => {
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
      updateDetailFeedback(modal, feedback.type, feedback.message);
    } else {
      updateDetailFeedback(modal);
    }

    const inferenceButton = modal.querySelector("[data-run-inference]");
    if (inferenceButton) {
      inferenceButton.addEventListener("click", async (event) => {
        event.preventDefault();

        const endpoint =
          inferenceButton.dataset.inferenceEndpoint || "";
        const motherId =
          inferenceButton.dataset.motherId || currentMotherId;

        updateDetailFeedback(modal);

        if (!endpoint) {
          updateDetailFeedback(
            modal,
            "error",
            "Endpoint inferensi tidak tersedia."
          );
          return;
        }

        if (!motherId) {
          updateDetailFeedback(
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
            updateDetailFeedback(modal, "error", errorMessage);
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
            updateDetailFeedback(
              modal,
              reloadFailedFeedback.type,
              reloadFailedFeedback.message
            );
          }
        } catch (error) {
          updateDetailFeedback(
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

    const consultationButton = modal.querySelector(
      "[data-start-consultation]"
    );

    if (consultationButton && !consultationButton.dataset.startConsultationBound) {
      consultationButton.dataset.startConsultationBound = "true";
      consultationButton.addEventListener("click", async (event) => {
        event.preventDefault();

        const endpoint =
          consultationButton.dataset.startConsultation || "";
        const motherId =
          consultationButton.dataset.motherId || currentMotherId;

        updateDetailFeedback(modal);

        if (!endpoint) {
          updateDetailFeedback(
            modal,
            "error",
            "Endpoint konsultasi tidak tersedia."
          );
          return;
        }

        if (!motherId) {
          updateDetailFeedback(
            modal,
            "error",
            "Data ibu tidak valid untuk konsultasi."
          );
          return;
        }

        setButtonLoading(consultationButton, true, "Mengalihkan...");

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
              "Gagal memulai konsultasi.";
            updateDetailFeedback(modal, "error", errorMessage);
            return;
          }

          const redirectUrl = data.redirect || data.url || null;

          if (!redirectUrl) {
            updateDetailFeedback(
              modal,
              "error",
              "Respon konsultasi tidak valid."
            );
            return;
          }

          window.location.href = redirectUrl;
        } catch (error) {
          updateDetailFeedback(
            modal,
            "error",
            error?.message || "Gagal memulai konsultasi."
          );
        } finally {
          if (consultationButton?.isConnected) {
            setButtonLoading(consultationButton, false);
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
  initStatusGuidanceModal();
  initSchedulePage();
  initDashboardPage();
  initConsultationPage();
});
