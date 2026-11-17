<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand-block">
            <a class="footer-logo-link" href="{{ route('home') }}" aria-label="ResFund, kembali ke beranda">
                <img class="footer-logo" src="{{ asset('assets/images/resfund-logo-white.png') }}" alt="ResFund — Research for Impact" width="220" height="74">
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
    </div>
    <div class="container footer-bottom"><span>© {{ date('Y') }} ResFund. Universitas Lampung.</span><span>Research Crowdfunding &amp; Innovation System</span></div>
</footer>
