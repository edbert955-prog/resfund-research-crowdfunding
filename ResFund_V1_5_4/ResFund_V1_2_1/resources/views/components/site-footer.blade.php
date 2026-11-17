<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand-block">
            <a class="brand brand-light" href="{{ route('home') }}">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 40 40"><path d="M20 4.5 34.5 12v16L20 35.5 5.5 28V12L20 4.5Z" fill="currentColor" opacity=".18"/><path d="M12 25.5c-2.2-1.3-3.1-4-2-6.3.8-1.6 2.3-2.4 4-2.4.2-3.2 2.7-5.4 5.7-5.4 3.1 0 5.5 2.4 5.5 5.4 2.6.1 4.8 2.2 4.8 4.9 0 2.2-1.4 4-3.4 4.7v3H13.2v-3.9Z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M20 12v16m-5.2-9.4 5.2 3m5.2-3-5.2 3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                <span class="brand-copy"><strong>ResFund</strong><small>Research for Impact</small></span>
            </a>
            <p>Platform pendanaan kolektif untuk mendukung penelitian akademik dan inovasi yang bermanfaat bagi masyarakat.</p>
        </div>
        <div>
            <h2>Jelajahi</h2>
            <a href="{{ route('projects.index') }}">Proyek penelitian</a>
            <a href="{{ route('how-it-works') }}">Cara kerja ResFund</a>
            <a href="{{ route('finance.public') }}">Laporan keuangan publik</a>
        </div>
        <div>
            <h2>Akun</h2>
            <a href="{{ route('login') }}">Masuk</a>
            <a href="{{ route('register') }}">Buat akun</a>
        </div>
        <div>
            <h2>Informasi</h2>
            <p class="footer-note">Prototype V1.2.1 untuk keperluan perancangan dan evaluasi UI/UX. Data proyek dan transaksi pada versi ini bersifat ilustratif.</p>
        </div>
    </div>
    <div class="container footer-bottom"><span>© {{ date('Y') }} ResFund Prototype. Universitas Lampung.</span><span>V1.2.1 · Area publik & dashboard Donatur</span></div>
</footer>
