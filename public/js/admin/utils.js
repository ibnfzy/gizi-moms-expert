export const escapeHtml = (value) => {
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

export const showNotification = (target, type, message, timeout = 4000) => {
  const element =
    typeof target === "string" ? document.getElementById(target) : target;
  if (!element) {
    return;
  }

  if (!message) {
    element.classList.add("hidden");
    element.textContent = "";
    return;
  }

  const baseClass =
    "rounded-lg border px-4 py-3 text-sm font-medium shadow-sm transition-all duration-200";
  const variantClass =
    type === "success"
      ? "bg-green-100 text-green-800 border-green-200 dark:bg-emerald-500/20 dark:text-emerald-200 dark:border-emerald-400/40"
      : "bg-red-100 text-red-800 border-red-200 dark:bg-rose-500/20 dark:text-rose-200 dark:border-rose-400/40";

  element.className = `${baseClass} ${variantClass}`;
  element.textContent = message;
  element.classList.remove("hidden");

  if (timeout) {
    window.clearTimeout(element.__hideTimer);
    element.__hideTimer = window.setTimeout(() => {
      element.classList.add("hidden");
    }, timeout);
  }
};

export const getAuthToken = () => {
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

export const fetchJson = async (url, options = {}) => {
  const headers = {
    Accept: "application/json",
    ...(options.headers || {}),
  };

  const token = getAuthToken();
  if (token && !headers.Authorization) {
    headers.Authorization = `Bearer ${token}`;
  }

  const config = {
    ...options,
    headers,
  };

  const response = await fetch(url, config);
  const contentType = response.headers.get("content-type") || "";
  let payload = null;

  if (contentType.includes("application/json")) {
    payload = await response.json();
  } else {
    const text = await response.text();
    try {
      payload = JSON.parse(text);
    } catch (error) {
      payload = { message: text };
    }
  }

  if (!response.ok || (payload && payload.status === false)) {
    const errorMessage = payload?.message || "Permintaan gagal diproses.";
    throw new Error(errorMessage);
  }

  return payload ?? {};
};

export const createSpinnerRow = (colspan, message) => `
    <tr>
        <td colspan="${colspan}" class="border border-black/40 px-6 py-8 dark:border-gray-300">
            <div class="flex items-center justify-center gap-3 text-sm text-gray-500 dark:text-slate-400">
                <div class="h-6 w-6 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
                ${escapeHtml(message)}
            </div>
        </td>
    </tr>
`;
