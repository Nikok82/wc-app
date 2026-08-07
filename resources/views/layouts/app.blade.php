<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'FIFA WC History')</title>
    <style>
        :root { --accent:#1b9e57; --ink:#16231d; --muted:#6b7a72; --line:#e2e8e5; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;
               color:var(--ink); background:#f4f6f5; line-height:1.5; }
        a { color:var(--accent); text-decoration:none; }
        a:hover { text-decoration:underline; }
        .wrap { max-width:880px; margin:0 auto; padding:24px 16px 90px; }

        .team-head { display:flex; align-items:center; gap:16px; margin-bottom:20px; }
        .team-head .flag { width:88px; height:auto; border-radius:6px;
                           box-shadow:0 1px 5px rgba(0,0,0,.22); }
        .team-head h1 { font-size:28px; margin:0; letter-spacing:-.3px; }

        .tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px;
                border-bottom:1px solid var(--line); padding-bottom:12px; }
        .tab-btn { font:inherit; cursor:pointer; padding:8px 16px; border-radius:999px;
                   border:1px solid var(--line); background:#fff; color:var(--ink); }
        .tab-btn.active { background:var(--accent); border-color:var(--accent); color:#fff; }
        .tab-btn:disabled { opacity:.45; cursor:not-allowed; }

        #tab-content { background:#fff; border:1px solid var(--line);
                       border-radius:12px; padding:18px 20px; min-height:120px; }

        .info-grid { display:grid; gap:0; }
        .info-row { display:flex; justify-content:space-between; gap:16px;
                    padding:10px 2px; border-bottom:1px solid var(--line); }
        .info-row:last-child { border-bottom:0; }
        .info-row .lbl { color:var(--muted); text-transform:uppercase;
                         font-size:12px; letter-spacing:.5px; padding-top:2px; }
        .info-row .val { font-weight:600; text-align:right; }

        .footer-nav { display:flex; justify-content:space-between; gap:12px; margin-top:22px; }
        .footer-nav a { padding:8px 14px; background:#fff; border:1px solid var(--line);
                        border-radius:999px; font-weight:600; }
        .err { color:#b3261e; }
    </style>
    @include('partials.luce-bordo-css')
</head>
<body>
    @include('partials.navbar')
    <div class="wrap">
        @yield('content')
    </div>
    {{-- Barra bottoni globale (solo responsive). Le sezioni contestuali
         prev/next arriveranno dai controller come $barraPrev/$barraNext. --}}
    @include('partials.barra-bottoni', [
        'barraPrev' => $barraPrev ?? null,
        'barraNext' => $barraNext ?? null,
    ])
    @include('partials.wc-assets')
</body>
</html>
