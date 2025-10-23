import { initAdminDashboard } from "./admin/dashboard.js";
import { initAdminRules } from "./admin/rules.js";
import { initAdminUsers } from "./admin/users.js";
import { initAdminMothers } from "./admin/mothers.js";

document.addEventListener("DOMContentLoaded", () => {
  initAdminDashboard();
  initAdminRules();
  initAdminUsers();
  initAdminMothers();
});
