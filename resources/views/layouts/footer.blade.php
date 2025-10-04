<footer class="border-top bg-white mt-auto site-footer" role="contentinfo">
    <div class="container py-3 small text-muted d-flex flex-wrap justify-content-between align-items-center">
        <span>&copy; {{ date('Y') }} {{ $appStoreName ?? config('app.name', 'POS') }}</span>
        <nav aria-label="Footer">
            <a href="#" class="link-secondary text-decoration-none me-3">Kebijakan Privasi</a>
            <a href="#" class="link-secondary text-decoration-none">Syarat & Ketentuan</a>
        </nav>
    </div>
</footer>
