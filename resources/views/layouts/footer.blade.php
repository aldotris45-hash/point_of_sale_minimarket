<footer class="border-top bg-white mt-auto site-footer" role="contentinfo">
    <div class="container py-3 small text-muted d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ $appStoreLogoPath ? asset($appStoreLogoPath) : asset('assets/images/logo.webp') }}"
                alt="Logo" width="24" height="24" class="border rounded bg-white p-1" />
            <span>&copy; {{ date('Y') }} {{ $appStoreName ?? config('app.name', 'POS') }}</span>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
            @if (!empty($appStoreAddress))
                <span><i class="bi bi-geo-alt me-1"></i>{{ $appStoreAddress }}</span>
            @endif
            @if (!empty($appStorePhone))
                <span><i class="bi bi-telephone me-1"></i>{{ $appStorePhone }}</span>
            @endif
        </div>
        <nav aria-label="Footer">
            <a href="#" class="link-secondary text-decoration-none me-3">Kebijakan Privasi</a>
            <a href="#" class="link-secondary text-decoration-none">Syarat & Ketentuan</a>
        </nav>
    </div>
</footer>
