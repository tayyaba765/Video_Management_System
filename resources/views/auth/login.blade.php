<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — CineRate</title>
    <style>
        :root {
            --accent: #2563eb;
            --accent-deep: #1d4ed8;
            --accent-soft: #eef4ff;
            --line: #e3e9f4;
            --line-2: #d3ddec;
            --text: #0f1f3d;
            --muted: #64748b;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", sans-serif;
            color: var(--text);
            background:
                radial-gradient(700px 380px at 10% -10%, rgba(59, 130, 246, .08), transparent 60%),
                radial-gradient(600px 340px at 95% 0%, rgba(37, 99, 235, .06), transparent 60%),
                #f7faff;
        }

        .auth-card {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 2rem 1.75rem 1.6rem;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -.4px;
            color: var(--text);
            text-decoration: none;
            margin-bottom: 1.4rem;
        }

        .brand span {
            color: var(--accent);
        }

        .auth-title {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -.5px;
            margin: 0 0 .3rem;
        }

        .auth-sub {
            font-size: .87rem;
            color: var(--muted);
            margin: 0 0 1.4rem;
        }

        .alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 9px;
            padding: .65rem .8rem;
            font-size: .8rem;
            margin-bottom: 1.1rem;
        }

        .alert ul {
            margin: 0;
            padding-left: 1rem;
        }

        .field {
            margin-bottom: 1rem;
        }

        .field label {
            display: block;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: .35rem;
        }

        .field input {
            width: 100%;
            border: 1px solid var(--line-2);
            border-radius: 9px;
            padding: .55rem .8rem;
            font-size: .92rem;
            font-family: inherit;
            color: var(--text);
            background: #fff;
            outline: none;
            transition: border-color .15s;
        }

        .field input::placeholder {
            color: var(--muted);
        }

        .field input:focus {
            border-color: var(--accent);
        }

        .btn-primary {
            width: 100%;
            background: var(--accent);
            border: 1px solid var(--accent);
            color: #fff;
            font-weight: 700;
            font-size: .9rem;
            font-family: inherit;
            border-radius: 9px;
            padding: .6rem 1rem;
            cursor: pointer;
            transition: background .15s, border-color .15s;
        }

        .btn-primary:hover {
            background: var(--accent-deep);
            border-color: var(--accent-deep);
        }

        .auth-foot {
            margin: 1.25rem 0 0;
            text-align: center;
            font-size: .82rem;
            color: var(--muted);
        }

        .auth-foot a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-foot a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="auth-card">
        <a href="/" class="brand">🎬 Cine<span>Rate</span></a>

        <h1 class="auth-title">Welcome back</h1>
        <p class="auth-sub">Log in to rate and review movies.</p>

        @if ($errors->any())
            <div class="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="/login">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    placeholder="you@example.com" autocomplete="email" required autofocus>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="••••••••"
                    autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn-primary">Login</button>
        </form>

        <p class="auth-foot">Don't have an account? <a href="/register">Register</a></p>
    </div>

</body>

</html>
