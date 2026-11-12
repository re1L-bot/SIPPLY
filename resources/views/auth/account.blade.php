<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FFF7EF">
    <title>My account | SipPly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --cream:#FFF7EF; --espresso:#542812; --coffee:#75452D; --caramel:#D99A69; --beige:#F5E2D1; --card:#FFFCF8; --ink:#382015; --muted:#8C7769; --label:#C2622A; --line:rgba(84,40,18,.10);
                /* legacy names, so shared partials keep working */ --brown:#542812; --brown-2:#75452D; --paper:#FFFCF8; --sand:#F5E2D1; }
        @view-transition { navigation: auto; }
        *, *::before, *::after { box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body { margin:0; font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,sans-serif; background:var(--cream); color:var(--ink); -webkit-font-smoothing:antialiased; animation:page-in .5s ease; }
        @keyframes page-in { from { opacity:0; } }
        a { color:inherit; text-decoration:none; }
        [hidden] { display:none !important; }
        :focus-visible { outline:2px solid var(--caramel); outline-offset:3px; border-radius:12px; }
        .display { font-family:'Playfair Display',Georgia,serif; }
        .ico { fill:none; stroke:currentColor; stroke-width:1.75; stroke-linecap:round; stroke-linejoin:round; flex-shrink:0; }
        :where(.ico) { width:1.25rem; height:1.25rem; }
        .sp-wrap { position:relative; z-index:1; width:min(1240px, calc(100% - 40px)); margin-inline:auto; }
        .eyebrow { display:block; font-size:12px; font-weight:800; letter-spacing:.2em; text-transform:uppercase; color:var(--label); }

        /* Background decoration */
        .deco { position:fixed; z-index:0; pointer-events:none; }
        .deco-leaves { left:-30px; top:30%; width:200px; opacity:.7; }
        .deco-blob { left:-110px; bottom:-120px; width:380px; height:380px; border-radius:50%; background:linear-gradient(140deg,#6b351c,var(--espresso)); }
        .deco-script { left:26px; bottom:26px; z-index:0; font:italic 500 22px/1.15 'Playfair Display',serif; color:#f5e2d1; }
        .deco-bean { position:fixed; z-index:0; pointer-events:none; width:30px; height:19px; border-radius:50%; background:linear-gradient(135deg,#7a4426,#3a1a0b); transform:rotate(var(--r)); box-shadow:0 4px 8px rgba(56,32,21,.25); }
        .deco-bean::after, .m-bean::after { content:""; position:absolute; inset:8px 4px auto; height:2px; border-radius:2px; background:#1d0c04; }
        @media (max-width:1100px) { .deco, .deco-bean { display:none; } }

        /* Floating navbar */
        .nav-wrap { position:sticky; top:12px; z-index:50; padding-top:12px; }
        .nav { display:flex; align-items:center; gap:18px; padding:10px 14px 10px 24px; border-radius:999px; background:rgba(255,252,248,.9); -webkit-backdrop-filter:blur(16px); backdrop-filter:blur(16px); border:1px solid rgba(217,154,105,.25); box-shadow:0 10px 30px rgba(84,40,18,.09); }
        .brand { display:flex; align-items:center; gap:10px; flex-shrink:0; }
        .brand img { width:46px; height:46px; border-radius:50%; object-fit:cover; }
        .brand span { font-family:'Playfair Display',serif; font-size:26px; font-weight:700; letter-spacing:-.5px; color:var(--espresso); }
        .nav-search { position:relative; display:flex; align-items:center; flex:1; min-width:0; max-width:460px; height:46px; padding:0 18px 0 46px; border-radius:99px; background:#fff; border:1px solid var(--line); transition:box-shadow .25s ease, border-color .25s ease; }
        .nav-search:hover { border-color:var(--caramel); }
        .nav-search:focus-within { border-color:var(--caramel); box-shadow:0 0 0 4px rgba(217,154,105,.25); }
        .nav-search svg { position:absolute; left:16px; width:18px; height:18px; color:var(--coffee); transition:transform .3s ease; }
        .nav-search:focus-within svg { transform:scale(1.15); }
        .nav-search input { flex:1; min-width:0; border:0; outline:0; background:transparent; font:500 14px 'Plus Jakarta Sans',sans-serif; color:var(--ink); }
        .nav-links { display:flex; align-items:center; gap:4px; margin-left:auto; }
        .nav-link { display:inline-flex; align-items:center; gap:8px; padding:10px 16px; border-radius:99px; font-size:14px; font-weight:600; color:var(--coffee); white-space:nowrap; transition:background-color .25s ease, color .25s ease, transform .25s ease; }
        .nav-link:hover { background:var(--beige); color:var(--espresso); transform:translateY(-1px); }
        .nav-link .ico { width:17px; height:17px; transition:transform .25s ease; }
        .nav-link:hover .ico { transform:scale(1.12); }
        .nav-link.is-dark { background:var(--espresso); color:#fff7ef; margin-left:6px; box-shadow:0 6px 16px rgba(84,40,18,.25); }
        .nav-link.is-dark:hover { background:var(--coffee); }
        .nav-link.is-outline { border:1px solid rgba(84,40,18,.22); background:transparent; color:var(--espresso); cursor:pointer; font-family:inherit; }
        .nav-link.is-outline:hover { background:#fff; }
        .nav-sep { width:1px; height:26px; margin:0 6px; background:var(--line); }
        .burger { display:none; place-items:center; width:46px; height:46px; margin-left:auto; border:0; border-radius:50%; background:var(--espresso); color:#fff; cursor:pointer; }
        .mobile-panel { margin-top:10px; padding:12px; border-radius:28px; background:rgba(255,252,248,.97); border:1px solid var(--line); box-shadow:0 14px 30px rgba(84,40,18,.12); animation:drop .3s cubic-bezier(.2,.8,.2,1); }
        .mobile-panel .nav-link { display:flex; }
        .mobile-panel .nav-search { max-width:none; margin-bottom:8px; }
        @keyframes drop { from { opacity:0; transform:translateY(-8px); } }
        @media (max-width:1020px) { .nav > .nav-search, .nav > .nav-links { display:none; } .burger { display:grid; } }

        /* Hero */
        .hero { position:relative; overflow:hidden; display:grid; grid-template-columns:1.1fr 1fr; align-items:center; gap:20px; margin-top:20px; min-height:340px; padding:42px 0 0 56px; border-radius:44px; background:linear-gradient(110deg,#F5E2D1 0%,#FBEBDB 50%,#FFF7EF 100%); border:1px solid var(--line); animation:rise .7s cubic-bezier(.2,.7,.2,1) backwards; }
        @keyframes rise { from { opacity:0; transform:translateY(16px); } }
        .hero-copy { position:relative; z-index:3; padding-bottom:42px; }
        .hero h1 { margin:10px 0 0; font-size:clamp(46px,6.2vw,76px); line-height:1.02; font-weight:800; letter-spacing:-.025em; color:var(--espresso); }
        .hero p.lead { margin:16px 0 0; max-width:42ch; font-size:16px; line-height:1.65; color:#664734; }
        .hero-art { position:relative; align-self:stretch; min-height:300px; }
        .hero-shape { position:absolute; right:-10%; top:-18%; width:96%; height:130%; background:linear-gradient(140deg,#6b351c,var(--espresso)); border-radius:50% 50% 0 56% / 60% 52% 0 46%; }
        .hero-leaf { position:absolute; right:3%; top:6%; width:36%; color:#7b4a31; opacity:.55; }
        .hero-arch { position:absolute; z-index:2; left:8%; right:22%; bottom:0; top:8%; overflow:hidden; border-radius:999px 999px 0 0; box-shadow:0 26px 60px rgba(56,32,21,.4); background:var(--beige); }
        .hero-arch img { width:100%; height:100%; object-fit:cover; transition:transform 1.2s cubic-bezier(.2,.7,.2,1); }
        .hero:hover .hero-arch img { transform:scale(1.05); }
        .m-bean { position:absolute; z-index:3; width:28px; height:18px; border-radius:50%; background:linear-gradient(135deg,#7a4426,#3a1a0b); animation:bean 6s ease-in-out var(--delay,0s) infinite; box-shadow:0 4px 8px rgba(56,32,21,.3); }
        @keyframes bean { 0%,100% { transform:translateY(0) rotate(var(--r)); } 50% { transform:translateY(-10px) rotate(calc(var(--r) + 14deg)); } }
        @media (max-width:860px) { .hero { grid-template-columns:1fr; padding:34px 26px 0; border-radius:32px; } .hero-art { min-height:260px; margin:0 -26px; } .hero-arch { left:18%; right:18%; } }

        /* Buttons, panels, fields (also used by shared partials) */
        .panel { background:var(--card); border:1px solid var(--line); border-radius:30px; box-shadow:0 1px 2px rgba(84,40,18,.05), 0 8px 26px rgba(84,40,18,.07); }
        .pill-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:999px; padding:13px 24px; font-size:14px; font-weight:700; border:1px solid transparent; cursor:pointer; transition:transform .25s ease, background-color .25s ease, box-shadow .25s ease; }
        .pill-button:hover { transform:translateY(-2px); }
        .pill-button:active { transform:scale(.97); }
        .pill-button .ico { transition:transform .25s ease; }
        .pill-button:hover .ico { transform:translateX(3px); }
        .btn-primary { background:var(--espresso); color:#fff7ef; box-shadow:0 10px 22px rgba(84,40,18,.25); }
        .btn-primary:hover { background:var(--coffee); box-shadow:0 14px 26px rgba(84,40,18,.32); }
        .btn-outline { background:transparent; color:var(--espresso); border-color:rgba(84,40,18,.25); }
        .btn-outline:hover { background:var(--beige); }
        .btn-small { padding:9px 16px; font-size:12px; }
        .label { display:block; margin-bottom:6px; font-size:12px; font-weight:700; color:var(--coffee); }
        .field { width:100%; border-radius:16px; border:1px solid rgba(84,40,18,.16); background:#fff; padding:13px 16px; font:500 14px 'Plus Jakarta Sans',sans-serif; color:var(--ink); outline:none; transition:border-color .2s ease, box-shadow .2s ease; }
        .field:hover { border-color:var(--caramel); }
        .field:focus { border-color:var(--caramel); box-shadow:0 0 0 4px rgba(217,154,105,.25); }
        .field-error { margin-top:4px; font-size:12px; color:#a4473d; }

        /* Profile card */
        .profile { position:relative; overflow:hidden; display:flex; flex-wrap:wrap; align-items:center; gap:22px; margin-top:22px; padding:22px 30px; animation:rise .7s .1s cubic-bezier(.2,.7,.2,1) backwards; }
        .avatar { display:grid; place-items:center; width:84px; height:84px; flex-shrink:0; border-radius:50%; overflow:hidden; background:var(--beige); border:3px solid #fff; box-shadow:0 0 0 2px var(--coffee), 0 8px 20px rgba(84,40,18,.2); font-size:28px; font-weight:700; color:var(--label); transition:transform .35s cubic-bezier(.3,1.6,.5,1); }
        .avatar img { width:100%; height:100%; object-fit:cover; }
        .profile:hover .avatar { transform:scale(1.06) rotate(-3deg); }
        .profile h2 { margin:0; font-size:30px; font-weight:700; color:var(--espresso); }
        .profile .mail { display:flex; align-items:center; gap:10px; margin-top:6px; font-size:15px; color:var(--ink); }
        .profile-leaf { position:absolute; right:-10px; bottom:-30px; width:210px; color:var(--beige); opacity:.9; pointer-events:none; }

        /* Tabs */
        .tabs-wrap { position:sticky; top:96px; z-index:30; margin-top:18px; }
        .tabs { display:inline-flex; max-width:100%; overflow-x:auto; gap:2px; padding:6px; border-radius:99px; background:rgba(245,226,209,.85); -webkit-backdrop-filter:blur(12px); backdrop-filter:blur(12px); border:1px solid var(--line); scrollbar-width:none; box-shadow:0 8px 24px rgba(84,40,18,.08); }
        .tabs::-webkit-scrollbar { display:none; }
        .tab { display:inline-flex; align-items:center; gap:10px; padding:13px 22px; border-radius:99px; font-size:14px; font-weight:600; color:var(--coffee); white-space:nowrap; transition:background-color .3s ease, color .3s ease, transform .25s ease, box-shadow .3s ease; }
        .tab:hover { background:rgba(255,252,248,.8); color:var(--espresso); transform:translateY(-1px); }
        .tab.is-active { background:var(--espresso); color:#fff7ef; box-shadow:0 8px 18px rgba(84,40,18,.28); }
        @media (max-width:1020px) { .tabs-wrap { top:92px; } }
        @media (max-width:640px) { .tab { padding:12px 16px; } }

        .section { scroll-margin-top:170px; margin-top:26px; padding:34px; }
        .section h2.title { margin:6px 0 0; font-size:34px; font-weight:700; letter-spacing:-.4px; color:var(--espresso); }
        .section .sub { margin:8px 0 0; font-size:14px; color:var(--muted); }
        @media (max-width:640px) { .section { padding:22px 18px; } }

        .count-chip { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:99px; background:var(--beige); font-size:13px; font-weight:700; color:var(--coffee); }

        /* Alerts */
        .alert { display:flex; align-items:flex-start; gap:12px; margin-top:20px; padding:14px 20px; border-radius:18px; font-size:14px; animation:drop .45s cubic-bezier(.2,.8,.2,1); }
        .alert-ok { background:#e5f3e3; color:#2f6a45; font-weight:600; }
        .alert-bad { background:#ffe3df; color:#a4473d; }

        /* Orders: compact flex cards */
        .orders-flex { display:flex; flex-wrap:wrap; gap:14px; margin-top:22px; }
        .order { flex:1 1 250px; max-width:calc(33.333% - 10px); display:flex; flex-direction:column; gap:10px; padding:16px 18px; border-radius:22px; background:#fff; border:1px solid var(--line); box-shadow:0 2px 10px rgba(84,40,18,.05); transition:transform .3s ease, box-shadow .3s ease, border-color .3s ease; animation:rise .6s cubic-bezier(.2,.7,.2,1) backwards; animation-delay:calc(var(--i) * 80ms + .25s); }
        .order:hover, .order:focus-within { transform:translateY(-3px); box-shadow:0 14px 30px rgba(84,40,18,.12); border-color:rgba(217,154,105,.5); }
        .order[hidden] { display:none !important; }
        @media (max-width:860px) { .order { max-width:calc(50% - 7px); } }
        @media (max-width:560px) { .order { max-width:100%; flex-basis:100%; } }
        .o-top { display:flex; align-items:center; justify-content:space-between; gap:8px; }
        .o-date { font-size:12px; color:var(--muted); white-space:nowrap; }
        .o-ref { margin:0; font-size:15px; font-weight:800; color:var(--espresso); overflow-wrap:anywhere; }
        .reason { display:flex; align-items:flex-start; gap:8px; margin:0; padding:8px 12px; border-radius:12px; border:1px solid #f4c9c3; background:#ffe9e6; color:#b3261e; font-size:12px; line-height:1.45; }
        .reason .ico { width:15px; height:15px; margin-top:1px; }
        .reason span { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .o-items { display:flex; flex-direction:column; gap:8px; margin:0; padding:0; list-style:none; }
        .item { display:flex; align-items:center; gap:10px; }
        .thumb { display:grid; place-items:center; width:38px; height:38px; flex-shrink:0; overflow:hidden; border-radius:12px; background:var(--beige); color:var(--coffee); font-size:13px; font-weight:800; }
        .thumb img { width:100%; height:100%; object-fit:cover; }
        .item-text { min-width:0; flex:1; }
        .item-name { margin:0; font-size:13px; font-weight:700; color:var(--espresso); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .item-meta { margin:1px 0 0; font-size:12px; color:var(--muted); }
        .o-more { margin:0; font-size:12px; font-weight:700; color:var(--label); }
        .o-foot { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:auto; padding-top:10px; border-top:1px solid var(--line); }
        .o-total { display:flex; flex-direction:column; line-height:1.15; }
        .o-total small { font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--muted); }
        .o-total strong { font-size:19px; font-weight:700; color:var(--espresso); }

        .badge { display:inline-flex; align-items:center; gap:6px; padding:5px 11px; border-radius:99px; font-size:11px; font-weight:700; }
        .badge i { width:6px; height:6px; border-radius:50%; background:currentColor; }
        .badge.pending { background:#fdf0d5; color:#8f5b17; } .badge.processing { background:#e1edfb; color:#2a5c9a; }
        .badge.delivered { background:#e5f3e3; color:#2f6a45; } .badge.cancelled { background:#ffe3df; color:#b3261e; } .badge.other { background:var(--beige); color:var(--coffee); }
        .badge.pending i, .badge.processing i { animation:pulse 1.8s ease-out infinite; }
        @keyframes pulse { 0% { box-shadow:0 0 0 0 currentColor; } 70%,100% { box-shadow:0 0 0 8px transparent; } }
        .cancel-btn { padding:7px 14px; border-radius:99px; border:1px solid #f1c8c2; background:#fff; font:700 12px 'Plus Jakarta Sans',sans-serif; color:#a4473d; cursor:pointer; transition:background-color .2s ease, transform .2s ease; }
        .cancel-btn:hover { background:#fff3f1; transform:translateY(-1px); }
        .sp-dialog { width:min(100% - 2rem, 30rem); padding:0; border:1px solid var(--line); border-radius:28px; background:var(--card); color:var(--ink); box-shadow:0 24px 60px rgba(56,32,21,.3); }
        .sp-dialog::backdrop { background:rgba(56,32,21,.5); }
        .sp-dialog[open] { animation:dialog-in .25s cubic-bezier(.2,.8,.2,1); }
        @keyframes dialog-in { from { opacity:0; transform:translateY(12px) scale(.97); } }

        .empty { margin-top:18px; padding:56px 24px; border-radius:26px; border:2px dashed var(--beige); background:#fff; text-align:center; }
        .empty .tile { display:grid; place-items:center; width:60px; height:60px; margin:0 auto; border-radius:20px; background:var(--beige); color:var(--label); }
        .nomatch { display:none; margin-top:18px; padding:36px; text-align:center; font-size:14px; font-weight:600; color:var(--muted); }
        .nomatch.is-on { display:block; animation:drop .3s ease; }

        /* Addresses, profile photo */
        .addr { margin-top:12px; padding:20px; border-radius:22px; border:1px solid var(--line); background:#fff; transition:transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
        .addr:hover { transform:translateY(-2px); box-shadow:0 12px 28px rgba(84,40,18,.1); }
        .addr.is-default { border-color:rgba(217,154,105,.7); background:#FFF6EC; }
        .pin { display:grid; place-items:center; width:42px; height:42px; flex-shrink:0; border-radius:14px; background:var(--beige); color:var(--label); transition:transform .3s cubic-bezier(.3,1.6,.5,1); }
        .addr:hover .pin { transform:translateY(-3px) scale(1.08); }
        .photo-row { display:flex; flex-wrap:wrap; align-items:center; gap:16px; margin:24px 0 28px; padding:16px 18px; border-radius:22px; background:#FBF1E6; }
        .link-btn { padding:9px 14px; border:0; border-radius:99px; background:transparent; font:700 12px 'Plus Jakarta Sans',sans-serif; cursor:pointer; transition:background-color .2s ease; }
        .link-btn.brown { color:var(--coffee); } .link-btn.brown:hover { background:var(--beige); }
        .link-btn.red { color:#a4473d; } .link-btn.red:hover { background:#ffe3df; }
        details.add > summary { list-style:none; cursor:pointer; }
        details.add > summary::-webkit-details-marker { display:none; }
        details.add[open] > summary .chev { transform:rotate(180deg); }
        .chev { transition:transform .25s ease; }
        details.add[open] > form { animation:drop .3s ease; }

        .foot { position:relative; z-index:1; margin-top:60px; padding:26px 20px; text-align:center; font-size:12px; color:var(--muted); border-top:1px solid var(--beige); }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration:.01ms !important; animation-iteration-count:1 !important; transition-duration:.01ms !important; scroll-behavior:auto !important; } }
    </style>
</head>
<body>
    @php
        $heroLocal = file_exists(public_path('images/hero-account.png')) ? asset('images/hero-account.png') : null;
        $heroPhoto = $heroLocal ?? 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=900&q=80';
        $initials = strtoupper(mb_substr($user->first_name, 0, 1).mb_substr($user->last_name, 0, 1));
        $photoUrl = $user->profile_photo_path ? url('/storage/'.ltrim($user->profile_photo_path, '/')) : null;
        $statusInfo = fn ($s) => match ($s) {
            'pending'                                         => ['pending', 'Pending'],
            'confirmed', 'preparing', 'ready', 'out_for_delivery' => ['processing', $s === 'out_for_delivery' ? 'Out for delivery' : ucfirst($s)],
            'completed'                                       => ['delivered', 'Delivered'],
            'cancelled'                                       => ['cancelled', 'Cancelled'],
            default                                           => ['other', ucfirst(str_replace('_', ' ', (string) $s))],
        };
        $icons = [
            'cup'   => '<path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/>',
            'tea'   => '<path d="M8 2h8l1 6H7L8 2z"/><rect x="5" y="8" width="14" height="12" rx="2"/><path d="M12 11v5"/><path d="M9.5 13.5h5"/>',
            'cookie'=> '<path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/><path d="M11 17v.01"/><path d="M7 14v.01"/>',
            'user'  => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'out'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>',
            'box'   => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
            'pin'   => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
            'lock'  => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            'search'=> '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'mail'  => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        ];
    @endphp

    {{-- Decoration --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <symbol id="leaf" viewBox="0 0 200 240"><path d="M100 10c50 40 80 100 60 170-30 30-70 40-100 30C30 130 50 50 100 10Z" fill="currentColor"/><path d="M100 30c-5 60-5 120 0 200" stroke="rgba(255,255,255,.35)" stroke-width="3" fill="none" stroke-linecap="round"/></symbol>
    </svg>
    <svg class="deco deco-leaves" viewBox="0 0 200 240" style="color:#F0D6BE" aria-hidden="true"><use href="#leaf"/></svg>
    <span class="deco deco-blob" aria-hidden="true"></span>
    <p class="deco deco-script" aria-hidden="true">Good vibes,<br>great coffee</p>
    <span class="deco-bean" style="--r:-30deg;left:50px;top:26%" aria-hidden="true"></span>
    <span class="deco-bean" style="--r:30deg;right:34px;top:48%" aria-hidden="true"></span>
    <span class="deco-bean" style="--r:-12deg;right:60px;top:74%;width:24px;height:15px" aria-hidden="true"></span>

    {{-- ===== NAVBAR ===== --}}
    <div class="sp-wrap nav-wrap">
        <header>
            <div class="nav">
                <a class="brand" href="{{ route('home') }}" aria-label="SipPly home">
                    <img src="{{ asset('images/favicon.png') }}" alt="" width="46" height="46"><span>SipPly</span>
                </a>
                <label class="nav-search">
                    <svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['search'] !!}</svg>
                    <span class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">Search your orders</span>
                    <input type="search" placeholder="Search your orders…" autocomplete="off" data-order-search>
                </label>
                <nav class="nav-links" aria-label="Main navigation">
                    <a class="nav-link" href="{{ route('categories.show', 'coffee') }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['cup'] !!}</svg>Coffee</a>
                    <a class="nav-link" href="{{ route('categories.show', 'milk-tea') }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['tea'] !!}</svg>Milk Tea</a>
                    <a class="nav-link" href="{{ route('categories.show', 'pastries') }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['cookie'] !!}</svg>Pastries</a>
                    <span class="nav-sep" aria-hidden="true"></span>
                    <a class="nav-link is-dark" href="{{ route('account') }}" aria-current="page"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['user'] !!}</svg>My account</a>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0 0 0 6px">
                        @csrf
                        <button class="nav-link is-outline" type="submit"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['out'] !!}</svg>Sign out</button>
                    </form>
                </nav>
                <button type="button" class="burger" id="burger" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-panel">
                    <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
            <div class="mobile-panel" id="mobile-panel" hidden>
                <label class="nav-search">
                    <svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['search'] !!}</svg>
                    <input type="search" placeholder="Search your orders…" aria-label="Search your orders" autocomplete="off" data-order-search>
                </label>
                <a class="nav-link" href="{{ route('categories.show', 'coffee') }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['cup'] !!}</svg>Coffee</a>
                <a class="nav-link" href="{{ route('categories.show', 'milk-tea') }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['tea'] !!}</svg>Milk Tea</a>
                <a class="nav-link" href="{{ route('categories.show', 'pastries') }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['cookie'] !!}</svg>Pastries</a>
                <a class="nav-link is-dark" style="margin-left:0" href="{{ route('account') }}"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['user'] !!}</svg>My account</a>
                <form method="POST" action="{{ route('logout') }}" style="margin:6px 0 0">
                    @csrf
                    <button class="nav-link is-outline" style="width:100%" type="submit"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['out'] !!}</svg>Sign out</button>
                </form>
            </div>
        </header>
    </div>

    <main class="sp-wrap">
        {{-- ===== HERO ===== --}}
        <section class="hero" aria-labelledby="account-title">
            <div class="hero-copy">
                <span class="eyebrow">Your SipPly space</span>
                <h1 class="display" id="account-title">My account</h1>
                <p class="lead">Your details, favorite delivery spots, and orders, all in one place.</p>
                <a class="pill-button btn-primary" style="margin-top:24px" href="{{ route('products.index') }}">
                    Browse the menu
                    <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
            <div class="hero-art" aria-hidden="true">
                <span class="hero-shape"></span>
                <svg class="hero-leaf" viewBox="0 0 200 240"><use href="#leaf"/></svg>
                <div class="hero-arch"><img src="{{ $heroPhoto }}" alt=""></div>
                <span class="m-bean" style="--r:-25deg;left:2%;top:18%"></span>
                <span class="m-bean" style="--r:35deg;--delay:-2s;right:10%;bottom:12%"></span>
                <span class="m-bean" style="--r:10deg;--delay:-4s;right:2%;top:30%;width:22px;height:14px"></span>
                <span class="m-bean" style="--r:-50deg;--delay:-1s;left:12%;bottom:8%;width:22px;height:14px"></span>
            </div>
        </section>

        {{-- Alerts --}}
        @if (session('status'))
            <div class="alert alert-ok" role="status">
                <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif
        @if (session('email_warning'))
            <div class="alert alert-bad" role="alert">
                <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <span>{{ session('email_warning') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-bad" role="alert">
                <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <div>
                    <p style="margin:0;font-weight:700">Please check the highlighted information.</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (! $user->hasVerifiedEmail())
            <div class="alert" role="alert" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;background:#fff8ee;border:1px solid rgba(217,154,105,0.45);color:#6b3c19;padding:14px 20px;border-radius:18px">
                <div style="display:flex;align-items:center;gap:10px">
                    <svg class="ico" viewBox="0 0 24 24" style="width:20px;height:20px;color:#c2622a;flex-shrink:0" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    <span>Your email address is unverified. Please check your inbox or request a fresh verification link.</span>
                </div>
                <form method="POST" action="{{ route('verification.send') }}" style="margin:0">
                    @csrf
                    <button type="submit" class="pill-button btn-outline btn-small" style="background:#fff;border-color:rgba(217,154,105,0.6);color:#542812;font-size:12px;padding:6px 14px">
                        Resend verification email
                    </button>
                </form>
            </div>
        @endif

        {{-- ===== PROFILE CARD ===== --}}
        <section class="panel profile" aria-label="Your profile">
            <span class="avatar">@if ($photoUrl)<img src="{{ $photoUrl }}" alt="Profile photo">@else{{ $initials }}@endif</span>
            <div style="min-width:0">
                <h2 class="display">{{ $user->first_name }} {{ $user->last_name }}</h2>
                <p class="mail">
                    <svg class="ico" viewBox="0 0 24 24" style="width:19px;height:19px;color:var(--coffee)" aria-hidden="true">{!! $icons['mail'] !!}</svg>
                    <span style="overflow-wrap:anywhere">{{ $user->email }}</span>
                    @if ($user->hasVerifiedEmail())
                        <span class="badge delivered" style="margin-left:8px;font-size:11px">
                            <i></i> Verified
                        </span>
                    @else
                        <span class="badge pending" style="margin-left:8px;font-size:11px">
                            <i></i> Unverified
                        </span>
                    @endif
                </p>
            </div>
            <svg class="profile-leaf" viewBox="0 0 200 240" aria-hidden="true"><use href="#leaf"/></svg>
        </section>

        {{-- ===== TABS (order: profile, addresses, security, orders) ===== --}}
        <div class="tabs-wrap">
            <nav class="tabs" aria-label="Account sections">
                <a class="tab is-active" href="#profile" data-spy="profile"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['user'] !!}</svg>Profile details</a>
                <a class="tab" href="#addresses" data-spy="addresses"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['pin'] !!}</svg>Delivery addresses</a>
                <a class="tab" href="#security" data-spy="security"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['lock'] !!}</svg>Password &amp; security</a>
                <a class="tab" href="#orders" data-spy="orders"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['box'] !!}</svg>My orders</a>
            </nav>
        </div>

        {{-- ===== PROFILE ===== --}}
        <section id="profile" class="panel section">
            <span class="eyebrow">Your personal information</span>
            <h2 class="title display">Profile details</h2>
            <p class="sub">Keep your contact details and profile photo up to date.</p>

            <div class="photo-row">
                <span class="avatar" style="width:68px;height:68px;font-size:22px">@if ($photoUrl)<img src="{{ $photoUrl }}" alt="Current profile photo">@else{{ $initials }}@endif</span>
                <div style="min-width:0;flex:1">
                    <p style="margin:0;font-size:14px;font-weight:700;color:var(--espresso)">Profile photo</p>
                    <p style="margin:4px 0 0;font-size:12px;color:var(--muted)">JPG, PNG, or WebP · up to 2 MB</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <form method="POST" action="{{ route('account.photo.store') }}" enctype="multipart/form-data">
                        @csrf
                        <label class="pill-button btn-outline btn-small" style="cursor:pointer;background:#fff">
                            <svg class="ico" viewBox="0 0 24 24" style="width:16px;height:16px" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                            Choose photo
                            <input class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required onchange="this.form.submit()">
                        </label>
                    </form>
                    @if ($user->profile_photo_path)
                        <form method="POST" action="{{ route('account.photo.destroy') }}">
                            @csrf
                            @method('DELETE')
                            <button class="link-btn red" type="submit">Remove</button>
                        </form>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('account.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="first_name">First name</label>
                        <input class="field" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" autocomplete="given-name" required>
                        @error('first_name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="last_name">Last name</label>
                        <input class="field" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" autocomplete="family-name" required>
                        @error('last_name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="email">Email address</label>
                        <input class="field" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                        @error('email') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="phone_number">Phone number</label>
                        <input class="field" id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number', $user->phone_number) }}" autocomplete="tel">
                        @error('phone_number') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label" for="profile_current_password">Current password <span style="font-weight:500;color:var(--muted)">· required to save profile changes</span></label>
                        <input class="field sm:max-w-md" id="profile_current_password" name="current_password" type="password" autocomplete="current-password" required>
                        @error('current_password') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <button class="pill-button btn-primary" style="margin-top:24px" type="submit">Save profile</button>
            </form>
        </section>

        {{-- ===== ADDRESSES ===== --}}
        <section id="addresses" class="panel section">
            <span class="eyebrow">For a smooth delivery</span>
            <h2 class="title display">Delivery addresses</h2>
            <p class="sub">Manage where your orders should arrive.</p>

            <div style="margin-top:22px">
                @forelse ($addresses as $address)
                    <article class="addr {{ $address->is_default ? 'is-default' : '' }}">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex min-w-0 gap-3">
                                <span class="pin"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true">{!! $icons['pin'] !!}</svg></span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--espresso)">{{ $address->label ?: 'Delivery address' }}</h3>
                                        @if ($address->is_default)<span style="padding:4px 11px;border-radius:99px;background:var(--espresso);color:#fff;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase">Default</span>@endif
                                    </div>
                                    <p style="margin:6px 0 0;font-size:14px;font-weight:600">{{ $address->recipient_name }} · {{ $address->phone_number }}</p>
                                    <p style="margin:4px 0 0;font-size:14px;line-height:1.6;color:var(--muted)">{{ $address->address_line }}, {{ $address->barangay }}, {{ $address->city }}, {{ $address->province }}@if ($address->postal_code), {{ $address->postal_code }}@endif</p>
                                </div>
                            </div>
                            <div class="flex shrink-0 flex-wrap items-center gap-1">
                                <button class="pill-button btn-outline btn-small" style="background:#fff" type="button" data-edit-toggle aria-expanded="false" aria-controls="address-edit-{{ $address->address_id }}">Edit</button>
                                @unless ($address->is_default)
                                    <form method="POST" action="{{ route('account.addresses.default', $address->address_id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="link-btn brown" type="submit">Make default</button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('account.addresses.destroy', $address->address_id) }}" onsubmit="return confirm('Remove this saved address?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="link-btn red" type="submit">Remove</button>
                                </form>
                            </div>
                        </div>

                        <div id="address-edit-{{ $address->address_id }}" style="margin-top:20px;padding-top:20px;border-top:1px solid var(--line)" hidden>
                            <form method="POST" action="{{ route('account.addresses.update', $address->address_id) }}">
                                @csrf
                                @method('PUT')
                                @include('auth.partials.address-fields', ['address' => $address, 'formKey' => 'edit-'.$address->address_id])
                                <button class="pill-button btn-primary btn-small" style="margin-top:20px" type="submit">Save address</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="empty" style="margin-top:0;padding:36px 20px">
                        <p style="margin:0;font-size:15px;font-weight:700;color:var(--espresso)">No saved addresses yet</p>
                        <p style="margin:6px 0 0;font-size:13px;color:var(--muted)">Add a delivery address to make checkout quicker.</p>
                    </div>
                @endforelse
            </div>

            <details class="add addr" style="margin-top:16px;padding:0">
                <summary class="flex items-center justify-between gap-3" style="padding:18px 22px;font-size:14px;font-weight:700;color:var(--coffee)">
                    <span class="flex items-center gap-3">
                        <span class="pin" style="width:34px;height:34px;border-radius:11px"><svg class="ico" viewBox="0 0 24 24" style="width:16px;height:16px" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg></span>
                        Add a delivery address
                    </span>
                    <svg class="ico chev" viewBox="0 0 24 24" style="width:16px;height:16px" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </summary>
                <form style="padding:20px 22px 24px;border-top:1px solid var(--line)" method="POST" action="{{ route('account.addresses.store') }}">
                    @csrf
                    @include('auth.partials.address-fields', ['address' => null, 'formKey' => 'new'])
                    <button class="pill-button btn-primary" style="margin-top:20px" type="submit">Save address</button>
                </form>
            </details>
        </section>

        {{-- ===== SECURITY ===== --}}
        <section id="security" class="panel section">
            <span class="eyebrow">Keep your account safe</span>
            <h2 class="title display">Password &amp; security</h2>
            <p class="sub">Choose a strong password you don't use anywhere else.</p>

            <form method="POST" action="{{ route('account.password.update') }}" style="margin-top:22px">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="label" for="password_current_password">Current password</label>
                        <input class="field sm:max-w-md" id="password_current_password" name="current_password" type="password" autocomplete="current-password" required>
                        @error('current_password') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="new_password">New password</label>
                        <input class="field" id="new_password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                        @error('password') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="new_password_confirmation">Confirm new password</label>
                        <input class="field" id="new_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                    </div>
                </div>
                <button class="pill-button btn-outline" style="margin-top:24px" type="submit">Change password</button>
            </form>
        </section>

        {{-- ===== ORDERS (last, compact flex cards) ===== --}}
        <section id="orders" class="panel section">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <span class="eyebrow">Your SipPly history</span>
                    <h2 class="title display">Your orders</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="count-chip" id="order-count"><svg class="ico" viewBox="0 0 24 24" style="width:17px;height:17px" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>{{ $orders->total() }} {{ \Illuminate\Support\Str::plural('order', $orders->total()) }}</span>
                    @if ($orders->total() > 0)
                        <form method="POST" action="{{ route('account.orders.clear') }}" onsubmit="return confirm('Clear your order history? Any order currently out for delivery will be kept.')">
                            @csrf
                            @method('DELETE')
                            <button class="cancel-btn" type="submit">Clear history</button>
                        </form>
                    @endif
                </div>
            </div>

            @if ($orders->count())
                <div class="orders-flex">
                    @foreach ($orders as $order)
                        @php
                            [$statusClass, $statusLabel] = $statusInfo($order->order_status);
                            $lineItems = $orderItems->get($order->order_id, collect());
                            $haystack = mb_strtolower($order->order_number.' '.$lineItems->pluck('product_name')->join(' '));
                            $shownItems = $lineItems->take(2);
                            $moreItems = $lineItems->count() - $shownItems->count();
                        @endphp
                        <article class="order" style="--i:{{ min($loop->index, 6) }}" data-order data-haystack="{{ $haystack }}">
                            <div class="o-top">
                                <span class="badge {{ $statusClass }}"><i></i>{{ $statusLabel }}</span>
                                <span class="o-date">{{ \Illuminate\Support\Carbon::parse($order->placed_at)->format('M j, Y') }}</span>
                            </div>

                            <p class="o-ref">Order #<span data-ref>{{ $order->order_number }}</span></p>

                            @if ($order->order_status === 'cancelled' && $cancellationReasons->has($order->order_id) && filled($cancellationReasons->get($order->order_id)->notes))
                                <p class="reason" role="note">
                                    <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                                    <span><strong>Reason:</strong> {{ $cancellationReasons->get($order->order_id)->notes }}</span>
                                </p>
                            @endif

                            @if ($lineItems->isNotEmpty())
                                <ul class="o-items">
                                    @foreach ($shownItems as $item)
                                        @php $thumb = $item->image_path ?? null; @endphp
                                        <li class="item">
                                            <span class="thumb">@if ($thumb)<img src="{{ asset('storage/'.ltrim($thumb, '/')) }}" alt="" loading="lazy">@else{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item->product_name, 0, 1)) }}@endif</span>
                                            <div class="item-text">
                                                <p class="item-name">{{ $item->product_name }}</p>
                                                <p class="item-meta">{{ $item->quantity }} ×@if ($item->size_name) {{ $item->size_name }}@else item @endif</p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($moreItems > 0)
                                    <p class="o-more">+{{ $moreItems }} more {{ \Illuminate\Support\Str::plural('item', $moreItems) }}</p>
                                @endif
                            @else
                                <p style="margin:0;font-size:12px;color:var(--muted)">Order items are not available.</p>
                            @endif

                            <div class="o-foot">
                                <span class="o-total"><small>Total</small><strong class="display">₱{{ number_format((float) $order->grand_total, 2) }}</strong></span>

                                <div class="flex items-center gap-2">
                                    @if ($order->order_status !== 'out_for_delivery' && in_array($order->order_status, ['pending', 'confirmed'], true))
                                        <form method="POST" action="{{ route('account.orders.cancel', $order->order_id) }}" data-cancellation-form>
                                            @csrf
                                            <button class="cancel-btn" type="submit">Cancel order</button>
                                            <input type="hidden" name="cancellation_reason">
                                            <dialog class="sp-dialog" data-cancellation-dialog aria-labelledby="account-cancel-title-{{ $order->order_id }}">
                                                <div style="padding:28px">
                                                    <h3 class="display" style="margin:0;font-size:26px;color:var(--espresso)" id="account-cancel-title-{{ $order->order_id }}">Cancel order?</h3>
                                                    <p style="margin:8px 0 0;font-size:14px;color:var(--muted)">Please tell us why you are cancelling this order.</p>
                                                    <label class="label" style="margin-top:18px" for="account-cancel-reason-{{ $order->order_id }}">Cancellation reason</label>
                                                    <textarea class="field" id="account-cancel-reason-{{ $order->order_id }}" rows="3" maxlength="500" data-cancellation-reason></textarea>
                                                    <div class="flex justify-end gap-2" style="margin-top:20px">
                                                        <button class="pill-button btn-outline btn-small" type="button" data-dismiss-cancellation>Keep order</button>
                                                        <button class="pill-button btn-small" style="background:#a4473d;color:#fff" type="button" data-confirm-cancellation>Confirm cancellation</button>
                                                    </div>
                                                </div>
                                            </dialog>
                                        </form>
                                    @endif

                                    @if ($order->order_status !== 'out_for_delivery')
                                        <form method="POST" action="{{ route('account.orders.destroy', $order->order_id) }}" onsubmit="return confirm('Remove order #{{ $order->order_number }} from your order history?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="cancel-btn" type="submit" title="Delete order from history">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty">
                    <span class="tile"><svg class="ico" viewBox="0 0 24 24" style="width:28px;height:28px" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></span>
                    <h3 class="display" style="margin:16px 0 0;font-size:24px;color:var(--espresso)">No orders just yet</h3>
                    <p style="margin:8px 0 0;font-size:14px;color:var(--muted)">Your next favorite drink is waiting to be discovered.</p>
                    <a class="pill-button btn-primary" style="margin-top:22px" href="{{ route('products.index') }}">Explore the menu</a>
                </div>
            @endif
            <p class="panel nomatch" id="order-nomatch">No orders on this page match your search.</p>

            @if ($orders->hasPages())
                <div style="margin-top:22px">{{ $orders->links() }}</div>
            @endif
        </section>
    </main>

    <footer class="foot">SipPly · Something good is brewing.</footer>

    {{-- Additive page scripts. The cancellation dialog is still handled by your existing app.js (data-cancellation-* hooks are unchanged). --}}
    <script>
        (function () {
            // Mobile menu
            var burger = document.getElementById('burger'), panel = document.getElementById('mobile-panel');
            if (burger && panel) {
                burger.addEventListener('click', function () {
                    panel.hidden = !panel.hidden;
                    burger.setAttribute('aria-expanded', String(!panel.hidden));
                });
            }

            // Address edit toggle
            document.querySelectorAll('[data-edit-toggle]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var target = document.getElementById(button.getAttribute('aria-controls'));
                    if (!target) { return; }
                    var open = target.hasAttribute('hidden');
                    if (open) { target.removeAttribute('hidden'); } else { target.setAttribute('hidden', ''); }
                    button.setAttribute('aria-expanded', open ? 'true' : 'false');
                    button.textContent = open ? 'Close' : 'Edit';
                });
            });

            // Tab highlight (scroll-spy + immediate on click)
            var links = document.querySelectorAll('[data-spy]');
            var map = {};
            links.forEach(function (l) {
                map[l.getAttribute('data-spy')] = l;
                l.addEventListener('click', function () { links.forEach(function (x) { x.classList.remove('is-active'); }); l.classList.add('is-active'); });
            });
            function activate(id) {
                links.forEach(function (l) { l.classList.remove('is-active'); });
                if (map[id]) { map[id].classList.add('is-active'); map[id].scrollIntoView({ block: 'nearest', inline: 'center' }); }
            }
            if ('IntersectionObserver' in window) {
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (en) { if (en.isIntersecting) { activate(en.target.id); } });
                }, { rootMargin: '-30% 0px -60% 0px' });
                Object.keys(map).forEach(function (id) { var s = document.getElementById(id); if (s) { io.observe(s); } });
            }
            // Orders is now the last section, so highlight it when the page is scrolled to the bottom
            window.addEventListener('scroll', function () {
                if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) { activate('orders'); }
            }, { passive: true });

            // Search your orders (filters the orders shown on this page; both search boxes stay in sync)
            var inputs = Array.prototype.slice.call(document.querySelectorAll('[data-order-search]'));
            var cards = Array.prototype.slice.call(document.querySelectorAll('[data-order]'));
            var none = document.getElementById('order-nomatch');
            function run(value) {
                var q = value.trim().toLowerCase(), shown = 0;
                cards.forEach(function (c) {
                    var ok = !q || c.getAttribute('data-haystack').indexOf(q) !== -1;
                    c.hidden = !ok; if (ok) { shown++; }
                });
                if (none) { none.classList.toggle('is-on', q !== '' && shown === 0 && cards.length > 0); }
            }
            inputs.forEach(function (input) {
                input.addEventListener('input', function () {
                    inputs.forEach(function (o) { if (o !== input) { o.value = input.value; } });
                    run(input.value);
                    if (input.value.trim() !== '') {
                        var o = document.getElementById('orders');
                        if (o && o.getBoundingClientRect().top > window.innerHeight * .6) { o.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                    }
                });
            });
        })();
    </script>
</body>
</html>