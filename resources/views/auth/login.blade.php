<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Masuk - POS Mutiara Kasih</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('assets/vendor/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons-1.13.1/bootstrap-icons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/custom-css.css') }}" rel="stylesheet" />
</head>

<body>
    <main class="container py-5">
        <section class="row justify-content-center">
            <div class="col-md-5">
                <header class="mb-4 text-center">
                    <img src="{{ asset('assets/images/logo.webp') }}" alt="Logo" width="80" height="80">
                    <h1 class="h4 mt-3">Masuk</h1>
                </header>

                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif

                <form action="{{ url('/login') }}" method="POST" novalidate>
                    @csrf
                    <fieldset class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email"
                            class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                            autocomplete="email" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </fieldset>

                    <fieldset class="mb-3">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <input id="password" name="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" autocomplete="current-password"
                            required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </fieldset>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1"
                            {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <script src="{{ asset('assets/vendor/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
