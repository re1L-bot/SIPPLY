<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FFF7EF">
    <title>Checkout | SipPly</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --cream:#FFF7EF; --espresso:#542812; --coffee:#75452D; --caramel:#D99A69; --beige:#F5E2D1; --card:#FFFCF8; --ink:#382015; --muted:#8C7769; --label:#C2622A; --line:rgba(84,40,18,.10); --danger:#b3402f; --ok:#2f6a45; }
        @view-transition { navigation: auto; }
        *, *::before, *::after { box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body { margin:0; font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,sans-serif; background:var(--cream); color:var(--ink); -webkit-font-smoothing:antialiased; animation:page-in .5s ease; }
        @keyframes page-in { from { opacity:0; } }
        a { color:inherit; text-decoration:none; }
        [hidden] { display:none !important; }
        :focus-visible { outline:2px solid var(--caramel); outline-offset:3px; border-radius:12px; }
        .display { font-family:'Playfair Display',Georgia,serif; }
        .ico { fill:none; stroke:currentColor; stroke-width:1.75; stroke-linecap:round; stroke-linejoin:round; flex-shrink:0; width:1.25rem; height:1.25rem; }
        .sp-wrap { position:relative; z-index:1; width:min(1240px, calc(100% - 40px)); margin-inline:auto; }
        .eyebrow { display:block; font-size:12px; font-weight:800; letter-spacing:.2em; text-transform:uppercase; color:var(--label); }
        .sr { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0); white-space:nowrap; }
        @keyframes rise { from { opacity:0; transform:translateY(16px); } }
        @keyframes drop { from { opacity:0; transform:translateY(-8px); } }
        @keyframes pop { from { opacity:0; transform:scale(.94) translateY(-6px); } }

        /* Decoration */
        .deco { position:fixed; z-index:0; pointer-events:none; }
        .deco-leaves { left:-30px; top:40%; width:180px; opacity:.7; color:#F0D6BE; }
        .deco-blob { left:-110px; bottom:-120px; width:340px; height:340px; border-radius:50%; background:linear-gradient(140deg,#6b351c,var(--espresso)); }
        .bean { position:absolute; width:28px; height:18px; border-radius:50%; background:linear-gradient(135deg,#7a4426,#3a1a0b); box-shadow:0 4px 8px rgba(56,32,21,.28); transform:rotate(var(--r)); }
        .bean::after { content:""; position:absolute; inset:7px 4px auto; height:2px; border-radius:2px; background:#1d0c04; }
        .bean.float { animation:bean 6s ease-in-out var(--delay,0s) infinite; }
        @keyframes bean { 0%,100% { transform:translateY(0) rotate(var(--r)); } 50% { transform:translateY(-10px) rotate(calc(var(--r) + 14deg)); } }
        .fixed-bean { position:fixed; z-index:0; pointer-events:none; }
        @media (max-width:1100px) { .deco, .fixed-bean { display:none; } }

        /* Navbar */
        .nav-wrap { position:sticky; top:12px; z-index:50; padding-top:12px; }
        .nav { display:flex; align-items:center; gap:16px; padding:10px 14px 10px 24px; border-radius:999px; background:rgba(255,252,248,.9); -webkit-backdrop-filter:blur(16px); backdrop-filter:blur(16px); border:1px solid rgba(217,154,105,.25); box-shadow:0 10px 30px rgba(84,40,18,.09); }
        .brand { display:flex; align-items:center; gap:10px; flex-shrink:0; }
        .brand img { width:46px; height:46px; border-radius:50%; object-fit:cover; }
        .brand span { font-family:'Playfair Display',serif; font-size:26px; font-weight:700; letter-spacing:-.5px; color:var(--espresso); }
        .nav-links { display:flex; align-items:center; gap:2px; margin-left:auto; }
        .nav-link { display:inline-flex; align-items:center; gap:8px; padding:10px 15px; border-radius:99px; font-size:14px; font-weight:600; color:var(--coffee); white-space:nowrap; transition:background-color .25s ease, color .25s ease, transform .25s ease; }
        .nav-link:hover { background:var(--beige); color:var(--espresso); transform:translateY(-1px); }
        .nav-link .ico { width:17px; height:17px; transition:transform .25s ease; }
        .nav-link:hover .ico { transform:scale(1.12); }
        .nav-link.is-active { background:var(--espresso); color:#fff7ef; box-shadow:0 6px 16px rgba(84,40,18,.25); }
        .nav-count { display:grid; place-items:center; min-width:20px; height:20px; padding:0 6px; border-radius:99px; background:var(--caramel); color:var(--espresso); font-size:11px; font-weight:800; }
        .burger { display:none; place-items:center; width:46px; height:46px; margin-left:auto; border:0; border-radius:50%; background:var(--espresso); color:#fff; cursor:pointer; }
        .mobile-panel { margin-top:10px; padding:12px; border-radius:28px; background:rgba(255,252,248,.97); border:1px solid var(--line); box-shadow:0 14px 30px rgba(84,40,18,.12); animation:drop .3s cubic-bezier(.2,.8,.2,1); }
        .mobile-panel .nav-link { display:flex; }
        @media (max-width:980px) { .nav > .nav-links { display:none; } .burger { display:grid; } }

        /* Hero */
        .hero { position:relative; overflow:hidden; display:grid; grid-template-columns:1.2fr 1fr; align-items:center; gap:20px; margin-top:20px; min-height:270px; padding:36px 0 0 56px; border-radius:44px; background:linear-gradient(110deg,#F5E2D1 0%,#FBEBDB 50%,#FFF7EF 100%); border:1px solid var(--line); animation:rise .7s cubic-bezier(.2,.7,.2,1) backwards; }
        .hero-copy { position:relative; z-index:3; padding-bottom:36px; }
        .hero h1 { margin:10px 0 0; font-size:clamp(44px,6vw,72px); line-height:1.02; font-weight:800; letter-spacing:-.025em; color:var(--espresso); }
        .steps { display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin-top:20px; }
        .step { display:inline-flex; align-items:center; gap:10px; padding:8px 16px 8px 8px; border-radius:99px; background:rgba(255,252,248,.8); border:1px solid var(--line); font-size:13px; font-weight:700; color:var(--muted); }
        .step b { display:grid; place-items:center; width:26px; height:26px; border-radius:50%; background:var(--beige); font-size:12px; color:var(--coffee); }
        .step.done { color:var(--ok); } .step.done b { background:#e5f3e3; color:var(--ok); }
        .step.now { background:var(--espresso); border-color:var(--espresso); color:#fff7ef; box-shadow:0 8px 18px rgba(84,40,18,.25); } .step.now b { background:var(--caramel); color:var(--espresso); }
        .step-line { width:22px; height:2px; border-radius:2px; background:rgba(84,40,18,.18); }
        .hero-art { position:relative; align-self:stretch; min-height:230px; }
        .hero-shape { position:absolute; right:-10%; top:-18%; width:96%; height:130%; background:linear-gradient(140deg,#6b351c,var(--espresso)); border-radius:50% 50% 0 56% / 60% 52% 0 46%; }
        .hero-leaf { position:absolute; right:3%; top:6%; width:34%; color:#7b4a31; opacity:.55; }
        .hero-arch { position:absolute; z-index:2; left:10%; right:24%; bottom:0; top:8%; overflow:hidden; border-radius:999px 999px 0 0; box-shadow:0 26px 60px rgba(56,32,21,.4); background:var(--beige); }
        .hero-arch img { width:100%; height:100%; object-fit:cover; transition:transform 1.2s cubic-bezier(.2,.7,.2,1); }
        .hero:hover .hero-arch img { transform:scale(1.05); }
        .hero .bean { z-index:3; }
        @media (max-width:860px) { .hero { grid-template-columns:1fr; padding:30px 24px 0; border-radius:32px; } .hero-art { display:none; } .hero-copy { padding-bottom:30px; } }

        /* Panels & fields */
        .panel { background:var(--card); border:1px solid var(--line); border-radius:30px; box-shadow:0 1px 2px rgba(84,40,18,.05), 0 8px 26px rgba(84,40,18,.07); }
        .pill-button { display:inline-flex; align-items:center; justify-content:center; gap:10px; border-radius:999px; padding:14px 26px; font:700 15px 'Plus Jakarta Sans',sans-serif; border:1px solid transparent; cursor:pointer; transition:transform .25s ease, background-color .25s ease, box-shadow .25s ease; }
        .pill-button:hover { transform:translateY(-2px); }
        .pill-button:active { transform:scale(.97); }
        .btn-primary { background:var(--espresso); color:#fff7ef; box-shadow:0 12px 24px rgba(84,40,18,.28); }
        .btn-primary:hover { background:var(--coffee); box-shadow:0 16px 30px rgba(84,40,18,.34); }
        .btn-soft { background:var(--beige); color:var(--coffee); } .btn-soft:hover { background:#efd5bd; }
        .label { display:block; margin-bottom:7px; font-size:13px; font-weight:700; color:var(--coffee); }
        .label small { font-weight:500; color:var(--muted); }
        .field { width:100%; height:52px; padding:0 16px; border-radius:16px; border:1.5px solid var(--line); background:#fff; font:500 15px 'Plus Jakarta Sans',sans-serif; color:var(--ink); transition:border-color .2s ease, box-shadow .25s ease, transform .2s ease; }
        textarea.field { height:auto; padding:14px 16px; resize:vertical; }
        select.field { appearance:none; -webkit-appearance:none; padding-right:42px; background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2375452D' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 16px center; cursor:pointer; }
        .field:hover { border-color:var(--caramel); }
        .field:focus { outline:0; border-color:var(--caramel); box-shadow:0 0 0 5px rgba(217,154,105,.25); transform:translateY(-1px); }
        .field.readonly { display:flex; align-items:center; background:var(--beige); border-color:transparent; color:var(--coffee); font-weight:700; }
        .field-error { margin:7px 0 0; font-size:13px; font-weight:600; color:var(--danger); animation:pop .35s ease; }
        .grid2 { display:grid; gap:18px 16px; grid-template-columns:1fr 1fr; }
        .grid2 .full { grid-column:1/-1; }
        @media (max-width:620px) { .grid2 { grid-template-columns:1fr; } }

        .alert { display:flex; align-items:flex-start; gap:12px; margin-top:20px; padding:14px 20px; border-radius:18px; font-size:14px; animation:drop .45s cubic-bezier(.2,.8,.2,1); }
        .alert-ok { background:#e5f3e3; color:var(--ok); font-weight:600; } .alert-bad { background:#ffe3df; color:#a4473d; }

        /* Coupon ticket */
        .coupon { position:relative; display:flex; flex-wrap:wrap; align-items:flex-end; gap:14px; margin-top:24px; padding:24px 28px 24px 34px; animation:rise .7s .1s cubic-bezier(.2,.7,.2,1) backwards; }
        .coupon::before, .coupon::after { content:""; position:absolute; left:-12px; width:24px; height:24px; border-radius:50%; background:var(--cream); border:1px solid var(--line); }
        .coupon::before { top:22px; } .coupon::after { bottom:22px; }
        .coupon .grow { flex:1 1 220px; }
        .coupon .field { text-transform:uppercase; letter-spacing:.08em; font-weight:700; }
        .coupon-ok { display:flex; align-items:center; gap:10px; width:100%; margin:0; padding:11px 16px; border-radius:16px; background:#e5f3e3; color:var(--ok); font-size:13px; font-weight:700; animation:pop .5s cubic-bezier(.3,1.5,.5,1); }
        .coupon-ok .ico { width:19px; height:19px; }
        .link-remove { margin:8px 6px 0 0; display:block; margin-left:auto; padding:6px 10px; border:0; background:transparent; font:700 12px 'Plus Jakarta Sans',sans-serif; color:var(--muted); text-decoration:underline; cursor:pointer; transition:color .2s ease; }
        .link-remove:hover { color:var(--danger); }

        /* Layout */
        .checkout-grid { display:grid; gap:26px; margin-top:26px; align-items:start; grid-template-columns:minmax(0,1fr) 400px; }
        @media (max-width:1020px) { .checkout-grid { grid-template-columns:1fr; } }
        .sec { padding:30px; animation:rise .6s cubic-bezier(.2,.7,.2,1) backwards; animation-delay:calc(var(--i) * 90ms + .2s); }
        .sec + .sec { margin-top:22px; }
        .sec-head { display:flex; align-items:center; gap:16px; margin-bottom:22px; }
        .num { display:grid; place-items:center; width:46px; height:46px; flex-shrink:0; border-radius:16px; background:var(--beige); color:var(--coffee); font-family:'Playfair Display',serif; font-size:20px; font-weight:800; transition:transform .3s cubic-bezier(.3,1.6,.5,1), background-color .2s ease, color .2s ease; }
        .sec:hover .num, .sec:focus-within .num { transform:rotate(-6deg) scale(1.08); background:var(--espresso); color:#fff7ef; }
        .sec-head h2 { margin:4px 0 0; font-size:28px; font-weight:700; letter-spacing:-.4px; color:var(--espresso); }
        .hint { margin:14px 0 0; font-size:14px; line-height:1.6; color:var(--muted); }
        .hint a { font-weight:700; color:var(--espresso); background:linear-gradient(var(--caramel),var(--caramel)) left bottom/100% 2px no-repeat; transition:background-size .3s ease; padding-bottom:2px; }
        .hint a:hover { background-size:100% 100%; }
        @media (max-width:640px) { .sec { padding:22px 18px; } }

        .addr-preview { display:flex; align-items:flex-start; gap:14px; margin-top:16px; padding:16px 18px; border-radius:20px; background:#FFF6EC; border:1px solid rgba(217,154,105,.5); animation:pop .4s ease; }
        .addr-preview .pin { display:grid; place-items:center; width:42px; height:42px; flex-shrink:0; border-radius:14px; background:var(--beige); color:var(--label); }
        .addr-preview p { margin:0; font-size:14px; line-height:1.55; color:var(--muted); }
        .addr-preview p strong { display:block; font-size:15px; color:var(--espresso); }
        .fee-chip { margin-left:auto; flex-shrink:0; padding:7px 14px; border-radius:99px; background:#fff; border:1px solid var(--line); font-size:13px; font-weight:800; color:var(--coffee); font-variant-numeric:tabular-nums; }
        .save-check { display:flex; align-items:center; gap:12px; font-size:14px; font-weight:600; color:var(--coffee); cursor:pointer; }
        .save-check input, .pay input { width:20px; height:20px; accent-color:var(--espresso); cursor:pointer; flex-shrink:0; }
        .city-fee { margin:8px 0 0; font-size:13px; font-weight:700; color:var(--ok); min-height:18px; }

        .pay-grid { display:grid; gap:14px; grid-template-columns:1fr 1fr; }
        @media (max-width:620px) { .pay-grid { grid-template-columns:1fr; } }
        .pay { display:flex; align-items:center; gap:14px; padding:18px 20px; border-radius:20px; border:1.5px solid var(--line); background:#fff; font-size:15px; font-weight:700; color:var(--coffee); cursor:pointer; transition:border-color .25s ease, background-color .25s ease, transform .25s ease, box-shadow .25s ease; }
        .pay:hover { transform:translateY(-3px); border-color:var(--caramel); box-shadow:0 12px 24px rgba(84,40,18,.1); }
        .pay:has(input:checked) { border-color:var(--espresso); background:#FFF6EC; color:var(--espresso); box-shadow:0 0 0 4px rgba(217,154,105,.22); }
        .pay .ico { width:22px; height:22px; margin-left:auto; color:var(--caramel); transition:transform .3s ease; }
        .pay:hover .ico { transform:rotate(-8deg) scale(1.1); }

        /* Summary */
        .summary { position:sticky; top:104px; padding:30px; overflow:hidden; animation:rise .7s .2s cubic-bezier(.2,.7,.2,1) backwards; }
        @media (max-width:1020px) { .summary { position:static; } }
        .summary h2 { margin:0; font-size:28px; font-weight:700; color:var(--espresso); }
        .lines { margin-top:14px; max-height:300px; overflow:auto; }
        .line { display:flex; justify-content:space-between; gap:16px; padding:13px 0; border-bottom:1px solid var(--line); font-size:14px; transition:transform .2s ease; }
        .line:hover { transform:translateX(4px); }
        .line span:first-child { color:var(--muted); } .line span:last-child { flex-shrink:0; font-weight:700; color:var(--espresso); font-variant-numeric:tabular-nums; }
        .s-row { display:flex; justify-content:space-between; gap:16px; padding:13px 0; border-bottom:1px solid var(--line); font-size:14px; color:var(--muted); font-variant-numeric:tabular-nums; }
        .s-row strong { color:var(--ink); }
        .s-row .disc { color:var(--ok); }
        .s-total { display:flex; align-items:center; justify-content:space-between; margin:18px -30px 0; padding:20px 30px; background:linear-gradient(100deg,#F5E2D1,#FBEBDB); }
        .s-total span:first-child { font-size:14px; font-weight:700; color:var(--coffee); }
        .s-total span:last-child { font-size:30px; font-weight:700; color:var(--espresso); font-variant-numeric:tabular-nums; }
        .place { position:relative; overflow:hidden; width:100%; margin-top:22px; padding:18px 24px; }
        .place::before { content:""; position:absolute; inset:0; background:linear-gradient(105deg,transparent 35%,rgba(255,255,255,.22) 50%,transparent 65%); transform:translateX(-120%); }
        .place:hover::before { animation:sweep .9s ease; }
        @keyframes sweep { to { transform:translateX(120%); } }
        .place.is-loading { pointer-events:none; opacity:.9; }
        .place.is-loading::after { content:""; width:18px; height:18px; border-radius:50%; border:2.5px solid rgba(255,255,255,.35); border-top-color:#fff; animation:spin .7s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .note { margin:14px 0 0; text-align:center; font-size:12px; line-height:1.6; color:var(--muted); }
        .secure { display:flex; align-items:center; justify-content:center; gap:8px; margin-top:14px; font-size:12px; font-weight:700; color:var(--coffee); }
        .secure .ico { width:16px; height:16px; }

        .foot { position:relative; z-index:1; margin-top:70px; padding:34px 20px 30px; text-align:center; font-size:13px; font-weight:500; color:var(--muted); border-top:1px solid var(--beige); overflow:hidden; }
        .foot .bean { width:20px; height:13px; opacity:.55; } .foot .bean::after { inset:5px 3px auto; height:1.5px; }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration:.01ms !important; animation-iteration-count:1 !important; transition-duration:.01ms !important; scroll-behavior:auto !important; } }
    </style>
</head>
<body>
    @php
        $cartBadge = $cartCount ?? $lines->count();
        $heroLocal = file_exists(public_path('images/hero-cart.png')) ? asset('images/hero-cart.png') : (file_exists(public_path('images/hero-account.png')) ? asset('images/hero-account.png') : null);
        $heroPhoto = $heroLocal ?? 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=900&q=80';
        $initialTotal = max(0, (float) $subtotal + (float) $deliveryFee - (float) $discountAmount);
        $icons = [
            'home'   => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'user'   => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'cup'    => '<path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/>',
            'tea'    => '<path d="M8 2h8l1 6H7L8 2z"/><rect x="5" y="8" width="14" height="12" rx="2"/><path d="M12 11v5"/><path d="M9.5 13.5h5"/>',
            'cookie' => '<path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/><path d="M11 17v.01"/><path d="M7 14v.01"/>',
            'cart'   => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
            'pin'    => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
            'check'  => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
            'card'   => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
            'lock'   => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            'ticket' => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/>',
        ];
        $navLinks = [
            ['Home',       route('home'),                                       'home'],
            ['My Account', auth()->check() ? route('account') : route('login'), 'user'],
            ['Coffee',     route('categories.show', 'coffee'),                  'cup'],
            ['Milk Tea',   route('categories.show', 'milk-tea'),                'tea'],
            ['Pastries',   route('categories.show', 'pastries'),                'cookie'],
        ];
    @endphp

    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <symbol id="leaf" viewBox="0 0 200 240"><path d="M100 10c50 40 80 100 60 170-30 30-70 40-100 30C30 130 50 50 100 10Z" fill="currentColor"/><path d="M100 30c-5 60-5 120 0 200" stroke="rgba(255,255,255,.35)" stroke-width="3" fill="none" stroke-linecap="round"/></symbol>
    </svg>
    <svg class="deco deco-leaves" viewBox="0 0 200 240" aria-hidden="true"><use href="#leaf"/></svg>
    <span class="deco deco-blob" aria-hidden="true"></span>
    <span class="bean fixed-bean" style="--r:-30deg;left:50px;top:30%" aria-hidden="true"></span>
    <span class="bean fixed-bean" style="--r:30deg;right:34px;top:56%" aria-hidden="true"></span>

    {{-- ===== NAVBAR ===== --}}
    <div class="sp-wrap nav-wrap">
        <header>
            <div class="nav">
                <a class="brand" href="{{ route('home') }}" aria-label="SipPly home"><img src="{{ asset('images/favicon.png') }}" alt="" width="46" height="46"><span>SipPly</span></a>
                <nav class="nav-links" aria-label="Main navigation">
                    @foreach ($navLinks as [$label, $href, $icon])
                        <a class="nav-link" href="{{ $href }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$icon] !!}</svg>{{ $label }}</a>
                    @endforeach
                    <a class="nav-link is-active" href="{{ route('cart.index') }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['cart'] !!}</svg>Cart @if ($cartBadge > 0)<span class="nav-count">{{ $cartBadge }}</span>@endif</a>
                </nav>
                <button type="button" class="burger" id="burger" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-panel"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
            </div>
            <div class="mobile-panel" id="mobile-panel" hidden>
                @foreach ($navLinks as [$label, $href, $icon])
                    <a class="nav-link" href="{{ $href }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$icon] !!}</svg>{{ $label }}</a>
                @endforeach
                <a class="nav-link is-active" href="{{ route('cart.index') }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['cart'] !!}</svg>Cart @if ($cartBadge > 0)<span class="nav-count">{{ $cartBadge }}</span>@endif</a>
            </div>
        </header>
    </div>

    <main class="sp-wrap">
        {{-- ===== HERO ===== --}}
        <section class="hero" aria-labelledby="checkout-title">
            <div class="hero-copy">
                <span class="eyebrow">Almost time for your next sip</span>
                <h1 class="display" id="checkout-title">Checkout</h1>
                <div class="steps" aria-label="Checkout progress">
                    <span class="step done"><b>✓</b>Cart</span><span class="step-line"></span>
                    <span class="step now" aria-current="step"><b>2</b>Checkout</span><span class="step-line"></span>
                    <span class="step"><b>3</b>Enjoy</span>
                </div>
            </div>
            <div class="hero-art" aria-hidden="true">
                <span class="hero-shape"></span>
                <svg class="hero-leaf" viewBox="0 0 200 240"><use href="#leaf"/></svg>
                <div class="hero-arch"><img src="{{ $heroPhoto }}" alt=""></div>
                <span class="bean float" style="--r:-25deg;left:4%;top:20%"></span>
                <span class="bean float" style="--r:35deg;--delay:-2s;right:10%;bottom:12%"></span>
                <span class="bean float" style="--r:10deg;--delay:-4s;right:2%;top:30%;width:22px;height:14px"></span>
            </div>
        </section>

        @if ($errors->any())
            <div class="alert alert-bad" role="alert">
                <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <ul class="list-inside list-disc space-y-1" style="margin:0;padding:0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @if (session('status'))
            <div class="alert alert-ok" role="status"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['check'] !!}</svg><span>{{ session('status') }}</span></div>
        @endif

        {{-- ===== COUPON ===== --}}
        <form class="panel coupon" method="POST" action="{{ route('checkout.coupon.apply') }}">
            @csrf
            <div class="grow">
                <label class="label" for="coupon_code"><svg class="ico" viewBox="0 0 24 24" style="width:16px;height:16px;vertical-align:-3px;margin-right:6px" aria-hidden="true">{!! $icons['ticket'] !!}</svg>Discount coupon</label>
                <input class="field" id="coupon_code" name="coupon_code" maxlength="50" value="{{ old('coupon_code', $coupon?->code) }}" placeholder="Enter coupon code" autocomplete="off">
            </div>
            <button class="pill-button btn-primary" type="submit">{{ $coupon ? 'Update coupon' : 'Apply coupon' }}</button>
            @if ($coupon)
                <p class="coupon-ok"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['check'] !!}</svg>{{ $coupon->code }} applied — {{ $coupon->discount_type === 'percent' ? number_format((float) $coupon->discount_value, 2).'%' : '₱'.number_format((float) $coupon->discount_value, 2) }} off your subtotal.</p>
            @endif
        </form>
        @if ($coupon)
            <form method="POST" action="{{ route('checkout.coupon.apply') }}">
                @csrf
                <input type="hidden" name="coupon_code" value="">
                <button class="link-remove" type="submit">Remove coupon</button>
            </form>
        @endif

        {{-- ===== MAIN FORM ===== --}}
        <form class="checkout-grid" method="POST" action="{{ route('checkout.store') }}" id="checkout-form">
            @csrf
            <div>
                <section class="panel sec" style="--i:0">
                    <div class="sec-head">
                        <span class="num">1</span>
                        <div><span class="eyebrow">Where should we deliver?</span><h2 class="display">Delivery details</h2></div>
                    </div>

                    @if ($addresses->isNotEmpty())
                        <div>
                            <label class="label" for="saved_address">Choose a saved delivery address</label>
                            <select class="field" id="saved_address" name="address_id" required>
                                @foreach ($addresses as $address)
                                    <option value="{{ $address->address_id }}"
                                        data-fee="{{ number_format((float) $deliveryFees->get($address->city, 0), 2, '.', '') }}"
                                        data-label="{{ $address->label ?: 'Delivery address' }}"
                                        data-who="{{ $address->recipient_name }}"
                                        data-where="{{ $address->address_line }}, {{ $address->city }}"
                                        @selected((string) old('address_id', $selectedAddressId) === (string) $address->address_id)>
                                        {{ $address->label ?: 'Delivery address' }} — {{ $address->recipient_name }}, {{ $address->address_line }}, {{ $address->city }} (₱{{ number_format((float) $deliveryFees->get($address->city, 0), 2) }}){{ $address->is_default ? ' (default)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('address_id') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="addr-preview" id="addr-preview" aria-live="polite">
                            <span class="pin"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['pin'] !!}</svg></span>
                            <p><strong id="ap-label"></strong><span id="ap-who"></span><br><span id="ap-where"></span></p>
                            <span class="fee-chip" id="ap-fee"></span>
                        </div>
                        <p class="hint">Need to change or add an address? <a href="{{ route('account') }}#addresses">Manage saved addresses in your account</a>.</p>
                    @else
                        <div class="grid2">
                            @if ($user === null)
                                <div class="full">
                                    <label class="label" for="email">Email address</label>
                                    <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" maxlength="254" autocomplete="email" required>
                                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                            @endif
                            <div>
                                <label class="label" for="recipient_name">Recipient name</label>
                                <input class="field" id="recipient_name" name="recipient_name" value="{{ old('recipient_name', $user ? $user->first_name.' '.$user->last_name : '') }}" maxlength="200" autocomplete="name" required>
                                @error('recipient_name') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label" for="phone_number">Phone number</label>
                                <input class="field" id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number', $user?->phone_number) }}" maxlength="25" autocomplete="tel" required>
                                @error('phone_number') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="full">
                                <label class="label" for="address_line">House / street / building</label>
                                <input class="field" id="address_line" name="address_line" value="{{ old('address_line') }}" maxlength="255" autocomplete="street-address" required>
                                @error('address_line') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label" for="barangay">Barangay</label>
                                <input class="field" id="barangay" name="barangay" value="{{ old('barangay') }}" maxlength="100" required>
                                @error('barangay') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label" for="city">Municipality</label>
                                <select class="field" id="city" name="city" autocomplete="address-level2" required>
                                    <option value="">Choose a municipality</option>
                                    @foreach ($municipalities as $municipality)
                                        <option value="{{ $municipality }}" data-fee="{{ number_format((float) $deliveryFees->get($municipality), 2, '.', '') }}" @selected(old('city') === $municipality)>{{ $municipality }} — ₱{{ number_format((float) $deliveryFees->get($municipality), 2) }}</option>
                                    @endforeach
                                </select>
                                @error('city') <p class="field-error">{{ $message }}</p> @enderror
                                <p class="city-fee" id="city-fee" aria-live="polite"></p>
                            </div>
                            <div>
                                <span class="label">Province</span>
                                <p class="field readonly">Cebu</p>
                                <input type="hidden" name="province" value="Cebu">
                            </div>
                            <div>
                                <label class="label" for="postal_code">Postal code <small>optional</small></label>
                                <input class="field" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" maxlength="20" autocomplete="postal-code">
                            </div>
                            <label class="save-check full">
                                <input type="checkbox" name="save_address" value="1" @checked(old('save_address'))>
                                Save this as a delivery address on my account
                            </label>
                        </div>
                    @endif
                </section>

                <section class="panel sec" style="--i:1">
                    <div class="sec-head">
                        <span class="num">2</span>
                        <div><span class="eyebrow">How would you like to pay?</span><h2 class="display">Payment method</h2></div>
                    </div>
                    <div class="pay-grid">
                        @foreach ($paymentMethods as $paymentMethod)
                            <label class="pay">
                                <input type="radio" name="payment_method_id" value="{{ $paymentMethod->payment_method_id }}" @checked((string) old('payment_method_id', $paymentMethods->first()->payment_method_id) === (string) $paymentMethod->payment_method_id) required>
                                {{ $paymentMethod->method_name }}
                                <svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['card'] !!}</svg>
                            </label>
                        @endforeach
                    </div>
                    @error('payment_method_id') <p class="field-error">{{ $message }}</p> @enderror
                    <p class="hint">Payment is recorded as pending. Online payment collection is not processed by this checkout.</p>
                </section>

                <section class="panel sec" style="--i:2">
                    <div class="sec-head">
                        <span class="num">3</span>
                        <div><span class="eyebrow">Anything we should know?</span><h2 class="display">Delivery notes</h2></div>
                    </div>
                    <label class="label" for="delivery_notes">Notes for the delivery team <small>optional</small></label>
                    <textarea class="field" id="delivery_notes" name="delivery_notes" rows="3" maxlength="500" placeholder="Anything the delivery team should know?">{{ old('delivery_notes') }}</textarea>
                    @error('delivery_notes') <p class="field-error">{{ $message }}</p> @enderror
                </section>
            </div>

            <aside class="panel summary" aria-labelledby="summary-title">
                <h2 class="display" id="summary-title">Order summary</h2>
                <div class="lines">
                    @foreach ($lines as $line)
                        <div class="line">
                            <span>{{ $line->quantity }} × {{ $line->product_name }}{{ $line->size_name ? ' · '.$line->size_name : '' }}</span>
                            <span>₱{{ number_format((float) $line->line_total, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                <div style="margin-top:6px">
                    <div class="s-row"><span>Subtotal</span><strong id="order-subtotal" data-subtotal="{{ number_format((float) $subtotal, 2, '.', '') }}">₱{{ number_format((float) $subtotal, 2) }}</strong></div>
                    <div class="s-row"><span>Delivery fee</span><strong id="delivery-fee" data-fee="{{ number_format((float) $deliveryFee, 2, '.', '') }}">₱{{ number_format((float) $deliveryFee, 2) }}</strong></div>
                    @if ($coupon)
                        <div class="s-row"><span>Coupon ({{ $coupon->code }})</span><strong class="disc" id="coupon-discount" data-discount="{{ number_format((float) $discountAmount, 2, '.', '') }}">−₱{{ number_format((float) $discountAmount, 2) }}</strong></div>
                    @endif
                </div>
                <div class="s-total"><span>Total</span><span class="display" id="order-total">₱{{ number_format($initialTotal, 2) }}</span></div>
                <button class="pill-button btn-primary place" type="submit" id="place-order"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['lock'] !!}</svg><span>Place order</span></button>
                <p class="secure"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['check'] !!}</svg>Review your details, then place your order</p>
                <p class="note">Stock is reserved when your order is placed. You’ll receive an order confirmation by email.</p>
            </aside>
        </form>
    </main>

    <footer class="foot">
        <span class="bean" style="--r:-25deg;left:12%;top:24px" aria-hidden="true"></span>
        <span class="bean" style="--r:30deg;right:14%;top:40px" aria-hidden="true"></span>
        <span class="bean" style="--r:10deg;left:26%;bottom:18px" aria-hidden="true"></span>
        <span class="bean" style="--r:-40deg;right:28%;bottom:16px" aria-hidden="true"></span>
        SipPly · Something good is brewing.
    </footer>
    @include('partials.footer')

    <script>
        (function () {
            // Mobile menu
            var burger = document.getElementById('burger'), panel = document.getElementById('mobile-panel');
            if (burger && panel) {
                burger.addEventListener('click', function () { panel.hidden = !panel.hidden; burger.setAttribute('aria-expanded', String(!panel.hidden)); });
            }

            var addressSelect = document.getElementById('saved_address') || document.getElementById('city');
            var deliveryFeeDisplay = document.getElementById('delivery-fee');
            var orderTotalDisplay = document.getElementById('order-total');
            var orderSubtotal = Number(document.getElementById('order-subtotal').dataset.subtotal);
            var couponDiscount = Number((document.getElementById('coupon-discount') || { dataset: {} }).dataset.discount || 0);
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var shown = { fee: Number(deliveryFeeDisplay.dataset.fee) || 0 }, raf = null;

            function money(n) { return '₱' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
            function paint(fee) {
                deliveryFeeDisplay.textContent = money(fee);
                orderTotalDisplay.textContent = money(Math.max(0, orderSubtotal + fee - couponDiscount));
            }
            function tween(to) {
                if (raf) { cancelAnimationFrame(raf); }
                if (reduce) { shown.fee = to; paint(to); return; }
                var from = shown.fee, t0 = null;
                raf = requestAnimationFrame(function step(ts) {
                    if (t0 === null) { t0 = ts; }
                    var k = Math.min((ts - t0) / 450, 1), e = 1 - Math.pow(1 - k, 3);
                    shown.fee = from + (to - from) * e; paint(shown.fee);
                    if (k < 1) { raf = requestAnimationFrame(step); } else { shown.fee = to; paint(to); }
                });
            }

            function sync() {
                if (!addressSelect) { return; }
                var opt = addressSelect.selectedOptions[0];
                var fee = Number(opt && opt.dataset.fee) || 0;
                tween(fee);

                var prev = document.getElementById('addr-preview');
                if (prev && opt) {
                    document.getElementById('ap-label').textContent = opt.dataset.label || '';
                    document.getElementById('ap-who').textContent = opt.dataset.who || '';
                    document.getElementById('ap-where').textContent = opt.dataset.where || '';
                    document.getElementById('ap-fee').textContent = 'Delivery ' + money(fee);
                    prev.style.animation = 'none'; void prev.offsetWidth; prev.style.animation = '';
                }
                var cityFee = document.getElementById('city-fee');
                if (cityFee) { cityFee.textContent = opt && opt.value ? 'Delivery to ' + opt.value + ': ' + money(fee) : ''; }
            }
            if (addressSelect) {
                addressSelect.addEventListener('change', sync);
                // Fill the preview / municipality hint on load without animating the totals
                var o = addressSelect.selectedOptions[0];
                var prev0 = document.getElementById('addr-preview');
                if (prev0 && o) {
                    document.getElementById('ap-label').textContent = o.dataset.label || '';
                    document.getElementById('ap-who').textContent = o.dataset.who || '';
                    document.getElementById('ap-where').textContent = o.dataset.where || '';
                    document.getElementById('ap-fee').textContent = 'Delivery ' + money(Number(o.dataset.fee) || 0);
                }
                var cf0 = document.getElementById('city-fee');
                if (cf0 && o && o.value) { cf0.textContent = 'Delivery to ' + o.value + ': ' + money(Number(o.dataset.fee) || 0); }
            }

            // Button feedback while the order is being placed (the form still posts normally)
            var form = document.getElementById('checkout-form'), btn = document.getElementById('place-order');
            if (form && btn) {
                form.addEventListener('submit', function () {
                    if (!form.checkValidity()) { return; }
                    btn.classList.add('is-loading');
                    btn.querySelector('span').textContent = 'Placing your order…';
                    setTimeout(function () { btn.disabled = true; }, 0);
                });
                window.addEventListener('pageshow', function (e) {
                    if (e.persisted) { btn.classList.remove('is-loading'); btn.disabled = false; btn.querySelector('span').textContent = 'Place order'; }
                });
            }
        })();
    </script>
</body>
</html>