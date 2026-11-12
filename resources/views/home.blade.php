<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fff7ef">
    <meta name="description" content="Freshly made drinks and sweet treats from SipPly. Find your next favorite and order today.">
    <title>SipPly — A little joy in every sip</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ------------------------------------------------------------
           SipPly home — design tokens (8px spacing grid, warm neutrals)
        ------------------------------------------------------------ */
        :root {
            --cream:#fff7ef; --paper:#fffdf9; --sand:#f8e9da; --sand-2:#f1d9c3;
            --brown:#4b2413; --brown-2:#6b351c; --caramel:#c75f26; --ink:#382116; --muted:#7a5a48;
            --line:rgba(75,36,19,.1);
            --shadow-sm:0 1px 2px rgba(75,36,19,.06), 0 4px 16px rgba(75,36,19,.05);
            --shadow-md:0 2px 4px rgba(75,36,19,.05), 0 18px 40px rgba(75,36,19,.1);
        }
        html { scroll-behavior:smooth; }
        body { background:var(--cream); color:var(--ink); }
        a:focus-visible, button:focus-visible { outline:2px solid var(--caramel); outline-offset:3px; border-radius:8px; }

        .display { font-family:Georgia,'Times New Roman',serif; }
        .site-shell { width:min(1240px, calc(100% - 48px)); margin-inline:auto; }
        .eyebrow { letter-spacing:.16em; text-transform:uppercase; font-size:11px; font-weight:800; color:var(--caramel); }
        .ico { fill:none; stroke:currentColor; stroke-width:1.75; stroke-linecap:round; stroke-linejoin:round; flex-shrink:0; }

        /* Header (logo only) */
        .site-header { position:relative; z-index:30; width:min(1240px, calc(100% - 48px)); margin:0 auto; }
        .header-inner { display:flex; align-items:center; justify-content:space-between; gap:20px; min-height:76px; }
        .site-nav { display:flex; align-items:center; gap:4px; }
        .nav-link { padding:10px 16px; border-radius:999px; font-size:14px; font-weight:600; color:#71432d; transition:background-color .2s ease, color .2s ease; }
        .nav-link:hover { background:rgba(75,36,19,.07); color:var(--brown); }
        .nav-account { display:inline-flex; align-items:center; gap:8px; margin-left:8px; padding:10px 18px; border-radius:999px; background:var(--brown); color:#fff7ef; font-size:14px; font-weight:700; transition:background-color .2s ease, transform .2s ease; }
        .nav-account:hover { background:var(--brown-2); transform:translateY(-1px); }
        .brand-logo { width:44px; height:44px; object-fit:contain; border-radius:50%; background:var(--paper); }

        /* Buttons */
        .pill-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:999px; padding:14px 24px; font-size:14px; font-weight:700; transition:transform .2s ease, background-color .2s ease, box-shadow .2s ease; }
        .pill-button:hover { transform:translateY(-2px); }
        .pill-button:active { transform:translateY(0) scale(.98); }
        .btn-primary { background:var(--brown); color:#fff7ef; box-shadow:0 8px 20px rgba(75,36,19,.22); }
        .btn-primary:hover { background:var(--brown-2); }
        .btn-ghost { background:transparent; color:var(--brown); border:1px solid rgba(75,36,19,.22); }
        .btn-ghost:hover { background:rgba(255,253,249,.8); }

        /* Hero */
        .hero { isolation:isolate; background:linear-gradient(135deg,#fff3e6 0%,#fbe3cc 100%); border:1px solid var(--line); }
        .coffee-shader { display:none; } /* kept in the DOM so existing JS never breaks; hidden for a cleaner hero */
        .hero-art { position:relative; display:grid; place-items:center; min-height:360px; }
        .hero-art::before { content:""; position:absolute; width:min(78%, 440px); aspect-ratio:1; border-radius:50%; background:linear-gradient(160deg,#f6c9a0 0%,#e39a62 100%); opacity:.55; z-index:-1; }
        .hero-cup { position:relative; height:clamp(340px, 42vw, 540px); width:auto; max-width:100%; object-fit:contain; filter:drop-shadow(0 28px 32px rgba(75,36,19,.28)); animation:cup-float 7s ease-in-out infinite; }
        @keyframes cup-float { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-10px); } }
        .hero h1 span { color:var(--caramel); font-style:italic; }

        /* Live hero effects: spreading milk, ripples, bouncing boba, drifting leaves */
        .hero-cup { z-index:1; }
        .fx { position:absolute; inset:0; z-index:0; pointer-events:none; overflow:hidden; }
        .fx > * { position:absolute; }

        .milk-goo { inset:0; filter:url(#milk-goo); opacity:.92; }
        .milk-goo > span { position:absolute; border-radius:50%; background:#fff6ec; will-change:transform; }
        .blob-a { width:46%; aspect-ratio:1; left:14%; bottom:6%;  animation:goo-a 8s ease-in-out infinite; }
        .blob-b { width:40%; aspect-ratio:1; right:12%; bottom:10%; animation:goo-b 10s ease-in-out infinite; }
        .blob-c { width:28%; aspect-ratio:1; left:36%; bottom:28%; animation:goo-c 7s ease-in-out infinite; }
        .blob-d { width:22%; aspect-ratio:1; right:6%; top:14%;   animation:goo-d 12s ease-in-out infinite; }
        .blob-e { width:15%; aspect-ratio:1; left:9%; top:22%;    animation:goo-a 9s ease-in-out -3s infinite reverse; }
        @keyframes goo-a { 0%,100% { transform:translate(0,0) scale(1); } 25% { transform:translate(45%,-30%) scale(1.25); } 50% { transform:translate(80%,8%) scale(.78); } 75% { transform:translate(20%,32%) scale(1.15); } }
        @keyframes goo-b { 0%,100% { transform:translate(0,0) scale(1); } 30% { transform:translate(-55%,-22%) scale(1.22); } 60% { transform:translate(-30%,28%) scale(.84); } }
        @keyframes goo-c { 0%,100% { transform:translate(0,0) scale(1); } 50% { transform:translate(35%,-70%) scale(1.35); } }
        @keyframes goo-d { 0%,100% { transform:translate(0,0) scale(1); } 40% { transform:translate(-60%,60%) scale(1.3); } 70% { transform:translate(-20%,110%) scale(.8); } }

        /* milk drops that fall and melt into the blobs */
        .milk-drop { width:13px; height:13px; top:0; opacity:0; animation:milk-drop 5.5s cubic-bezier(.5,0,1,.6) infinite; }
        .drop-1 { left:24%; }
        .drop-2 { left:70%; animation-delay:2.7s; width:11px; height:11px; }
        @keyframes milk-drop {
            0%   { transform:translateY(-10px) scale(.5,.5); opacity:0; }
            8%   { opacity:1; transform:translateY(0) scale(.9,1.1); }
            55%  { transform:translateY(240px) scale(.8,1.5); opacity:1; }
            62%  { transform:translateY(260px) scale(1.3,.6); opacity:0; }
            100% { transform:translateY(260px) scale(1,1); opacity:0; }
        }

        .ripple { left:50%; bottom:3%; width:64%; aspect-ratio:3/1; border:2px solid rgba(255,255,255,.9); border-radius:50%; opacity:0; animation:ripple 4.2s ease-out infinite; }
        .ripple-2 { animation-delay:1.4s; }
        .ripple-3 { animation-delay:2.8s; }
        @keyframes ripple {
            0%   { transform:translateX(-50%) scale(.35); opacity:.9; }
            100% { transform:translateX(-50%) scale(1.45); opacity:0; }
        }

        .boba { width:var(--s,22px); height:var(--s,22px); bottom:5%; border-radius:50%; background:radial-gradient(circle at 32% 28%, #9b6a48 0, #4a2412 48%, #1f0d05 100%); box-shadow:0 4px 6px rgba(31,13,5,.3), inset -2px -3px 4px rgba(0,0,0,.35); transform-origin:50% 100%; animation:boba-jelly var(--t,4.6s) linear infinite backwards; animation-delay:var(--d,0s); }
        .boba-1 { left:12%;  --s:24px; --d:0s;   --t:4.4s; --dir:-1; }
        .boba-2 { left:21%;  --s:18px; --d:.8s;  --t:5.1s; --dir:-1; }
        .boba-3 { left:28%;  --s:20px; --d:1.7s; --t:4.7s; --dir:1; }
        .boba-4 { right:26%; --s:20px; --d:.4s;  --t:4.9s; --dir:-1; }
        .boba-5 { right:17%; --s:26px; --d:1.3s; --t:4.3s; --dir:1; }
        .boba-6 { right:9%;  --s:18px; --d:2.2s; --t:5.3s; --dir:1; }
        /* jelly physics: stretch while falling, big squash on landing, shrinking rebounds, then a little roll and wobble */
        @keyframes boba-jelly {
            0% { transform:translate(calc(0px * var(--dir,1)),-170px) scale(0.9,1.15) rotate(calc(0deg * var(--dir,1))); opacity:0; animation-timing-function:cubic-bezier(.5,0,1,.5); }
            6% { opacity:1; }
            26% { transform:translate(calc(0px * var(--dir,1)),0px) scale(0.9,1.15) rotate(calc(0deg * var(--dir,1))); animation-timing-function:ease-out; }
            29% { transform:translate(calc(0px * var(--dir,1)),0px) scale(1.45,0.58) rotate(calc(0deg * var(--dir,1))); animation-timing-function:cubic-bezier(.2,.7,.3,1); }
            40% { transform:translate(calc(4px * var(--dir,1)),-78px) scale(0.88,1.18) rotate(calc(6deg * var(--dir,1))); animation-timing-function:cubic-bezier(.5,0,1,.5); }
            50% { transform:translate(calc(8px * var(--dir,1)),0px) scale(0.95,1.08) rotate(calc(10deg * var(--dir,1))); animation-timing-function:ease-out; }
            52% { transform:translate(calc(8px * var(--dir,1)),0px) scale(1.28,0.72) rotate(calc(10deg * var(--dir,1))); animation-timing-function:cubic-bezier(.2,.7,.3,1); }
            60% { transform:translate(calc(14px * var(--dir,1)),-34px) scale(0.94,1.1) rotate(calc(16deg * var(--dir,1))); animation-timing-function:cubic-bezier(.5,0,1,.5); }
            67% { transform:translate(calc(18px * var(--dir,1)),0px) scale(1,1) rotate(calc(20deg * var(--dir,1))); animation-timing-function:ease-out; }
            69% { transform:translate(calc(18px * var(--dir,1)),0px) scale(1.14,0.86) rotate(calc(20deg * var(--dir,1))); animation-timing-function:cubic-bezier(.2,.7,.3,1); }
            75% { transform:translate(calc(21px * var(--dir,1)),-12px) scale(0.97,1.05) rotate(calc(22deg * var(--dir,1))); animation-timing-function:cubic-bezier(.5,0,1,.5); }
            80% { transform:translate(calc(23px * var(--dir,1)),0px) scale(1.08,0.92) rotate(calc(24deg * var(--dir,1))); animation-timing-function:ease-in-out; }
            84% { transform:translate(calc(23px * var(--dir,1)),0px) scale(0.97,1.04) rotate(calc(24deg * var(--dir,1))); animation-timing-function:ease-in-out; }
            88% { transform:translate(calc(23px * var(--dir,1)),0px) scale(1.02,0.98) rotate(calc(24deg * var(--dir,1))); opacity:1; }
            100% { transform:translate(calc(23px * var(--dir,1)),0px) scale(1,1) rotate(calc(24deg * var(--dir,1))); opacity:0; }
        }

        .leaf { width:var(--s,40px); height:var(--s,40px); color:var(--c,#7f9f60); }
        .leaf path:first-child { fill:currentColor; }
        .leaf path:last-child { fill:none; stroke:rgba(255,255,255,.55); stroke-width:1; stroke-linecap:round; }
        .leaf-1 { --s:46px; --c:#7f9f60; top:8%; left:5%; animation:leaf-sway 6s ease-in-out infinite; }
        .leaf-2 { --s:36px; --c:#6f8f55; top:5%; right:9%; animation:leaf-sway 7s ease-in-out -2s infinite reverse; }
        .leaf-3 { --s:28px; --c:#93b26f; top:46%; left:0; animation:leaf-sway 5.5s ease-in-out -1s infinite; }
        @keyframes leaf-sway {
            0%,100% { transform:translate(0,0) rotate(-12deg); }
            50%     { transform:translate(12px,-16px) rotate(16deg); }
        }
        .leaf-fall { top:-8%; opacity:0; animation:leaf-fall 9s ease-in-out infinite; }
        .leaf-fall-1 { --s:26px; --c:#8aa86b; left:30%; }
        .leaf-fall-2 { --s:22px; --c:#6f8f55; right:28%; animation-delay:4.5s; animation-duration:11s; }
        @keyframes leaf-fall {
            0%   { transform:translate(0,0) rotate(0deg); opacity:0; }
            10%  { opacity:.95; }
            30%  { transform:translate(26px,110px) rotate(70deg); }
            55%  { transform:translate(-22px,210px) rotate(160deg); }
            80%  { transform:translate(20px,300px) rotate(250deg); opacity:.95; }
            100% { transform:translate(-6px,380px) rotate(330deg); opacity:0; }
        }

        /* Entrance + scroll reveal */
        @keyframes rise { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:none; } }
        .rise { opacity:0; animation:rise .7s cubic-bezier(.2,.7,.2,1) forwards; }
        .rise-1 { animation-delay:.05s; } .rise-2 { animation-delay:.15s; } .rise-3 { animation-delay:.25s; } .rise-4 { animation-delay:.35s; }
        .reveal-ready [data-reveal] { opacity:0; transform:translateY(18px); transition:opacity .6s ease, transform .6s ease; }
        .reveal-ready [data-reveal].is-visible { opacity:1; transform:none; }

        /* Cards */
        .category-card, .product-card { transition:transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
        .category-card:hover, .product-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); border-color:rgba(199,95,38,.35); }
        .product-image { aspect-ratio:1.18 / 1; object-fit:cover; width:100%; }

        footer { background:#3b1c0e; }

        @media (max-width:767px) {
            .site-shell { width:min(100% - 32px, 600px); }
            .site-header { width:min(100% - 32px, 600px); }
            .header-inner { flex-wrap:wrap; gap:4px 12px; padding-block:12px; min-height:64px; }
            .site-nav { width:100%; overflow-x:auto; scrollbar-width:none; padding-bottom:4px; }
            .site-nav::-webkit-scrollbar { display:none; }
            .nav-link, .nav-account { white-space:nowrap; }
            .nav-account { margin-left:auto; }
            .hero-art { min-height:300px; }
            .leaf-3, .boba-3, .boba-6 { display:none; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration:.01ms !important; animation-iteration-count:1 !important; transition-duration:.01ms !important; scroll-behavior:auto !important; }
            .rise { opacity:1; }
            .fx *, .hero-cup { animation:none !important; }
            .ripple, .leaf-fall, .milk-drop { display:none; }
        }
    </style>
</head>
<body class="warm-theme min-h-screen antialiased">
    <header class="site-header">
        <div class="header-inner">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="SipPly home">
                <img class="brand-logo" src="{{ asset('images/sipply-logo.png') }}" alt="SipPly Coffee Shop logo" width="44" height="44">
                <span class="display text-[22px] font-bold tracking-[-1px] text-[#4b2413]">SipPly</span>
            </a>

            <nav class="site-nav" aria-label="Main navigation">
                <a class="nav-link" href="{{ route('categories.show', 'coffee') }}">Coffee</a>
                <a class="nav-link" href="{{ route('categories.show', 'milk-tea') }}">Milk Tea</a>
                <a class="nav-link" href="{{ route('categories.show', 'pastries') }}">Pastries</a>
                <a class="nav-account" href="{{ auth()->check() ? route('account') : route('login') }}">
                    <svg class="ico size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    My account
                </a>
            </nav>
        </div>
    </header>

    <main>
        {{-- Hero --}}
        <section class="site-shell pt-6 sm:pt-8">
            <div class="hero relative overflow-hidden rounded-[32px] px-6 py-10 sm:px-10 lg:px-16 lg:py-14">
                <canvas class="coffee-shader" data-coffee-shader aria-hidden="true"></canvas>
                <div class="relative z-10 grid items-center gap-8 lg:grid-cols-[1.05fr_.95fr]">
                    <div class="max-w-[580px]">
                        <div class="rise rise-1 mb-6 inline-flex items-center gap-2 rounded-full bg-white/70 px-4 py-2 text-xs font-bold text-[#6b351c] ring-1 ring-[#4b2413]/10">
                            <svg class="ico size-4 text-[#c75f26]" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg>
                            Your little moment of refresh
                        </div>
                        <h1 class="rise rise-2 display text-[42px] font-bold leading-[1.04] tracking-[-1.5px] text-[#3d1c0d] sm:text-6xl lg:text-[64px]">Freshly made happiness,<br><span>one sip at a time.</span></h1>
                        <p class="rise rise-3 mt-5 max-w-md text-base leading-7 text-[#664734]">Find your next favorite, made fresh and ready for your day.</p>
                        <div class="rise rise-4 mt-8 flex flex-wrap items-center gap-3">
                            <a href="{{ route('products.index') }}" class="pill-button btn-primary">Shop now
                                <svg class="ico size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </a>
                            <a href="#about" class="pill-button btn-ghost">Our story</a>
                        </div>
                    </div>
                    <div class="hero-art rise rise-3">
                        <div class="fx" aria-hidden="true">
                            <svg width="0" height="0" style="position:absolute" focusable="false"><defs><filter id="milk-goo" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur in="SourceGraphic" stdDeviation="11" result="blur"/><feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 22 -9" result="goo"/><feComposite in="SourceGraphic" in2="goo" operator="atop"/></filter></defs></svg>
                            <div class="milk-goo">
                                <span class="blob-a"></span><span class="blob-b"></span><span class="blob-c"></span><span class="blob-d"></span><span class="blob-e"></span>
                                <span class="milk-drop drop-1"></span><span class="milk-drop drop-2"></span>
                            </div>
                            <span class="ripple"></span><span class="ripple ripple-2"></span><span class="ripple ripple-3"></span>
                            <svg class="leaf leaf-1" viewBox="0 0 24 24"><path d="M21 3C10 3 3 9.5 3 18c0 1.2.2 2.2.5 3C12 21 21 15 21 3Z"/><path d="M3.5 21C8 14 12 11 16 9"/></svg>
                            <svg class="leaf leaf-2" viewBox="0 0 24 24"><path d="M21 3C10 3 3 9.5 3 18c0 1.2.2 2.2.5 3C12 21 21 15 21 3Z"/><path d="M3.5 21C8 14 12 11 16 9"/></svg>
                            <svg class="leaf leaf-3" viewBox="0 0 24 24"><path d="M21 3C10 3 3 9.5 3 18c0 1.2.2 2.2.5 3C12 21 21 15 21 3Z"/><path d="M3.5 21C8 14 12 11 16 9"/></svg>
                            <svg class="leaf leaf-fall leaf-fall-1" viewBox="0 0 24 24"><path d="M21 3C10 3 3 9.5 3 18c0 1.2.2 2.2.5 3C12 21 21 15 21 3Z"/><path d="M3.5 21C8 14 12 11 16 9"/></svg>
                            <svg class="leaf leaf-fall leaf-fall-2" viewBox="0 0 24 24"><path d="M21 3C10 3 3 9.5 3 18c0 1.2.2 2.2.5 3C12 21 21 15 21 3Z"/><path d="M3.5 21C8 14 12 11 16 9"/></svg>
                            <span class="boba boba-1"></span>
                            <span class="boba boba-2"></span>
                            <span class="boba boba-3"></span>
                            <span class="boba boba-4"></span>
                            <span class="boba boba-5"></span>
                            <span class="boba boba-6"></span>
                        </div>
                        <img class="hero-cup" src="{{ asset('images/sipply-hero.webp') }}" alt="SipPly iced latte with the SipPly logo" width="800" height="1200" fetchpriority="high">
                    </div>
                </div>
            </div>
        </section>

        {{-- Highlights --}}
        <section class="site-shell pt-5" aria-label="Why SipPly">
            <ul class="grid divide-y divide-[#4b2413]/10 overflow-hidden rounded-[24px] border border-[#4b2413]/10 bg-[#fffdf9] shadow-[0_1px_2px_rgba(75,36,19,.06)] sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                <li class="flex items-center gap-4 px-6 py-5">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-[#f8e9da] text-[#c75f26]"><svg class="ico size-5" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h12l4 6-10 13L2 9Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg></span>
                    <p class="text-sm font-bold text-[#4b2413]">Premium quality<span class="mt-0.5 block text-xs font-normal text-[#7a5a48]">Only the best ingredients</span></p>
                </li>
                <li class="flex items-center gap-4 px-6 py-5">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-[#f8e9da] text-[#c75f26]"><svg class="ico size-5" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/></svg></span>
                    <p class="text-sm font-bold text-[#4b2413]">Fast service<span class="mt-0.5 block text-xs font-normal text-[#7a5a48]">Freshness without the wait</span></p>
                </li>
                <li class="flex items-center gap-4 px-6 py-5">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-[#f8e9da] text-[#c75f26]"><svg class="ico size-5" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg></span>
                    <p class="text-sm font-bold text-[#4b2413]">Made with love<span class="mt-0.5 block text-xs font-normal text-[#7a5a48]">In every cup</span></p>
                </li>
            </ul>
        </section>

        {{-- Promotions --}}
        @if ($promotions->isNotEmpty())
            <section class="site-shell pt-16" aria-labelledby="offers-title" data-reveal>
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div><p class="eyebrow">Good things are happening</p><h2 id="offers-title" class="display mt-2 text-3xl tracking-[-.7px] text-[#4b2413] sm:text-4xl">A little extra for you</h2></div>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm font-bold text-[#6b351c] hover:text-[#c75f26]">Shop all <svg class="ico size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg></a>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($promotions as $promotion)
                        <article class="relative overflow-hidden rounded-[24px] border border-[#4b2413]/10 bg-[#f8e9da] p-6 sm:p-8">
                            <p class="eyebrow flex items-center gap-2"><svg class="ico size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/></svg>SipPly special</p>
                            <h3 class="display mt-3 text-2xl text-[#4b2413]">{{ $promotion->title }}</h3>
                            <p class="mt-2 max-w-lg text-sm leading-6 text-[#6b4a38]">{{ $promotion->description }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Categories --}}


        {{-- About + contact --}}
        <section id="about" class="site-shell scroll-mt-28 py-16 sm:py-20" data-reveal>
            <div class="grid gap-10 lg:grid-cols-[1.1fr_.9fr] lg:items-center">
                <div>
                    <p class="eyebrow">A fresh little pause</p>
                    <h2 class="display mt-3 max-w-xl text-4xl leading-[1.1] tracking-[-1px] text-[#4b2413] sm:text-5xl">Small sips. <span class="italic text-[#c75f26]">Big smiles.</span></h2>
                    <p class="mt-5 max-w-lg text-base leading-7 text-[#6b4a38]">SipPly brings freshly made drinks and sweet treats to the Bantayan community. Whether you're taking a quick break or catching up with friends, there's always a little something to brighten your day.</p>
                    <a href="{{ route('products.index') }}" class="pill-button btn-primary mt-8">Find your next favorite <svg class="ico size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
                </div>
                <div class="rounded-[28px] border border-[#4b2413]/10 bg-[#fffdf9] p-6 shadow-[0_1px_2px_rgba(75,36,19,.06),0_16px_40px_rgba(75,36,19,.07)] sm:p-8">
                    <p class="eyebrow">Come say hello</p>
                    <h3 class="display mt-2 text-2xl text-[#4b2413]">We'd love to see you.</h3>
                    <div class="mt-6 space-y-5 text-sm text-[#6b4a38]">
                        <div class="flex gap-4"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#f8e9da] text-[#c75f26]"><svg class="ico size-5" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg></span><p><strong class="mb-0.5 block text-[#4b2413]">Visit us</strong>Bantayan Public Market area, Bantayan, Cebu</p></div>
                        <div class="flex gap-4"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#f8e9da] text-[#c75f26]"><svg class="ico size-5" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span><p><strong class="mb-0.5 block text-[#4b2413]">Call</strong><a class="hover:underline" href="tel:+639175550142">+63 917 555 0142</a></p></div>
                        <div class="flex gap-4"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#f8e9da] text-[#c75f26]"><svg class="ico size-5" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></span><p><strong class="mb-0.5 block text-[#4b2413]">Email</strong><a class="hover:underline" href="mailto:hello@sipply.ph">hello@sipply.ph</a></p></div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('partials.footer')

    {{-- Scroll reveal (additive; your existing app.js is untouched) --}}
    <script>
        (function () {
            var items = document.querySelectorAll('[data-reveal]');
            var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (!items.length || reduce || !('IntersectionObserver' in window)) { return; }
            document.documentElement.classList.add('reveal-ready');
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target); }
                });
            }, { threshold: 0.08 });
            items.forEach(function (el) { observer.observe(el); });
        })();
    </script>
</body>
</html>