<?= $this->extend('layouts/landing') ?>

<?= $this->section('content') ?>
<header id="siteHeader" class="fixed inset-x-0 top-0 z-50 transition duration-300 ease-in-out">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
        <a href="#hero" class="text-2xl font-extrabold tracking-tight text-white transition hover:opacity-80">GiziChain</a>
        <nav class="hidden items-center space-x-8 text-sm font-medium uppercase tracking-wide md:flex">
            <a href="#features" class="nav-link text-white/80 transition hover:text-white">Fitur</a>
            <a href="#roles" class="nav-link text-white/80 transition hover:text-white">Peran</a>
            <a href="#contact" class="nav-link text-white/80 transition hover:text-white">Kontak</a>
        </nav>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <button id="loginToggle" class="flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">
                    Login
                    <?= view('components/icon', [
                        'name' => 'chevron-down',
                        'class' => 'h-4 w-4',
                    ]) ?>
                </button>
                <div id="loginDropdown" class="absolute right-0 mt-3 hidden w-40 overflow-hidden rounded-xl bg-white/95 text-sm font-semibold text-slate-700 shadow-xl ring-1 ring-slate-200/70 backdrop-blur">
                    <a href="#" class="block px-4 py-3 transition hover:bg-slate-100">Admin</a>
                    <a href="#" class="block px-4 py-3 transition hover:bg-slate-100">Pakar</a>
                </div>
            </div>
            <a id="downloadHeader" href="#download" class="rounded-full bg-gizigreen px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-green-500/20 transition hover:scale-105 hover:shadow-green-500/40">Download APK</a>
            <button id="mobileMenuButton" class="ml-2 inline-flex items-center justify-center rounded-full border border-white/20 p-2 text-white transition hover:bg-white/10 md:hidden">
                <?= view('components/icon', [
                    'name' => 'menu',
                    'class' => 'h-6 w-6',
                ]) ?>
            </button>
        </div>
    </div>
    <div id="mobileMenu" class="mx-auto hidden max-w-7xl space-y-2 px-4 pb-6 sm:px-6 lg:px-8 md:hidden">
        <a href="#features" class="block rounded-lg px-4 py-3 text-sm font-semibold text-white/80 transition hover:bg-white/10 hover:text-white">Fitur</a>
        <a href="#roles" class="block rounded-lg px-4 py-3 text-sm font-semibold text-white/80 transition hover:bg-white/10 hover:text-white">Peran</a>
        <a href="#contact" class="block rounded-lg px-4 py-3 text-sm font-semibold text-white/80 transition hover:bg-white/10 hover:text-white">Kontak</a>
        <div class="rounded-xl bg-white/10 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-white/60">Login Sebagai</p>
            <div class="mt-3 space-y-2 text-sm font-semibold text-white">
                <a href="#" class="block rounded-lg bg-white/20 px-4 py-2 transition hover:bg-white/30">Admin</a>
                <a href="#" class="block rounded-lg bg-white/20 px-4 py-2 transition hover:bg-white/30">Pakar</a>
            </div>
        </div>
    </div>
</header>

<main id="hero" class="relative flex min-h-screen flex-col overflow-hidden bg-gradient-to-br from-giziblue to-giziblueLight pt-32">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.35),_transparent_60%)]"></div>
    <div class="relative mx-auto flex w-full max-w-7xl flex-1 flex-col justify-center px-4 pb-16 sm:px-6 lg:flex-row lg:items-center lg:px-8">
        <div class="max-w-2xl space-y-8 text-white">
            <span class="inline-flex items-center rounded-full bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-white/80">Solusi Digital Gizi</span>
            <h1 class="text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">Sistem Pakar Kebutuhan Gizi Ibu Menyusui</h1>
            <p class="text-lg text-white/80 sm:text-xl">Bantu pemenuhan gizi optimal bagi ibu dan bayi dengan metode Forward Chaining.</p>
            <div class="flex flex-col gap-4 sm:flex-row">
                <a href="#features" class="group inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-semibold text-giziblue transition hover:-translate-y-1 hover:shadow-xl hover:shadow-white/30">
                    Mulai Sekarang
                    <?= view('components/icon', [
                        'name' => 'arrow-right',
                        'class' => 'ml-3 h-4 w-4 transition-transform group-hover:translate-x-1',
                    ]) ?>
                </a>
                <a href="#download" class="inline-flex items-center justify-center rounded-full border border-white/60 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                    Download Aplikasi Android
                </a>
            </div>
            <div class="flex flex-wrap gap-6 text-white/80">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/20 text-2xl">💡</span>
                    <div>
                        <p class="text-sm font-semibold">Metode Forward Chaining</p>
                        <p class="text-xs text-white/70">Akurat dan dapat dipercaya</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/20 text-2xl">📱</span>
                    <div>
                        <p class="text-sm font-semibold">Aplikasi Mobile</p>
                        <p class="text-xs text-white/70">Praktis dimanapun Anda berada</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-12 w-full max-w-lg lg:mt-0 lg:max-w-xl">
            <div class="relative rounded-[2.5rem] bg-white/10 p-1 shadow-2xl shadow-blue-900/40 backdrop-blur">
                <div class="rounded-[2rem] bg-white/90 p-6">
                    <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=900&q=80" alt="Ilustrasi ibu menyusui" class="h-full w-full rounded-2xl object-cover shadow-lg shadow-blue-500/20" />
                </div>
                <div class="pointer-events-none absolute -bottom-10 left-1/2 w-72 -translate-x-1/2 rounded-3xl bg-white/20 p-4 text-center text-xs font-semibold uppercase tracking-wide text-white/80 backdrop-blur">
                    Didukung pakar gizi berlisensi
                </div>
            </div>
        </div>
    </div>
</main>

<section id="features" class="bg-slate-50 py-24 text-slate-900">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Fitur Unggulan GiziChain</h2>
            <p class="mt-4 text-lg text-slate-600">Dirancang untuk membantu ibu menyusui memahami kebutuhan gizi secara menyeluruh dengan dukungan data dan pakar.</p>
        </div>
        <div class="mt-16 grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
            <div class="group rounded-xl bg-white p-8 shadow-lg shadow-slate-200 transition hover:-translate-y-2 hover:scale-[1.01] hover:shadow-2xl">
                <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-giziblue/10 text-3xl">🔍</div>
                <h3 class="text-xl font-semibold">Analisis Gizi Otomatis</h3>
                <p class="mt-3 text-sm leading-relaxed text-slate-600">Algoritma cerdas mengidentifikasi kebutuhan nutrisi berdasarkan data ibu dan bayi secara real-time.</p>
            </div>
            <div class="group rounded-xl bg-white p-8 shadow-lg shadow-slate-200 transition hover:-translate-y-2 hover:scale-[1.01] hover:shadow-2xl">
                <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-3xl">📊</div>
                <h3 class="text-xl font-semibold">Rekomendasi Menu Sehat</h3>
                <p class="mt-3 text-sm leading-relaxed text-slate-600">Ratusan kombinasi menu sehat yang disesuaikan dengan preferensi, alergi, dan target nutrisi harian.</p>
            </div>
            <div class="group rounded-xl bg-white p-8 shadow-lg shadow-slate-200 transition hover:-translate-y-2 hover:scale-[1.01] hover:shadow-2xl">
                <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-fuchsia-100 text-3xl">💬</div>
                <h3 class="text-xl font-semibold">Konsultasi Langsung dengan Pakar</h3>
                <p class="mt-3 text-sm leading-relaxed text-slate-600">Terhubung dengan ahli gizi profesional untuk memvalidasi rekomendasi dan menjawab pertanyaan Anda.</p>
            </div>
        </div>
    </div>
</section>

<section id="roles" class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl">Tiga Peran dalam Sistem</h2>
            <p class="mt-4 text-lg text-slate-600">Kolaborasi yang memastikan setiap rekomendasi gizi tersampaikan tepat sasaran.</p>
        </div>
        <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-3xl border border-blue-100 bg-blue-50 p-8 transition hover:-translate-y-2 hover:shadow-2xl">
                <div class="text-4xl">🧑‍💼</div>
                <h3 class="mt-6 text-xl font-semibold text-blue-900">Admin</h3>
                <p class="mt-3 text-sm leading-relaxed text-blue-800/80">Kelola data pengguna, aturan inferensi, dan menjaga sistem tetap mutakhir.</p>
            </div>
            <div class="rounded-3xl border border-purple-100 bg-purple-50 p-8 transition hover:-translate-y-2 hover:shadow-2xl">
                <div class="text-4xl">👩‍⚕️</div>
                <h3 class="mt-6 text-xl font-semibold text-purple-900">Pakar Gizi</h3>
                <p class="mt-3 text-sm leading-relaxed text-purple-800/80">Validasi hasil inferensi serta memberikan saran personal sesuai kondisi ibu dan bayi.</p>
            </div>
            <div class="rounded-3xl border border-pink-100 bg-pink-50 p-8 transition hover:-translate-y-2 hover:shadow-2xl">
                <div class="text-4xl">🤱</div>
                <h3 class="mt-6 text-xl font-semibold text-pink-900">Ibu Menyusui</h3>
                <p class="mt-3 text-sm leading-relaxed text-pink-800/80">Menerima panduan menu sehat, pemantauan nutrisi, dan pengingat konsumsi harian.</p>
            </div>
        </div>
    </div>
</section>

<section id="download" class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-500 to-indigo-700 py-24 text-white">
    <div class="absolute inset-0 opacity-30" aria-hidden="true">
        <div class="absolute -top-32 -left-32 h-64 w-64 rounded-full bg-white/20 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
    </div>
    <div class="relative mx-auto max-w-5xl rounded-3xl border border-white/10 bg-white/10 px-8 py-16 text-center shadow-2xl backdrop-blur-lg sm:px-16">
        <p class="text-sm font-semibold uppercase tracking-[0.4em] text-white/70">#BersamaGiziChain</p>
        <h2 class="mt-6 text-4xl font-bold sm:text-5xl">Siap bantu ibu menyusui Indonesia lebih sehat?</h2>
        <p class="mt-4 text-lg text-white/80">Gabung bersama ribuan ibu menyusui yang merasakan manfaat panduan gizi personal dari GiziChain.</p>
        <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="#" class="inline-flex items-center justify-center rounded-full bg-gizigreen px-8 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-500/30 transition hover:-translate-y-1 hover:shadow-emerald-500/50">Download Aplikasi Android</a>
            <a href="#" class="inline-flex items-center justify-center rounded-full border border-white/70 px-8 py-3 text-base font-semibold text-white transition hover:bg-white/10">Login Pakar / Admin</a>
        </div>
    </div>
</section>

<section id="contact" class="bg-white py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2">
            <div>
                <h3 class="text-3xl font-bold text-slate-900">Butuh bantuan lebih lanjut?</h3>
                <p class="mt-4 text-base text-slate-600">Tim kami siap membantu Anda memahami fitur GiziChain untuk mendukung perjalanan menyusui yang lebih sehat.</p>
                <div class="mt-8 grid gap-6 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 p-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</p>
                        <p class="mt-2 text-lg font-semibold text-slate-900">support@gizichain.id</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Telepon</p>
                        <p class="mt-2 text-lg font-semibold text-slate-900">+62 812-3456-7890</p>
                    </div>
                </div>
            </div>
            <div class="rounded-3xl bg-slate-50 p-10 shadow-xl">
                <form class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700">Nama Lengkap</label>
                        <input type="text" id="name" name="name" placeholder="Contoh: Dina Pratiwi" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/30" />
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Email</label>
                        <input type="email" id="email" name="email" placeholder="nama@domain.com" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/30" />
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-semibold text-slate-700">Pesan</label>
                        <textarea id="message" name="message" rows="4" placeholder="Ceritakan kebutuhan Anda" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/30"></textarea>
                    </div>
                    <button type="submit" class="w-full rounded-full bg-giziblue px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:-translate-y-1 hover:shadow-blue-500/50">Kirim Pesan</button>
                </form>
            </div>
        </div>
    </div>
</section>

<footer class="bg-[#1f2937] py-10 text-sm text-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
        <p>© 2025 GiziChain. Semua Hak Dilindungi.</p>
        <div class="flex items-center gap-4">
            <a href="#" class="rounded-full bg-white/10 p-2 transition hover:bg-white/20" aria-label="Facebook">
                <?= view('components/icon', [
                    'name' => 'facebook',
                    'class' => 'h-5 w-5',
                ]) ?>
            </a>
            <a href="#" class="rounded-full bg-white/10 p-2 transition hover:bg-white/20" aria-label="Instagram">
                <?= view('components/icon', [
                    'name' => 'instagram',
                    'class' => 'h-5 w-5',
                ]) ?>
            </a>
            <a href="#" class="rounded-full bg-white/10 p-2 transition hover:bg-white/20" aria-label="LinkedIn">
                <?= view('components/icon', [
                    'name' => 'linkedin',
                    'class' => 'h-5 w-5',
                ]) ?>
            </a>
        </div>
    </div>
</footer>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const header = document.getElementById('siteHeader');
        const loginToggle = document.getElementById('loginToggle');
        const loginDropdown = document.getElementById('loginDropdown');
        const mobileButton = document.getElementById('mobileMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');
        const downloadHeader = document.getElementById('downloadHeader');

        const setHeaderState = () => {
            const scrolled = window.scrollY > 10;
            header.classList.toggle('bg-white/95', scrolled);
            header.classList.toggle('shadow-lg', scrolled);
            header.classList.toggle('backdrop-blur-xl', scrolled);

            const links = header.querySelectorAll('.nav-link');
            links.forEach(link => {
                link.classList.toggle('text-slate-800', scrolled);
                link.classList.toggle('text-white/80', !scrolled);
                link.classList.toggle('hover:text-giziblue', scrolled);
                link.classList.toggle('hover:text-white', !scrolled);
            });

            const logo = header.querySelector('a[href="#hero"]');
            logo.classList.toggle('text-slate-900', scrolled);
            logo.classList.toggle('text-white', !scrolled);

            if (scrolled) {
                loginToggle.classList.remove('border-white/20', 'text-white', 'hover:bg-white/10');
                loginToggle.classList.add('border-slate-200', 'text-slate-800', 'hover:bg-slate-100');
                mobileButton?.classList.remove('border-white/20', 'text-white', 'hover:bg-white/10');
                mobileButton?.classList.add('border-slate-200', 'text-slate-800', 'hover:bg-slate-100');
                downloadHeader.classList.add('shadow-green-500/30');
            } else {
                loginToggle.classList.add('border-white/20', 'text-white', 'hover:bg-white/10');
                loginToggle.classList.remove('border-slate-200', 'text-slate-800', 'hover:bg-slate-100');
                mobileButton?.classList.add('border-white/20', 'text-white', 'hover:bg-white/10');
                mobileButton?.classList.remove('border-slate-200', 'text-slate-800', 'hover:bg-slate-100');
                downloadHeader.classList.remove('shadow-green-500/30');
            }
        };

        setHeaderState();
        window.addEventListener('scroll', setHeaderState);

        document.addEventListener('click', (event) => {
            if (loginToggle.contains(event.target)) {
                event.preventDefault();
                loginDropdown.classList.toggle('hidden');
            } else if (!loginDropdown.contains(event.target)) {
                loginDropdown.classList.add('hidden');
            }
        });

        mobileButton?.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    })();
</script>
<?= $this->endSection() ?>
