# Rekap Fitur

## Website
- **Autentikasi berbasis peran** – Halaman login memverifikasi kredensial, menyimpan sesi pengguna, dan mengarahkan admin/pakar ke dashboard masing-masing, lengkap dengan tampilan form yang responsif dan penanganan pesan kesalahan.【F:app/Controllers/AuthController.php†L9-L68】【F:app/Views/auth/login.php†L1-L87】
- **Dashboard admin interaktif** – Admin dapat memantau statistik pengguna, rule, dan inferensi sekaligus melihat ringkasan rule terbaru yang dimuat ulang secara dinamis melalui modul JavaScript khusus.【F:app/Controllers/AdminDashboardController.php†L17-L92】【F:app/Views/admin/dashboard.php†L4-L84】【F:public/js/admin.js†L85-L210】
- **Manajemen rule melalui modal** – Halaman manajemen rule menyediakan tabel CRUD, validasi JSON, serta aksi tambah, edit, dan hapus yang terhubung langsung ke API menggunakan permintaan AJAX.【F:app/Views/admin/rules.php†L4-L131】【F:public/js/admin.js†L213-L469】
- **Dashboard pakar dengan ringkasan status** – Pakar melihat ringkasan status ibu menyusui, tabel data, dan detail inferensi terbaru dalam modal yang diisi otomatis dari endpoint API.【F:app/Controllers/PakarDashboardController.php†L13-L45】【F:app/Views/pakar/dashboard.php†L4-L247】【F:public/js/pakar.js†L62-L177】
- **Percakapan konsultasi realtime** – Halaman konsultasi pakar menampilkan daftar sesi, percakapan, serta form pengiriman pesan yang memperbarui UI secara instan sambil menyinkronkan data ke server.【F:app/Controllers/PakarConsultationController.php†L18-L138】【F:app/Views/pakar/consultation.php†L9-L161】【F:public/js/pakar.js†L178-L378】

## API
- **Layanan autentikasi JWT** – Endpoint login menghasilkan token JWT, sedangkan register (khusus admin) dan profil `me` memanfaatkan helper auth untuk mengamankan identitas pengguna.【F:app/Controllers/Api/AuthController.php†L20-L143】【F:app/Config/Routes.php†L16-L24】
- **Inferensi kebutuhan gizi** – Endpoint `inference/run` mengubah data ibu menjadi fakta, menjalankan mesin forward chaining, menyimpan hasil, dan mengembalikan fakta, rule yang aktif, serta rekomendasi.【F:app/Controllers/Api/InferenceController.php†L19-L172】【F:app/Config/Routes.php†L16-L18】
- **Akses data ibu menyusui** – Endpoint daftar dan detail ibu menampilkan profil terformat termasuk status inferensi terkini bagi pengguna berperan pakar/ibu.【F:app/Controllers/Api/MotherController.php†L15-L60】【F:app/Config/Routes.php†L26-L28】
- **Pembuatan konsultasi terproteksi** – Endpoint konsultasi memvalidasi kepemilikan ibu, pakar tujuan, serta status yang diperbolehkan sebelum menyimpan dan mengembalikan payload terformat.【F:app/Controllers/Api/ConsultationController.php†L20-L148】【F:app/Config/Routes.php†L26-L33】
- **Percakapan konsultasi** – Endpoint pesan hanya dapat diakses peserta sesi dan mendukung pengambilan maupun pengiriman pesan dengan cap waktu yang terformat.【F:app/Controllers/Api/MessageController.php†L18-L186】【F:app/Config/Routes.php†L26-L33】
- **Manajemen rule via API** – Endpoint publik `api/rules` memberikan operasi daftar, tambah, ubah, dan hapus dengan validasi JSON serta umpan balik status yang jelas.【F:app/Controllers/Api/RuleController.php†L13-L205】【F:app/Config/Routes.php†L37-L42】
