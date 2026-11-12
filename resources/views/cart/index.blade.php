<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FFF7EF">
    <title>Your cart | SipPly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --cream:#FFF7EF; --espresso:#542812; --coffee:#75452D; --caramel:#D99A69; --beige:#F5E2D1; --card:#FFFCF8; --ink:#382015; --muted:#8C7769; --label:#C2622A; --line:rgba(84,40,18,.10); }
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

        /* Decoration */
        .deco { position:fixed; z-index:0; pointer-events:none; }
        .deco-leaves { left:-30px; top:34%; width:190px; opacity:.7; color:#F0D6BE; }
        .deco-blob { left:-110px; bottom:-120px; width:360px; height:360px; border-radius:50%; background:linear-gradient(140deg,#6b351c,var(--espresso)); }
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
        .nav-search { position:relative; display:flex; align-items:center; flex:1; min-width:0; max-width:340px; height:46px; padding:0 18px 0 46px; border-radius:99px; background:#fff; border:1px solid var(--line); transition:box-shadow .25s ease, border-color .25s ease; }
        .nav-search:hover { border-color:var(--caramel); }
        .nav-search:focus-within { border-color:var(--caramel); box-shadow:0 0 0 4px rgba(217,154,105,.25); }
        .nav-search .ico { position:absolute; left:16px; width:18px; height:18px; color:var(--coffee); transition:transform .3s ease; }
        .nav-search:focus-within .ico { transform:scale(1.15); }
        .nav-search input { flex:1; min-width:0; border:0; outline:0; background:transparent; font:500 14px 'Plus Jakarta Sans',sans-serif; color:var(--ink); }
        .nav-links { display:flex; align-items:center; gap:2px; margin-left:auto; }
        .nav-link { position:relative; display:inline-flex; align-items:center; gap:8px; padding:10px 15px; border-radius:99px; font-size:14px; font-weight:600; color:var(--coffee); white-space:nowrap; transition:background-color .25s ease, color .25s ease, transform .25s ease; }
        .nav-link:hover { background:var(--beige); color:var(--espresso); transform:translateY(-1px); }
        .nav-link .ico { width:17px; height:17px; transition:transform .25s ease; }
        .nav-link:hover .ico { transform:scale(1.12); }
        .nav-link.is-active { background:var(--espresso); color:#fff7ef; box-shadow:0 6px 16px rgba(84,40,18,.25); }
        .nav-count { display:grid; place-items:center; min-width:20px; height:20px; padding:0 6px; border-radius:99px; background:var(--caramel); color:var(--espresso); font-size:11px; font-weight:800; }
        .burger { display:none; place-items:center; width:46px; height:46px; margin-left:auto; border:0; border-radius:50%; background:var(--espresso); color:#fff; cursor:pointer; }
        .mobile-panel { margin-top:10px; padding:12px; border-radius:28px; background:rgba(255,252,248,.97); border:1px solid var(--line); box-shadow:0 14px 30px rgba(84,40,18,.12); animation:drop .3s cubic-bezier(.2,.8,.2,1); }
        .mobile-panel .nav-link { display:flex; }
        .mobile-panel .nav-search { max-width:none; margin-bottom:8px; }
        @keyframes drop { from { opacity:0; transform:translateY(-8px); } }
        @media (max-width:1060px) { .nav > .nav-search, .nav > .nav-links { display:none; } .burger { display:grid; } }

        /* Hero */
        .hero { position:relative; overflow:hidden; display:grid; grid-template-columns:1.1fr 1fr; align-items:center; gap:20px; margin-top:20px; min-height:320px; padding:42px 0 0 56px; border-radius:44px; background:linear-gradient(110deg,#F5E2D1 0%,#FBEBDB 50%,#FFF7EF 100%); border:1px solid var(--line); animation:rise .7s cubic-bezier(.2,.7,.2,1) backwards; }
        @keyframes rise { from { opacity:0; transform:translateY(16px); } }
        .hero-copy { position:relative; z-index:3; padding-bottom:42px; }
        .hero h1 { margin:10px 0 0; font-size:clamp(46px,6.2vw,78px); line-height:1.02; font-weight:800; letter-spacing:-.025em; color:var(--espresso); }
        .hero p.lead { margin:16px 0 0; max-width:40ch; font-size:17px; line-height:1.6; color:#664734; }
        .hero-art { position:relative; align-self:stretch; min-height:280px; }
        .hero-shape { position:absolute; right:-10%; top:-18%; width:96%; height:130%; background:linear-gradient(140deg,#6b351c,var(--espresso)); border-radius:50% 50% 0 56% / 60% 52% 0 46%; }
        .hero-leaf { position:absolute; right:3%; top:6%; width:36%; color:#7b4a31; opacity:.55; }
        .hero-arch { position:absolute; z-index:2; left:8%; right:22%; bottom:0; top:8%; overflow:hidden; border-radius:999px 999px 0 0; box-shadow:0 26px 60px rgba(56,32,21,.4); background:var(--beige); }
        .hero-arch img { width:100%; height:100%; object-fit:cover; transition:transform 1.2s cubic-bezier(.2,.7,.2,1); }
        .hero:hover .hero-arch img { transform:scale(1.05); }
        .hero .bean { z-index:3; }
        @media (max-width:860px) { .hero { grid-template-columns:1fr; padding:34px 26px 0; border-radius:32px; } .hero-art { min-height:250px; margin:0 -26px; } .hero-arch { left:18%; right:18%; } }

        /* Shared */
        .panel { background:var(--card); border:1px solid var(--line); border-radius:30px; box-shadow:0 1px 2px rgba(84,40,18,.05), 0 8px 26px rgba(84,40,18,.07); }
        .pill-button { display:inline-flex; align-items:center; justify-content:center; gap:10px; border-radius:999px; padding:14px 26px; font:700 15px 'Plus Jakarta Sans',sans-serif; border:1px solid transparent; cursor:pointer; transition:transform .25s ease, background-color .25s ease, box-shadow .25s ease; }
        .pill-button:hover { transform:translateY(-2px); }
        .pill-button:active { transform:scale(.97); }
        .pill-button .ico { width:18px; height:18px; transition:transform .25s ease; }
        .pill-button:hover .ico { transform:translateX(3px); }
        .btn-primary { background:var(--espresso); color:#fff7ef; box-shadow:0 10px 22px rgba(84,40,18,.25); }
        .btn-primary:hover { background:var(--coffee); box-shadow:0 14px 26px rgba(84,40,18,.32); }
        .btn-outline { background:transparent; color:var(--espresso); border-color:rgba(84,40,18,.25); }
        .btn-outline:hover { background:var(--beige); }

        .alert { display:flex; align-items:flex-start; gap:12px; margin-top:20px; padding:14px 20px; border-radius:18px; font-size:14px; animation:drop .45s cubic-bezier(.2,.8,.2,1); }
        .alert-ok { background:#e5f3e3; color:#2f6a45; font-weight:600; }
        .alert-bad { background:#ffe3df; color:#a4473d; }

        /* Cart layout */
        .cart-grid { display:grid; gap:26px; margin-top:26px; align-items:start; grid-template-columns:minmax(0,1fr) 390px; }
        @media (max-width:1020px) { .cart-grid { grid-template-columns:1fr; } }
        .list-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:18px 26px; background:linear-gradient(100deg,#F5E2D1,#FBEBDB); border-bottom:1px solid var(--line); }
        .check { display:flex; align-items:center; gap:12px; font-size:13px; font-weight:700; color:var(--coffee); cursor:pointer; user-select:none; }
        .check input, .item-check { width:20px; height:20px; accent-color:var(--espresso); cursor:pointer; flex-shrink:0; }
        .item { position:relative; display:flex; flex-wrap:wrap; align-items:center; gap:18px 20px; padding:22px 26px; border-bottom:1px solid var(--line); transition:background-color .25s ease, opacity .3s ease, transform .3s ease; animation:rise .6s cubic-bezier(.2,.7,.2,1) backwards; animation-delay:calc(var(--i) * 80ms + .2s); }
        .item:last-child { border-bottom:0; }
        .item:hover { background:rgba(245,226,209,.35); }
        .item[hidden] { display:none !important; }
        .item.is-off .item-main { opacity:.5; }
        .item.is-removing { opacity:0; transform:translateX(24px); }
        .item-main { display:flex; align-items:center; gap:18px; flex:1 1 300px; min-width:0; cursor:pointer; transition:opacity .3s ease; }
        .thumb { display:grid; place-items:center; width:92px; height:92px; flex-shrink:0; overflow:hidden; border-radius:24px; background:var(--beige); color:var(--coffee); font-size:32px; font-weight:800; box-shadow:0 6px 16px rgba(84,40,18,.12); transition:transform .35s cubic-bezier(.3,1.4,.5,1); }
        .thumb img { width:100%; height:100%; object-fit:cover; }
        .item:hover .thumb { transform:scale(1.06) rotate(-3deg); }
        .item-name { margin:0; font-family:'Playfair Display',serif; font-size:22px; font-weight:700; color:var(--espresso); }
        .chip { display:inline-block; margin-top:6px; padding:4px 12px; border-radius:99px; background:var(--beige); font-size:12px; font-weight:700; color:var(--coffee); }
        .each { margin:6px 0 0; font-size:13px; color:var(--muted); }
        .warn { margin:8px 0 0; font-size:12px; font-weight:700; color:#b3402f; }
        .qty-form { display:flex; align-items:center; gap:8px; }
        .qty { display:flex; align-items:center; height:48px; padding:0 4px; border-radius:99px; border:1.5px solid var(--beige); background:var(--cream); }
        .qty button { display:grid; place-items:center; width:38px; height:38px; border:0; border-radius:50%; background:transparent; font-size:20px; font-weight:700; color:var(--espresso); cursor:pointer; transition:background-color .15s ease, transform .15s ease; }
        .qty button:hover { background:var(--beige); }
        .qty button:active { transform:scale(.88); }
        .qty input { width:38px; border:0; background:transparent; text-align:center; font:700 15px 'Plus Jakarta Sans',sans-serif; color:var(--ink); -moz-appearance:textfield; appearance:textfield; outline:0; }
        .qty input::-webkit-outer-spin-button, .qty input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
        .update-btn { padding:11px 18px; border:0; border-radius:99px; background:var(--espresso); color:#fff; font:700 13px 'Plus Jakarta Sans',sans-serif; cursor:pointer; animation:pop-in .3s cubic-bezier(.3,1.5,.5,1); transition:background-color .2s ease, transform .2s ease; }
        .update-btn:hover { background:var(--coffee); transform:translateY(-1px); }
        @keyframes pop-in { from { opacity:0; transform:scale(.7); } }
        .qty-static { padding:12px 18px; border-radius:99px; background:var(--beige); font-size:13px; font-weight:700; color:var(--coffee); }
        .line-total { min-width:96px; text-align:right; font-family:'Playfair Display',serif; font-size:22px; font-weight:700; color:var(--espresso); font-variant-numeric:tabular-nums; }
        .remove { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border:0; border-radius:99px; background:transparent; font:700 12px 'Plus Jakarta Sans',sans-serif; color:#a4473d; cursor:pointer; transition:background-color .2s ease, transform .2s ease; }
        .remove .ico { width:16px; height:16px; transition:transform .25s ease; }
        .remove:hover { background:#ffe3df; transform:translateY(-1px); }
        .remove:hover .ico { transform:rotate(-12deg) scale(1.1); }
        .item-tools { display:flex; align-items:center; gap:14px; margin-left:auto; flex-wrap:wrap; }
        @media (max-width:640px) { .item { padding:18px; } .item-tools { width:100%; justify-content:space-between; } .thumb { width:76px; height:76px; border-radius:20px; } }

        .summary { position:sticky; top:104px; padding:30px; overflow:hidden; animation:rise .7s .15s cubic-bezier(.2,.7,.2,1) backwards; }
        @media (max-width:1020px) { .summary { position:static; } }
        .summary h2 { margin:0; font-size:28px; font-weight:700; color:var(--espresso); }
        .s-row { display:flex; justify-content:space-between; gap:16px; padding:14px 0; border-bottom:1px solid var(--line); font-size:14px; color:var(--muted); font-variant-numeric:tabular-nums; }
        .s-row strong { color:var(--ink); }
        .s-total { display:flex; align-items:center; justify-content:space-between; margin:18px -30px 0; padding:20px 30px; background:linear-gradient(100deg,#F5E2D1,#FBEBDB); }
        .s-total span:first-child { font-size:14px; font-weight:700; color:var(--coffee); }
        .s-total span:last-child { font-size:30px; font-weight:700; color:var(--espresso); font-variant-numeric:tabular-nums; }
        .note { margin:14px 0 0; font-size:12px; line-height:1.6; color:var(--muted); }
        .selected-line { display:flex; align-items:center; gap:10px; margin-top:8px; font-size:13px; font-weight:600; color:var(--coffee); }
        .selected-line .ico { width:18px; height:18px; }
        .err-inline { margin-top:14px; padding:12px 16px; border-radius:16px; background:#ffe3df; color:#a4473d; font-size:13px; font-weight:600; animation:shake .4s ease; }
        @keyframes shake { 20%,60% { transform:translateX(-5px); } 40%,80% { transform:translateX(5px); } }
        .nomatch { display:none; padding:34px; text-align:center; font-size:14px; font-weight:600; color:var(--muted); }
        .nomatch.is-on { display:block; animation:drop .3s ease; }

        /* Empty state */
        .empty { position:relative; overflow:hidden; margin-top:26px; padding:64px 24px 70px; text-align:center; border-radius:44px; background:radial-gradient(circle at 50% 0%, #FFFCF8 0%, #FFF7EF 70%); animation:rise .7s .1s cubic-bezier(.2,.7,.2,1) backwards; }
        .empty h2 { margin:26px auto 0; max-width:18ch; font-size:clamp(30px,4vw,42px); line-height:1.1; font-weight:700; color:var(--espresso); }
        .empty p { margin:14px auto 0; max-width:40ch; font-size:16px; line-height:1.6; color:var(--muted); }
        .mascot { position:relative; width:min(240px,62%); margin:0 auto; animation:bob 4s ease-in-out infinite; }
        .mascot::before { content:""; position:absolute; inset:-14px; border-radius:50%; background:radial-gradient(circle, rgba(217,154,105,.45), transparent 68%); animation:glow 3.4s ease-in-out infinite; }
        .mascot img { position:relative; display:block; width:100%; height:auto; border-radius:50%; box-shadow:0 22px 40px rgba(84,40,18,.3); }
        @keyframes bob { 0%,100% { transform:translateY(0) rotate(-1.5deg); } 50% { transform:translateY(-10px) rotate(1.5deg); } }
        @keyframes glow { 0%,100% { opacity:.55; transform:scale(.96); } 50% { opacity:1; transform:scale(1.06); } }
        .empty .bean { z-index:1; }
        .empty-leaf { position:absolute; color:var(--beige); opacity:.9; pointer-events:none; }

        .foot { position:relative; z-index:1; margin-top:70px; padding:34px 20px 30px; text-align:center; font-size:13px; font-weight:500; color:var(--muted); border-top:1px solid var(--beige); overflow:hidden; }
        .foot .bean { width:20px; height:13px; opacity:.55; }
        .foot .bean::after { inset:5px 3px auto; height:1.5px; }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration:.01ms !important; animation-iteration-count:1 !important; transition-duration:.01ms !important; scroll-behavior:auto !important; } }
    </style>
</head>
<body>
    @php
        $cartBadge = $cartCount ?? $items->count();
        $heroLocal = file_exists(public_path('images/hero-cart.png')) ? asset('images/hero-cart.png') : (file_exists(public_path('images/hero-account.png')) ? asset('images/hero-account.png') : null);
        $heroPhoto = $heroLocal ?? 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=900&q=80';
        $deliveryFee = isset($deliveryFee) && is_numeric($deliveryFee) ? (float) $deliveryFee : null;
        $icons = [
            'home'   => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'user'   => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'cup'    => '<path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/>',
            'tea'    => '<path d="M8 2h8l1 6H7L8 2z"/><rect x="5" y="8" width="14" height="12" rx="2"/><path d="M12 11v5"/><path d="M9.5 13.5h5"/>',
            'cookie' => '<path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/><path d="M11 17v.01"/><path d="M7 14v.01"/>',
            'cart'   => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
            'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'trash'  => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>',
            'arrow'  => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
            'back'   => '<path d="m15 18-6-6 6-6"/>',
            'lock'   => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        ];
        $navLinks = [
            ['Home',       route('home'),                                       'home',   false],
            ['My Account', auth()->check() ? route('account') : route('login'), 'user',   false],
            ['Coffee',     route('categories.show', 'coffee'),                  'cup',    false],
            ['Milk Tea',   route('categories.show', 'milk-tea'),                'tea',    false],
            ['Pastries',   route('categories.show', 'pastries'),                'cookie', false],
        ];
    @endphp

    {{-- Decoration --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <symbol id="leaf" viewBox="0 0 200 240"><path d="M100 10c50 40 80 100 60 170-30 30-70 40-100 30C30 130 50 50 100 10Z" fill="currentColor"/><path d="M100 30c-5 60-5 120 0 200" stroke="rgba(255,255,255,.35)" stroke-width="3" fill="none" stroke-linecap="round"/></symbol>
    </svg>
    <svg class="deco deco-leaves" viewBox="0 0 200 240" aria-hidden="true"><use href="#leaf"/></svg>
    <span class="deco deco-blob" aria-hidden="true"></span>
    <span class="bean fixed-bean" style="--r:-30deg;left:50px;top:28%" aria-hidden="true"></span>
    <span class="bean fixed-bean" style="--r:30deg;right:34px;top:52%" aria-hidden="true"></span>

    {{-- ===== NAVBAR ===== --}}
    <div class="sp-wrap nav-wrap">
        <header>
            <div class="nav">
                <a class="brand" href="{{ route('home') }}" aria-label="SipPly home"><img src="{{ asset('images/favicon.png') }}" alt="" width="46" height="46"><span>SipPly</span></a>
                <label class="nav-search">
                    <svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['search'] !!}</svg>
                    <span class="sr">Search your cart</span>
                    <input type="search" placeholder="Search your cart…" autocomplete="off" data-cart-search>
                </label>
                <nav class="nav-links" aria-label="Main navigation">
                    @foreach ($navLinks as [$label, $href, $icon, $active])
                        <a class="nav-link" href="{{ $href }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$icon] !!}</svg>{{ $label }}</a>
                    @endforeach
                    <a class="nav-link is-active" href="{{ route('cart.index') }}" aria-current="page"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['cart'] !!}</svg>Cart @if ($cartBadge > 0)<span class="nav-count">{{ $cartBadge }}</span>@endif</a>
                </nav>
                <button type="button" class="burger" id="burger" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-panel"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
            </div>
            <div class="mobile-panel" id="mobile-panel" hidden>
                <label class="nav-search">
                    <svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['search'] !!}</svg>
                    <input type="search" placeholder="Search your cart…" aria-label="Search your cart" autocomplete="off" data-cart-search>
                </label>
                @foreach ($navLinks as [$label, $href, $icon, $active])
                    <a class="nav-link" href="{{ $href }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$icon] !!}</svg>{{ $label }}</a>
                @endforeach
                <a class="nav-link is-active" href="{{ route('cart.index') }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['cart'] !!}</svg>Cart @if ($cartBadge > 0)<span class="nav-count">{{ $cartBadge }}</span>@endif</a>
            </div>
        </header>
    </div>

    <main class="sp-wrap">
        {{-- ===== HERO ===== --}}
        <section class="hero" aria-labelledby="cart-title">
            <div class="hero-copy">
                <span class="eyebrow">Your shopping cart</span>
                <h1 class="display" id="cart-title">Your cart</h1>
                <p class="lead">Good things are brewing…</p>
            </div>
            <div class="hero-art" aria-hidden="true">
                <span class="hero-shape"></span>
                <svg class="hero-leaf" viewBox="0 0 200 240"><use href="#leaf"/></svg>
                <div class="hero-arch"><img src="{{ $heroPhoto }}" alt=""></div>
                <span class="bean float" style="--r:-25deg;left:2%;top:18%"></span>
                <span class="bean float" style="--r:35deg;--delay:-2s;right:10%;bottom:12%"></span>
                <span class="bean float" style="--r:10deg;--delay:-4s;right:2%;top:30%;width:22px;height:14px"></span>
                <span class="bean float" style="--r:-50deg;--delay:-1s;left:12%;bottom:8%;width:22px;height:14px"></span>
            </div>
        </section>

        @if (session('status'))
            <div class="alert alert-ok" role="status"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg><span>{{ session('status') }}</span></div>
        @endif
        @if ($errors->any())
            <div class="alert alert-bad" role="alert">
                <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <ul class="list-inside list-disc space-y-1" style="margin:0;padding:0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if ($items->isEmpty())
            {{-- ===== EMPTY CART ===== --}}
            <section class="panel empty" aria-labelledby="empty-title">
                <svg class="empty-leaf" viewBox="0 0 200 240" style="left:-30px;top:30px;width:170px;transform:rotate(-20deg)" aria-hidden="true"><use href="#leaf"/></svg>
                <svg class="empty-leaf" viewBox="0 0 200 240" style="right:-24px;bottom:-20px;width:200px;transform:rotate(25deg) scaleX(-1)" aria-hidden="true"><use href="#leaf"/></svg>
                <span class="bean float" style="--r:-25deg;left:14%;top:46%" aria-hidden="true"></span>
                <span class="bean float" style="--r:30deg;--delay:-2s;right:15%;top:22%" aria-hidden="true"></span>
                <span class="bean float" style="--r:10deg;--delay:-3s;right:22%;bottom:18%;width:22px;height:14px" aria-hidden="true"></span>
                <span class="bean float" style="--r:-40deg;--delay:-1s;left:24%;bottom:12%;width:22px;height:14px" aria-hidden="true"></span>

                <div class="mascot" aria-hidden="true">
                    <img src="{{ asset('images/sipply-mascot.webp') }}" alt="" width="240" height="240" loading="lazy">
                </div>
                <h2 class="display" id="empty-title">Your cart is waiting for a little refresh</h2>
                <p>Explore the menu and add a drink to get started.</p>
                <a class="pill-button btn-primary" style="margin-top:30px;padding:16px 34px;font-size:16px" href="{{ route('products.index') }}">Browse the menu <svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['arrow'] !!}</svg></a>
            </section>
        @else
            {{-- ===== CART WITH ITEMS ===== --}}
            <div class="cart-grid">
                {{-- Cart items list: no outer form, so the inner update/delete forms are not nested --}}
                <section class="panel" style="overflow:hidden" aria-label="Cart items">
                    <div class="list-head">
                        <label class="check">
                            <input id="select-all-items" type="checkbox" checked>
                            Select all
                        </label>
                        <span style="font-size:13px;color:var(--coffee);font-weight:600"><span id="selected-count">{{ $items->count() }}</span> of {{ $items->count() }} items selected</span>
                    </div>

                    @foreach ($items as $item)
                        @php
                            $usable = $item->is_available && $item->size_is_active && $item->stock_quantity > 0 && $item->quantity <= $item->stock_quantity;
                            $editable = $item->is_available && $item->size_is_active && $item->stock_quantity > 0;
                            $thumb = $item->image_path ?? null;
                        @endphp
                        <article class="item {{ $usable ? '' : 'is-off' }}" style="--i:{{ min($loop->index, 6) }}" data-item data-haystack="{{ mb_strtolower($item->product_name.' '.($item->size_name ?? '')) }}">
                            <label class="item-main">
                                <input
                                    type="checkbox"
                                    form="cart-checkout-form"
                                    name="selected_items[]"
                                    value="{{ $item->cart_item_id }}"
                                    data-line-total="{{ (float) $item->base_price * $item->quantity }}"
                                    class="item-checkbox item-check"
                                    @if ($usable) checked @else disabled @endif
                                >
                                <span class="thumb">@if ($thumb)<img src="{{ asset('storage/'.ltrim($thumb, '/')) }}" alt="" loading="lazy">@else{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item->product_name, 0, 1)) }}@endif</span>
                                <span style="min-width:0">
                                    <h2 class="item-name">{{ $item->product_name }}</h2>
                                    @if ($item->size_name)<span class="chip">{{ $item->size_name }}</span>@endif
                                    <p class="each">₱{{ number_format((float) $item->base_price, 2) }} each</p>
                                    @if (! $item->is_available || ! $item->size_is_active || $item->stock_quantity < 1)
                                        <p class="warn">Currently unavailable — remove this item to continue.</p>
                                    @elseif ($item->quantity > $item->stock_quantity)
                                        <p class="warn">Only {{ $item->stock_quantity }} in stock. Reduce the quantity.</p>
                                    @endif
                                </span>
                            </label>

                            <div class="item-tools">
                                @if ($editable)
                                    <form class="qty-form" method="POST" action="{{ route('cart.items.update', $item->cart_item_id) }}" data-qty-form>
                                        @csrf
                                        @method('PUT')
                                        <label class="sr" for="quantity-{{ $item->cart_item_id }}">Quantity for {{ $item->product_name }}</label>
                                        <div class="qty">
                                            <button type="button" data-minus aria-label="Decrease quantity">−</button>
                                            <input id="quantity-{{ $item->cart_item_id }}" name="quantity" type="number" value="{{ $item->quantity }}" data-original="{{ $item->quantity }}" min="1" max="{{ min($item->stock_quantity, 99) }}" required>
                                            <button type="button" data-plus aria-label="Increase quantity">+</button>
                                        </div>
                                        <button class="update-btn" type="submit" data-update hidden>Update</button>
                                    </form>
                                @else
                                    <span class="qty-static">Qty {{ $item->quantity }}</span>
                                @endif

                                <p class="line-total">₱{{ number_format((float) $item->base_price * $item->quantity, 2) }}</p>

                                <form method="POST" action="{{ route('cart.items.destroy', $item->cart_item_id) }}" data-remove-form>
                                    @csrf
                                    @method('DELETE')
                                    <button class="remove" type="submit" aria-label="Remove {{ $item->product_name }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['trash'] !!}</svg>Remove</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                    <p class="nomatch" id="cart-nomatch">No items in your cart match your search.</p>
                </section>

                <aside class="panel summary" aria-labelledby="summary-title">
                    <h2 class="display" id="summary-title">Order summary</h2>
                    <p class="selected-line"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['cart'] !!}</svg><span><span id="selected-count-side">{{ $items->count() }}</span> selected for checkout</span></p>
                    <div style="margin-top:14px">
                        <div class="s-row"><span>Subtotal</span><strong id="cart-summary-subtotal">₱{{ number_format($subtotal, 2) }}</strong></div>
                        <div class="s-row"><span>Delivery fee</span><strong id="cart-delivery">@if ($deliveryFee !== null)₱{{ number_format($deliveryFee, 2) }}@else Confirmed by the store @endif</strong></div>
                    </div>
                    <div class="s-total"><span>{{ $deliveryFee !== null ? 'Total' : 'Total before delivery' }}</span><span class="display" id="cart-summary-total">₱{{ number_format($subtotal + ($deliveryFee ?? 0), 2) }}</span></div>
                    <p class="note">Delivery fees, if applicable, will be confirmed by the store.</p>

                    {{-- Separate checkout form --}}
                    <form id="cart-checkout-form" method="POST" action="{{ route('cart.checkout.select') }}">
                        @csrf
                        <div id="checkout-error" class="err-inline" role="alert" hidden>Please select at least one item to continue to checkout.</div>
                        <button type="submit" class="pill-button btn-primary" style="width:100%;margin-top:20px;padding:17px 24px">
                            <svg class="ico" viewBox="0 0 24 24" style="transform:none" aria-hidden="true">{!! $icons['lock'] !!}</svg>Continue to checkout
                        </button>
                    </form>
                    <a class="pill-button btn-outline" style="width:100%;margin-top:12px" href="{{ route('products.index') }}"><svg class="ico" viewBox="0 0 24 24" style="transform:none" aria-hidden="true">{!! $icons['back'] !!}</svg>Continue shopping</a>
                </aside>
            </div>

            <script>
            (function () {
                var selectAll = document.getElementById('select-all-items');
                var checkboxes = Array.from(document.querySelectorAll('.item-checkbox:not([disabled])'));
                var countEl = document.getElementById('selected-count');
                var countSide = document.getElementById('selected-count-side');
                var checkoutForm = document.getElementById('cart-checkout-form');
                var subtotalEl = document.getElementById('cart-summary-subtotal');
                var totalEl = document.getElementById('cart-summary-total');
                var errorEl = document.getElementById('checkout-error');
                var fee = {{ $deliveryFee !== null ? $deliveryFee : 'null' }};
                var shown = { sub: parseFloat('{{ (float) $subtotal }}') || 0 };
                var raf = null;

                function money(n) { return '₱' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
                function paint(sub) {
                    subtotalEl.textContent = money(sub);
                    if (totalEl) { totalEl.textContent = money(sub + (fee || 0)); }
                }
                function tween(to) {
                    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    if (raf) { cancelAnimationFrame(raf); }
                    if (reduce) { shown.sub = to; paint(to); return; }
                    var from = shown.sub, t0 = null;
                    raf = requestAnimationFrame(function step(ts) {
                        if (t0 === null) { t0 = ts; }
                        var k = Math.min((ts - t0) / 420, 1), e = 1 - Math.pow(1 - k, 3);
                        shown.sub = from + (to - from) * e;
                        paint(shown.sub);
                        if (k < 1) { raf = requestAnimationFrame(step); } else { shown.sub = to; paint(to); }
                    });
                }

                function updateCount() {
                    var checkedBoxes = checkboxes.filter(function (cb) { return cb.checked; });
                    var checked = checkedBoxes.length;
                    countEl.textContent = checked;
                    if (countSide) { countSide.textContent = checked; }
                    selectAll.indeterminate = checked > 0 && checked < checkboxes.length;
                    selectAll.checked = checkboxes.length > 0 && checked === checkboxes.length;
                    checkboxes.forEach(function (cb) { cb.closest('.item').classList.toggle('is-off', !cb.checked); });
                    if (checked > 0 && errorEl) { errorEl.hidden = true; }
                    var sum = checkedBoxes.reduce(function (total, cb) { return total + (parseFloat(cb.dataset.lineTotal) || 0); }, 0);
                    tween(sum);
                }

                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(function (cb) { cb.checked = selectAll.checked; });
                    updateCount();
                });
                checkboxes.forEach(function (cb) { cb.addEventListener('change', updateCount); });

                checkoutForm.addEventListener('submit', function (e) {
                    var checked = checkboxes.filter(function (cb) { return cb.checked; }).length;
                    if (checked === 0) {
                        e.preventDefault();
                        errorEl.hidden = false;
                        errorEl.style.animation = 'none'; void errorEl.offsetWidth; errorEl.style.animation = '';
                    }
                });

                // Quantity steppers: the Update button appears only when the number changed (the form still posts normally)
                document.querySelectorAll('[data-qty-form]').forEach(function (form) {
                    var input = form.querySelector('input[name="quantity"]');
                    var btn = form.querySelector('[data-update]');
                    function sync() { btn.hidden = String(input.value) === String(input.getAttribute('data-original')); }
                    form.querySelector('[data-minus]').addEventListener('click', function () {
                        var v = parseInt(input.value, 10) || 1;
                        if (v > parseInt(input.min, 10)) { input.value = v - 1; sync(); }
                    });
                    form.querySelector('[data-plus]').addEventListener('click', function () {
                        var v = parseInt(input.value, 10) || 1;
                        if (v < parseInt(input.max, 10)) { input.value = v + 1; sync(); }
                    });
                    input.addEventListener('input', sync);
                    form.addEventListener('submit', function () { btn.textContent = 'Updating…'; setTimeout(function () { btn.disabled = true; }, 0); });
                });

                // Slide the row out when removing (the form still posts normally)
                document.querySelectorAll('[data-remove-form]').forEach(function (form) {
                    form.addEventListener('submit', function () { form.closest('.item').classList.add('is-removing'); });
                });

                // Search your cart (filters the items shown here)
                var inputs = Array.from(document.querySelectorAll('[data-cart-search]'));
                var rows = Array.from(document.querySelectorAll('[data-item]'));
                var none = document.getElementById('cart-nomatch');
                function run(v) {
                    var q = v.trim().toLowerCase(), n = 0;
                    rows.forEach(function (r) { var ok = !q || r.getAttribute('data-haystack').indexOf(q) !== -1; r.hidden = !ok; if (ok) { n++; } });
                    if (none) { none.classList.toggle('is-on', q !== '' && n === 0); }
                }
                inputs.forEach(function (input) {
                    input.addEventListener('input', function () {
                        inputs.forEach(function (o) { if (o !== input) { o.value = input.value; } });
                        run(input.value);
                    });
                });

                updateCount();
            }());
            </script>
        @endif
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
            var burger = document.getElementById('burger'), panel = document.getElementById('mobile-panel');
            if (burger && panel) {
                burger.addEventListener('click', function () { panel.hidden = !panel.hidden; burger.setAttribute('aria-expanded', String(!panel.hidden)); });
            }
        })();
    </script>
</body>
</html>