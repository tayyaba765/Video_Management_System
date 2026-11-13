<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — CineRate</title>
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
            font-family: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", sans-serif;
            color: var(--text);
            background: #fff;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 1.1rem;
        }

        /* ---------- TOP BAR ---------- */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #fff;
            border-bottom: 1px solid var(--line);
        }

        .topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding: .8rem 0;
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
        }

        .brand span {
            color: var(--accent);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .user-chip {
            font-size: .8rem;
            font-weight: 600;
            color: var(--muted);
            border: 1px solid var(--line-2);
            border-radius: 999px;
            padding: .3rem .75rem;
            background: #fff;
        }

        .btn-ghost {
            background: #fff;
            border: 1px solid var(--line-2);
            color: var(--text);
            font-weight: 600;
            font-size: .83rem;
            font-family: inherit;
            border-radius: 9px;
            padding: .42rem .9rem;
            cursor: pointer;
            transition: background .15s, border-color .15s;
        }

        .btn-ghost:hover {
            background: var(--accent-soft);
            border-color: var(--accent);
            color: var(--accent-deep);
        }

        .btn-primary {
            display: inline-block;
            background: var(--accent);
            border: 1px solid var(--accent);
            color: #fff;
            font-weight: 700;
            font-size: .87rem;
            font-family: inherit;
            border-radius: 9px;
            padding: .5rem 1.05rem;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s, border-color .15s;
        }

        .btn-primary:hover {
            background: var(--accent-deep);
            border-color: var(--accent-deep);
            color: #fff;
        }

        /* ---------- PAGE ---------- */
        .page-head {
            padding: 2.2rem 0 1.4rem;
        }

        .page-head h1 {
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -.5px;
            margin: 0 0 .3rem;
        }

        .page-head p {
            margin: 0;
            color: var(--muted);
            font-size: .9rem;
        }

        .page-head strong {
            color: var(--text);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
            padding-bottom: 3rem;
        }

        .panel {
            display: flex;
            flex-direction: column;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
            padding: 1.2rem 1.25rem;
        }

        .panel-label {
            font-size: .66rem;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: .5rem;
        }

        .panel-title {
            font-size: 1.02rem;
            font-weight: 700;
            margin: 0 0 .4rem;
        }

        .panel-text {
            margin: 0 0 1.1rem;
            color: var(--muted);
            font-size: .86rem;
            line-height: 1.5;
        }

        .panel .btn-primary {
            align-self: flex-start;
        }
    </style>
</head>

<body>

    <header class="topbar">
        <div class="container topbar-inner">
            <a href="/" class="brand">🎬 Cine<span>Rate</span></a>

            <div class="topbar-right">
                <span class="user-chip">{{ auth()->user()->name }}</span>
                <form method="POST" action="/logout" style="margin:0">
                    @csrf
                    <button type="submit" class="btn-ghost">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="page-head">
            <h1>Dashboard</h1>
            <p>Welcome back, <strong>{{ auth()->user()->name }}</strong> 👋</p>
        </div>

        <div class="grid">
            <section class="panel">
                <span class="panel-label">Movies</span>
                <h2 class="panel-title">Rate something new</h2>
                <p class="panel-text">Browse the catalogue, leave an emoji rating, a score out of 10 and a short review.
                </p>
                <a href="{{ route('video-ratings.index') }}" class="btn-primary">Browse movies</a>
            </section>

            <section class="panel">
                <span class="panel-label">Account</span>
                <h2 class="panel-title">{{ auth()->user()->name }}</h2>
                <p class="panel-text">{{ auth()->user()->email }}</p>
                <a href="{{ route('video-ratings.index') }}" class="btn-primary">Back to home</a>
            </section>
        </div>
    </main>

</body>

</html>
