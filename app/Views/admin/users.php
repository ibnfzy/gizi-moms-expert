<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8" data-admin-users data-base-endpoint="<?= site_url('api/admin/users') ?>"
  data-notification-id="admin-users-notification">
  <div id="admin-users-notification"
    class="hidden rounded-lg border border-transparent px-4 py-3 text-sm font-medium transition-all duration-200"
    role="status" aria-live="polite"></div>

  <section class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-slate-100">Manajemen Pengguna</h1>
        <p class="text-sm text-gray-600 dark:text-slate-400">Atur akses admin dan pakar yang dapat menggunakan sistem.
        </p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <button type="button"
          class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-400"
          data-open-create>
          <span class="text-lg" aria-hidden="true">＋</span>
          Tambah Pengguna
        </button>
      </div>
    </div>

    <div
      class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm shadow-slate-100 ring-1 ring-gray-100 dark:border-black/70 dark:bg-slate-950/70 dark:shadow-black/40 dark:ring-black/60">
      <div class="overflow-x-auto">
        <table class="min-w-full border-collapse border border-black/40 text-left text-sm dark:border-gray-300">
          <thead
            class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-slate-950/70 dark:text-slate-200">
            <tr>
              <th scope="col" class="border border-black/40 px-6 py-3 dark:border-gray-300">Nama</th>
              <th scope="col" class="border border-black/40 px-6 py-3 dark:border-gray-300">Email</th>
              <th scope="col" class="border border-black/40 px-6 py-3 dark:border-gray-300">Peran</th>
              <th scope="col" class="border border-black/40 px-6 py-3 dark:border-gray-300">Dibuat</th>
              <th scope="col" class="border border-black/40 px-6 py-3 text-right dark:border-gray-300">Aksi</th>
            </tr>
          </thead>
          <tbody data-table-body class="text-gray-700 dark:text-slate-200">
            <tr>
              <td colspan="5" class="border border-black/40 px-6 py-8 dark:border-gray-300">
                <div class="flex items-center justify-center gap-3 text-sm text-gray-500 dark:text-slate-400">
                  <div class="h-6 w-6 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600"
                    aria-hidden="true"></div>
                  Memuat data pengguna...
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<!-- Create Modal -->
<div id="adminUserCreateModal"
  class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4 py-6 dark:bg-black/70">
  <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-xl dark:border-slate-200/40 dark:bg-slate-950 dark:text-slate-100">
    <form id="adminUserCreateForm" class="space-y-6">
      <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-slate-200/30">
        <div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Tambah Pengguna</h2>
          <p class="text-sm text-gray-500 dark:text-slate-400">Buat akun admin atau pakar baru.</p>
        </div>
        <button type="button" class="text-gray-400 hover:text-gray-600 dark:text-slate-300 dark:hover:text-slate-100"
          data-close-create>
          <span class="sr-only">Tutup</span>
          &times;
        </button>
      </div>
      <div class="space-y-4 px-6">
        <div class="space-y-2">
          <label for="userCreateName" class="text-sm font-medium text-gray-700 dark:text-slate-200">Nama</label>
          <input type="text" id="userCreateName" name="name"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-200/50 dark:bg-slate-900 dark:text-slate-100"
            placeholder="Nama lengkap pengguna" required>
        </div>
        <div class="space-y-2">
          <label for="userCreateEmail" class="text-sm font-medium text-gray-700 dark:text-slate-200">Email</label>
          <input type="email" id="userCreateEmail" name="email"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-200/50 dark:bg-slate-900 dark:text-slate-100"
            placeholder="contoh@email.com" required>
        </div>
        <div class="space-y-2">
          <label for="userCreateRole" class="text-sm font-medium text-gray-700 dark:text-slate-200">Peran</label>
          <select id="userCreateRole" name="role"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-200/50 dark:bg-slate-900 dark:text-slate-100"
            required>
            <option value="admin">Admin</option>
            <option value="pakar">Pakar</option>
          </select>
        </div>
        <div class="space-y-2">
          <label for="userCreatePassword" class="text-sm font-medium text-gray-700 dark:text-slate-200">Password</label>
          <input type="password" id="userCreatePassword" name="password"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-200/50 dark:bg-slate-900 dark:text-slate-100"
            placeholder="Minimal 8 karakter" required>
        </div>
      </div>
      <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-slate-200/30">
        <button type="button"
          class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-200/30 dark:text-slate-300 dark:hover:bg-slate-900/50"
          data-close-create>Batal</button>
        <button type="submit"
          class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-400">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div id="adminUserEditModal"
  class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4 py-6 dark:bg-black/70">
  <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-xl dark:border-slate-200/40 dark:bg-slate-950 dark:text-slate-100">
    <form id="adminUserEditForm" class="space-y-6">
      <input type="hidden" id="userEditId" name="id">
      <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-slate-200/30">
        <div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Perbarui Pengguna</h2>
          <p class="text-sm text-gray-500 dark:text-slate-400">Ubah informasi nama, email, atau peran pengguna.</p>
        </div>
        <button type="button" class="text-gray-400 hover:text-gray-600 dark:text-slate-300 dark:hover:text-slate-100"
          data-close-edit>
          <span class="sr-only">Tutup</span>
          &times;
        </button>
      </div>
      <div class="space-y-4 px-6">
        <div class="space-y-2">
          <label for="userEditName" class="text-sm font-medium text-gray-700 dark:text-slate-200">Nama</label>
          <input type="text" id="userEditName" name="name"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-200/50 dark:bg-slate-900 dark:text-slate-100"
            required>
        </div>
        <div class="space-y-2">
          <label for="userEditEmail" class="text-sm font-medium text-gray-700 dark:text-slate-200">Email</label>
          <input type="email" id="userEditEmail" name="email"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-200/50 dark:bg-slate-900 dark:text-slate-100"
            required>
        </div>
        <div class="space-y-2">
          <label for="userEditRole" class="text-sm font-medium text-gray-700 dark:text-slate-200">Peran</label>
          <select id="userEditRole" name="role"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-200/50 dark:bg-slate-900 dark:text-slate-100"
            required>
            <option value="admin">Admin</option>
            <option value="pakar">Pakar</option>
          </select>
        </div>
      </div>
      <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-slate-200/30">
        <button type="button"
          class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-200/30 dark:text-slate-300 dark:hover:bg-slate-900/50"
          data-close-edit>Batal</button>
        <button type="submit"
          class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-400">Simpan
          Perubahan</button>
      </div>
    </form>
  </div>
</div>

<!-- Password Modal -->
<div id="adminUserPasswordModal"
  class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4 py-6 dark:bg-black/70">
  <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-xl dark:border-slate-200/40 dark:bg-slate-950 dark:text-slate-100">
    <form id="adminUserPasswordForm" class="space-y-6">
      <input type="hidden" id="userPasswordId" name="id">
      <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-slate-200/30">
        <div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Atur Ulang Password</h2>
          <p class="text-sm text-gray-500 dark:text-slate-400">Tetapkan password baru untuk pengguna ini.</p>
        </div>
        <button type="button" class="text-gray-400 hover:text-gray-600 dark:text-slate-300 dark:hover:text-slate-100"
          data-close-password>
          <span class="sr-only">Tutup</span>
          &times;
        </button>
      </div>
      <div class="space-y-4 px-6">
        <div class="space-y-2">
          <label for="userPasswordInput" class="text-sm font-medium text-gray-700 dark:text-slate-200">Password
            Baru</label>
          <input type="password" id="userPasswordInput" name="password"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-200/50 dark:bg-slate-900 dark:text-slate-100"
            placeholder="Minimal 8 karakter" required>
        </div>
      </div>
      <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-slate-200/30">
        <button type="button"
          class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-200/30 dark:text-slate-300 dark:hover:bg-slate-900/50"
          data-close-password>Batal</button>
        <button type="submit"
          class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-400">Perbarui
          Password</button>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('js/admin.js') ?>"></script>
<?= $this->endSection() ?>