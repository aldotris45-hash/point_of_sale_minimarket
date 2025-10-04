<header class="navbar navbar-expand-lg bg-white border-bottom sticky-top" role="banner">
    <div class="container-fluid">
        <button class="btn btn-outline-secondary d-lg-none me-2" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Toggling sidebar">
            <i class="bi bi-list"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
            <img src="{{ asset('assets/images/logo.webp') }}" alt="Logo" width="32" height="32" />
            <span class="fw-semibold">{{ $appStoreName ?? config('app.name', 'POS') }}</span>
        </a>

        <div class="ms-auto d-flex align-items-center gap-2">
            @auth
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" type="button"
                        id="userMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu pengguna">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                        <li>
                            <h6 class="dropdown-header">Akun</h6>
                        </li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth

            @guest
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                </a>
            @endguest
        </div>
    </div>
</header>
