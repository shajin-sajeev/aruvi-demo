<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — Aruvi on the Cliff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root { --teal:#008C95; --teal-hover:#00737b; --deep:#073b3f; --ink:#0b2224; }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
        }

        /* ── Shell ───────────────────────────────── */
        .auth-shell { display: flex; width: 100%; min-height: 100vh; }

        /* ── Left panel ───────────────────────────── */
        .auth-left {
            flex: 0 0 42%;
            background: linear-gradient(160deg, var(--deep) 0%, #0a5560 55%, var(--teal) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }
        /* subtle decorative circles */
        .auth-left::before {
            content: '';
            position: absolute;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            top: -80px; right: -80px;
            pointer-events: none;
        }
        .auth-left::after {
            content: '';
            position: absolute;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            bottom: -60px; left: -60px;
            pointer-events: none;
        }
        .auth-left-overlay { display: none; } /* not needed */

        .auth-left-content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        /* Logo + brand image block */
        .auth-brand-block {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .auth-logo-img {
            height: 48px;
            width: auto;
            object-fit: contain;
            display: block;
        }
        .auth-brand-img {
            width: 100%;
            max-width: 260px;
            height: auto;
            object-fit: contain;
            display: block;
            border-radius: 8px;
        }

        .auth-left-content h2 {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.3;
        }
        .auth-left-content p {
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            line-height: 1.65;
            margin: 0;
            max-width: 300px;
        }
        .auth-features {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .auth-features li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255,255,255,0.8);
            font-size: 0.82rem;
            margin-bottom: 0.4rem;
        }
        .auth-features li i { color: #2fbf9f; font-size: 0.82rem; }
        .auth-left-footer {
            color: rgba(255,255,255,0.32);
            font-size: 0.7rem;
        }

        /* ── Right panel — form ───────────────────── */
        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
            background: #fff;
            overflow-y: auto;
        }
        .auth-form-wrap { width: 100%; max-width: 420px; }

        .form-eyebrow {
            color: var(--teal);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            display: block;
            margin-bottom: 0.35rem;
        }
        .auth-form-wrap h2 {
            font-family: 'Playfair Display', serif;
            color: var(--ink);
            font-size: 1.85rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }
        .auth-subtitle { color: #6b8a8c; font-size: 0.88rem; margin-bottom: 1.75rem; }

        /* Fields */
        .field-wrap { position: relative; margin-bottom: 1.2rem; }
        .field-wrap label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 0.35rem;
        }

        /* Icon + toggle scoped to input wrapper — never shifts when error text appears below */
        .field-input-wrap {
            position: relative;
        }
        .field-input-wrap .field-icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ab5b7;
            font-size: 0.9rem;
            pointer-events: none;
        }
        .field-input-wrap .toggle-pw {
            position: absolute;
            right: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            color: #9ab5b7;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .field-input-wrap .toggle-pw:hover { color: var(--teal); }
        .field-wrap input {
            width: 100%;
            padding: 0.7rem 2.6rem 0.7rem 2.6rem;
            border: 1.5px solid #dde8e9;
            border-radius: 10px;
            font-size: 0.92rem;
            color: var(--ink);
            background: #f8fdfd;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .field-wrap input:focus {
            border-color: var(--teal);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0,140,149,0.1);
        }
        .field-wrap input.is-invalid { border-color: #dc3545; background: #fff8f8; }
        .invalid-feedback { display: block; color: #dc3545; font-size: 0.78rem; margin-top: 0.3rem; }
        /* Suppress Bootstrap's built-in validation icon that conflicts with our custom icons */
        .field-input-wrap input.is-invalid,
        .field-input-wrap input.is-valid {
            background-image: none !important;
            padding-right: 2.6rem !important;
        }

        .auth-row {
            display: flex;
            align-items: center;
            margin-bottom: 1.4rem;
            font-size: 0.85rem;
        }
        .auth-row label {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            color: #6b8a8c;
            cursor: pointer;
        }
        .auth-row input[type="checkbox"] { accent-color: var(--teal); }

        .btn-auth {
            width: 100%;
            padding: 0.8rem;
            background: var(--teal);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.25s;
        }
        .btn-auth:hover {
            background: var(--teal-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0,140,149,0.25);
        }
        .auth-switch {
            text-align: center;
            margin-top: 1.4rem;
            font-size: 0.875rem;
            color: #6b8a8c;
        }
        .auth-switch a { color: var(--teal); font-weight: 600; text-decoration: none; }
        .auth-switch a:hover { text-decoration: underline; }

        .auth-alert {
            background: #fff2f2;
            border: 1px solid #fcc;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            color: #b91c1c;
            font-size: 0.83rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 1.2rem;
        }

        @media (max-width: 767.98px) {
            .auth-left { display: none; }
            .auth-right { padding: 2rem 1.25rem; background: #f0fafa; }
            .auth-form-wrap {
                background: #fff;
                border-radius: 18px;
                padding: 2rem 1.5rem;
                box-shadow: 0 8px 30px rgba(7,59,63,0.08);
            }
        }
    </style>
</head>
<body>
<div class="auth-shell">

    {{-- Left: brand image fills full panel --}}
    <div class="auth-left">
        <div class="auth-left-overlay"></div>

        <div class="auth-left-content">
            {{-- Logo + brand image stacked, fully visible --}}
            <div class="auth-brand-block">
                <img src="{{ asset($settings['admin_logo'] ?? 'images/default/logo.ico') }}"
                     alt="Aruvi Logo"
                     class="auth-logo-img">
                <img src="{{ asset($settings['site_brand_image'] ?? 'images/default/brand.png') }}"
                     alt="Aruvi on the Cliff"
                     class="auth-brand-img">
            </div>

            <h2>Resort Admin<br>Control Center</h2>
            <p>Manage rooms, dining, gallery, guests, and all resort content from one elegant dashboard.</p>
            <ul class="auth-features">
                <li><i class="bi bi-check-circle-fill"></i> Full CMS for all website sections</li>
                <li><i class="bi bi-check-circle-fill"></i> Media uploads &amp; theme customization</li>
                <li><i class="bi bi-check-circle-fill"></i> Role-based secure access control</li>
            </ul>
            <div class="auth-left-footer">&copy; {{ date('Y') }} Aruvi on the Cliff. All rights reserved.</div>
        </div>
    </div>

    {{-- Right: form --}}
    <div class="auth-right">
        <div class="auth-form-wrap">

            {{-- Logo moved to left panel --}}

            <span class="form-eyebrow"><i class="bi bi-shield-lock me-1"></i>Secure Access</span>
            <h2>Welcome back</h2>
            <p class="auth-subtitle">Sign in to your admin account to continue.</p>

            @if($errors->any())
                <div class="auth-alert">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0 mt-1"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf

                <div class="field-wrap">
                    <label for="email">Email Address</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-envelope field-icon"></i>
                        <input type="email" id="email" name="email"
                               value="{{ old('email') }}"
                               placeholder="admin@aruvi.test"
                               class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                               required autofocus autocomplete="email">
                    </div>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="field-wrap">
                    <label for="password">Password</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-lock field-icon"></i>
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password"
                               class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                               required autocomplete="current-password">
                        <button type="button" class="toggle-pw" onclick="togglePw('password',this)" aria-label="Toggle">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="auth-row">
                    <label>
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn-auth">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In to Dashboard
                </button>
            </form>

            <div class="auth-switch">
                New admin? <a href="{{ route('admin.register') }}">Create an account</a>
            </div>
        </div>
    </div>
</div>
<script>
function togglePw(id, btn) {
    const inp = document.getElementById(id), ic = btn.querySelector('i');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ic.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
