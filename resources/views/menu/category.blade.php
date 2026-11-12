<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FFF7EF">
    <title>{{ $category->category_name }} | SipPly</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --cream:#FFF7EF; --espresso:#542812; --coffee:#75452D; --caramel:#D99A69; --beige:#F5E2D1; --ink:#382015; --muted:#8a6553; --card:#FFFCF8; --line:rgba(84,40,18,.10); }
        @view-transition { navigation: auto; }
        *, *::before, *::after { box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body { margin:0; font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,sans-serif; background:var(--cream); color:var(--ink); -webkit-font-smoothing:antialiased; animation:page-in .5s ease; }
        @keyframes page-in { from { opacity:0; } }
        a { color:inherit; text-decoration:none; }
        [hidden] { display:none !important; }
        .sp-wrap { position:relative; z-index:1; width:min(1240px, calc(100% - 40px)); margin-inline:auto; }
        :focus-visible { outline:2px solid var(--caramel); outline-offset:3px; border-radius:12px; }

        /* Background decoration */
        .sp-deco { position:fixed; z-index:0; pointer-events:none; }
        .sp-deco-leaf { right:-60px; bottom:-40px; width:520px; opacity:.55; }
        .sp-deco-blob { left:-90px; bottom:-90px; width:300px; height:300px; border-radius:50%; background:var(--espresso); opacity:.9; }

        /* Floating navbar */
        .sp-nav-wrap { position:sticky; top:14px; z-index:40; padding-top:14px; }
        .sp-nav { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:10px 14px 10px 24px; border-radius:999px; background:rgba(255,252,248,.88); -webkit-backdrop-filter:blur(16px); backdrop-filter:blur(16px); border:1px solid rgba(217,154,105,.25); box-shadow:0 10px 30px rgba(84,40,18,.09); }
        .sp-brand { display:flex; align-items:center; gap:10px; flex-shrink:0; }
        .sp-brand img { width:46px; height:46px; border-radius:50%; object-fit:cover; }
        .sp-brand span { font-size:24px; font-weight:800; letter-spacing:-.8px; color:var(--espresso); }
        .sp-links { display:flex; align-items:center; gap:4px; }
        .sp-link { display:inline-flex; align-items:center; gap:8px; padding:10px 16px; border-radius:999px; font-size:14px; font-weight:600; color:var(--coffee); white-space:nowrap; transition:background-color .25s ease, color .25s ease, transform .25s ease; }
        .sp-link svg { width:17px; height:17px; fill:none; stroke:currentColor; stroke-width:1.9; stroke-linecap:round; stroke-linejoin:round; transition:transform .25s ease; }
        .sp-link:hover { background:var(--beige); color:var(--espresso); transform:translateY(-1px); }
        .sp-link:hover svg { transform:scale(1.12); }
        .sp-link.is-active { background:var(--caramel); color:var(--espresso); }
        .sp-actions { display:flex; align-items:center; gap:10px; }
        .sp-icon-btn { position:relative; display:grid; place-items:center; width:46px; height:46px; border-radius:50%; border:0; cursor:pointer; background:#fff; color:var(--espresso); box-shadow:0 2px 8px rgba(84,40,18,.1); transition:transform .25s ease, box-shadow .25s ease, background-color .25s ease; }
        .sp-icon-btn svg { width:19px; height:19px; fill:none; stroke:currentColor; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .sp-icon-btn:hover { transform:translateY(-2px); box-shadow:0 8px 18px rgba(84,40,18,.18); }
        .sp-icon-btn.dark { background:var(--espresso); color:#fff; }
        .sp-icon-btn.dark:hover { background:var(--coffee); }
        .sp-count { position:absolute; top:-4px; right:-4px; display:grid; place-items:center; min-width:20px; height:20px; padding:0 5px; border-radius:99px; background:var(--caramel); color:var(--espresso); font-size:11px; font-weight:800; border:2px solid var(--cream); }
        .sp-burger { display:none; }
        .sp-mobile { margin-top:10px; padding:10px; border-radius:28px; background:rgba(255,252,248,.96); border:1px solid var(--line); box-shadow:0 14px 30px rgba(84,40,18,.12); }
        .sp-mobile .sp-link { display:flex; }
        .sp-search { margin-top:10px; display:flex; align-items:center; gap:12px; padding:0 22px; height:54px; border-radius:999px; background:rgba(255,252,248,.96); border:1px solid rgba(217,154,105,.4); box-shadow:0 10px 30px rgba(84,40,18,.09); animation:drop .3s cubic-bezier(.2,.8,.2,1); }
        .sp-search svg { width:19px; height:19px; flex-shrink:0; fill:none; stroke:var(--coffee); stroke-width:2; stroke-linecap:round; }
        .sp-search input { flex:1; min-width:0; border:0; outline:0; background:transparent; font:500 15px 'Plus Jakarta Sans',sans-serif; color:var(--ink); }
        @keyframes drop { from { opacity:0; transform:translateY(-8px); } }
        @media (max-width:980px) { .sp-links { display:none; } .sp-burger { display:grid; } }

        /* Hero */
        .sp-hero { position:relative; overflow:hidden; display:grid; grid-template-columns:1.05fr 1fr; align-items:center; gap:24px; margin-top:22px; min-height:430px; padding:44px 24px 44px 52px; border-radius:44px; background:linear-gradient(115deg,#F5E2D1 0%,#FBEBDB 45%,#FFF7EF 100%); border:1px solid var(--line); }
        .sp-hero-copy { position:relative; z-index:3; }
        .sp-back { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:99px; background:rgba(255,252,248,.85); border:1px solid var(--line); font-size:13px; font-weight:700; color:var(--coffee); transition:transform .25s ease, background-color .25s ease; }
        .sp-back svg { width:15px; height:15px; fill:none; stroke:currentColor; stroke-width:2.5; stroke-linecap:round; stroke-linejoin:round; transition:transform .25s ease; }
        .sp-back:hover { background:#fff; transform:translateX(-3px); }
        .sp-back:hover svg { transform:translateX(-3px); }
        .sp-label { display:block; margin:34px 0 0 6px; font-size:12px; font-weight:700; letter-spacing:.34em; text-transform:uppercase; color:var(--coffee); }
        .sp-title { margin:6px 0 0; font-size:clamp(60px,9.5vw,118px); line-height:1; font-weight:800; letter-spacing:-.045em; color:var(--espresso); }
        .sp-brush { display:block; width:min(280px,60%); margin:-4px 0 0 8px; overflow:visible; }
        .sp-brush path { fill:none; stroke:url(#brushGrad); stroke-width:6; stroke-linecap:round; stroke-dasharray:420; stroke-dashoffset:420; animation:draw 1.1s .35s cubic-bezier(.6,.1,.2,1) forwards; }
        @keyframes draw { to { stroke-dashoffset:0; } }
        .sp-desc { margin:20px 0 0 6px; max-width:36ch; font-size:19px; line-height:1.55; color:var(--coffee); }
        .sp-countpill { display:inline-flex; align-items:center; gap:10px; margin:22px 0 0 6px; padding:11px 20px; border-radius:99px; background:rgba(255,252,248,.92); box-shadow:0 4px 14px rgba(84,40,18,.08); font-size:14px; font-weight:700; color:var(--espresso); }
        .sp-countpill svg { width:18px; height:18px; fill:var(--coffee); }

        .sp-art { position:relative; align-self:stretch; min-height:340px; }
        .sp-shape { position:absolute; background:linear-gradient(140deg,#6b351c,var(--espresso)); }
        .sp-shape.a { right:-12%; top:-20%; width:92%; height:128%; border-radius:46% 54% 0 60% / 58% 46% 0 54%; }
        .sp-shape.b { left:2%; bottom:-14%; width:46%; height:62%; border-radius:62% 38% 55% 45% / 50% 55% 45% 50%; background:var(--beige); opacity:.9; }
        .sp-photo { position:absolute; inset:8% 8% 8% 12%; z-index:2; overflow:hidden; border-radius:220px 52px 220px 52px; box-shadow:0 26px 60px rgba(56,32,21,.35); }
        .sp-photo img { width:100%; height:100%; object-fit:cover; transition:transform 1.2s cubic-bezier(.2,.7,.2,1); }
        .sp-hero:hover .sp-photo img { transform:scale(1.04); }
        .sp-bean { position:absolute; z-index:3; width:30px; height:19px; border-radius:50%; background:linear-gradient(135deg,#7a4426,#3a1a0b); animation:bean 6s ease-in-out var(--delay,0s) infinite; box-shadow:0 4px 8px rgba(56,32,21,.25); }
        .sp-bean::after { content:""; position:absolute; inset:8px 4px auto; height:2px; border-radius:2px; background:#1d0c04; }
        @keyframes bean { 0%,100% { transform:translateY(0) rotate(var(--r)); } 50% { transform:translateY(-10px) rotate(calc(var(--r) + 14deg)); } }
        .sp-bean.hero-left { left:-14px; top:62%; }
        @media (max-width:860px) {
            .sp-hero { grid-template-columns:1fr; padding:32px 24px 0; border-radius:32px; }
            .sp-art { min-height:300px; margin:0 -24px; }
            .sp-photo { inset:6% 10% 0 10%; border-radius:160px 40px 0 0; }
        }

        /* Product grid */
        .sp-section { margin-top:48px; padding-bottom:90px; }
        .sp-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 390px)); gap:28px; justify-content:start; }
        @media (max-width:640px) { .sp-grid { grid-template-columns:1fr; } }

        .sp-card { position:relative; display:flex; flex-direction:column; overflow:hidden; border-radius:30px; background:var(--card); border:1px solid var(--line); box-shadow:0 4px 18px rgba(84,40,18,.07); transition:transform .3s cubic-bezier(.22,.61,.36,1), box-shadow .3s ease; animation:card-in .6s cubic-bezier(.2,.7,.2,1) backwards; animation-delay:calc(var(--i) * 80ms + .3s); }
        @keyframes card-in { from { opacity:0; transform:translateY(18px); } }
        .sp-card:hover, .sp-card:focus-within { transform:translateY(-6px); box-shadow:0 22px 48px rgba(84,40,18,.16); }
        .sp-card[hidden] { display:none !important; }
        .sp-img { position:relative; aspect-ratio:4/3; overflow:hidden; background:var(--beige); }
        .sp-img img { width:100%; height:100%; object-fit:cover; transition:transform .6s cubic-bezier(.22,.61,.36,1); }
        .sp-card:hover .sp-img img { transform:scale(1.07); }
        .sp-ph { display:grid; place-items:center; width:100%; height:100%; font-size:64px; font-weight:800; color:var(--caramel); }
        .sp-heart { position:absolute; top:14px; right:14px; z-index:2; display:grid; place-items:center; width:42px; height:42px; border:0; border-radius:50%; cursor:pointer; background:rgba(255,255,255,.92); box-shadow:0 4px 12px rgba(0,0,0,.14); transition:transform .25s cubic-bezier(.3,1.6,.5,1); }
        .sp-heart svg { width:19px; height:19px; fill:transparent; stroke:#d14a4a; stroke-width:2.2; transition:fill .2s ease; }
        .sp-heart:hover { transform:scale(1.12); }
        .sp-heart.liked svg { fill:#d14a4a; }
        .sp-heart.pop { animation:pop .4s ease; }
        @keyframes pop { 40% { transform:scale(1.35); } }
        .sp-body { display:flex; flex:1; flex-direction:column; padding:22px 22px 24px; }
        .sp-top { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; }
        .sp-name { margin:0; font-size:21px; font-weight:800; letter-spacing:-.4px; line-height:1.2; color:var(--espresso); }
        .sp-name a:hover { color:var(--coffee); }
        .sp-price { flex-shrink:0; padding:7px 14px; border-radius:99px; background:var(--beige); color:var(--coffee); font-size:13px; font-weight:800; white-space:nowrap; }
        .sp-text { margin:10px 0 0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; font-size:14px; line-height:1.6; color:var(--muted); }
        .sp-stock { display:flex; align-items:center; gap:8px; margin-top:14px; font-size:13px; font-weight:700; }
        .sp-stock i { width:9px; height:9px; border-radius:50%; background:currentColor; }
        .sp-stock.ok { color:#2f7a45; } .sp-stock.low { color:#b0701a; } .sp-stock.out { color:#b3402f; }
        .sp-stock.ok i { animation:pulse 2s ease-out infinite; }
        @keyframes pulse { 0% { box-shadow:0 0 0 0 rgba(47,122,69,.5); } 70%,100% { box-shadow:0 0 0 8px rgba(47,122,69,0); } }

        .sp-form { display:flex; flex-direction:column; gap:12px; margin-top:18px; }
        .sp-select { width:100%; padding:12px 40px 12px 16px; border-radius:16px; border:1.5px solid var(--beige); background:var(--cream) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2375452D' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 16px center; appearance:none; -webkit-appearance:none; font:600 14px 'Plus Jakarta Sans',sans-serif; color:var(--ink); cursor:pointer; transition:border-color .2s ease, box-shadow .2s ease; }
        .sp-select:hover { border-color:var(--caramel); }
        .sp-select:focus { outline:0; border-color:var(--caramel); box-shadow:0 0 0 4px rgba(217,154,105,.25); }
        .sp-buy { display:flex; align-items:center; gap:12px; }
        .sp-qty { display:flex; align-items:center; flex-shrink:0; height:50px; padding:0 4px; border-radius:99px; border:1.5px solid var(--beige); background:var(--cream); }
        .sp-qty button { display:grid; place-items:center; width:38px; height:38px; border:0; border-radius:50%; background:transparent; font-size:20px; font-weight:700; color:var(--espresso); cursor:pointer; transition:background-color .15s ease, transform .15s ease; }
        .sp-qty button:hover { background:var(--beige); }
        .sp-qty button:active { transform:scale(.88); }
        .sp-qty input { width:34px; border:0; background:transparent; text-align:center; font:700 15px 'Plus Jakarta Sans',sans-serif; color:var(--ink); -moz-appearance:textfield; appearance:textfield; outline:0; }
        .sp-qty input::-webkit-outer-spin-button, .sp-qty input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
        .sp-add { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:10px; height:50px; padding:0 18px; border:0; border-radius:99px; background:var(--espresso); color:#fff; font:700 15px 'Plus Jakarta Sans',sans-serif; cursor:pointer; box-shadow:0 10px 22px rgba(84,40,18,.25); transition:background-color .25s ease, transform .25s ease, box-shadow .25s ease; }
        .sp-add svg { width:18px; height:18px; fill:none; stroke:currentColor; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; transition:transform .3s ease; }
        .sp-add:hover { background:var(--coffee); transform:translateY(-2px); box-shadow:0 14px 26px rgba(84,40,18,.32); }
        .sp-add:hover svg { transform:translateX(3px) rotate(-8deg); }
        .sp-add:active { transform:scale(.97); }
        .sp-add[disabled] { opacity:.75; pointer-events:none; }
        .sp-now { display:inline-flex; align-items:center; justify-content:center; gap:8px; height:46px; border:2px solid var(--caramel); border-radius:99px; background:transparent; font:700 14px 'Plus Jakarta Sans',sans-serif; color:var(--espresso); cursor:pointer; transition:background-color .25s ease, transform .25s ease; }
        .sp-now:hover { background:var(--beige); transform:translateY(-1px); }
        .sp-now:active { transform:scale(.98); }
        .sp-unavail { margin-top:18px; padding:14px; border-radius:18px; border:1.5px dashed var(--caramel); background:var(--cream); text-align:center; font-size:14px; font-weight:700; color:var(--muted); }
        .sp-empty { grid-column:1/-1; padding:64px 24px; border-radius:30px; border:2px dashed var(--beige); background:var(--card); text-align:center; }
        .sp-empty svg { width:52px; height:52px; margin-bottom:14px; fill:none; stroke:var(--caramel); stroke-width:1.5; stroke-linecap:round; stroke-linejoin:round; }
        .sp-nomatch { display:none; grid-column:1/-1; padding:40px; text-align:center; font-size:15px; font-weight:600; color:var(--muted); }
        .sp-nomatch.is-on { display:block; animation:drop .3s ease; }

        /* ===== Best sellers: compact flex row ===== */
        .sp-bestsellers-section { margin-top:40px; padding:26px 24px 28px; border-radius:32px; background:linear-gradient(135deg,rgba(255,252,248,.96) 0%,rgba(245,226,209,.5) 100%); border:1.5px solid rgba(217,154,105,.35); box-shadow:0 12px 36px rgba(84,40,18,.06); position:relative; overflow:hidden; }
        .sp-bestsellers-section::before { content:""; position:absolute; inset:0 auto 0 -40%; width:40%; background:linear-gradient(100deg,transparent,rgba(255,255,255,.55),transparent); transform:skewX(-18deg); animation:sweep 7s ease-in-out 1.5s infinite; pointer-events:none; }
        @keyframes sweep { 0% { left:-45%; } 40%,100% { left:130%; } }
        .sp-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:18px; flex-wrap:wrap; position:relative; }
        .sp-badge-tag { display:inline-flex; align-items:center; gap:7px; padding:5px 12px; border-radius:99px; background:var(--espresso); color:#fff; font-size:11px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; box-shadow:0 4px 12px rgba(84,40,18,.18); }
        .sp-badge-tag svg { width:14px; height:14px; fill:#D99A69; transform-origin:50% 90%; animation:flicker 1.6s ease-in-out infinite; }
        @keyframes flicker { 0%,100% { transform:scale(1) rotate(0); } 25% { transform:scale(1.18,1.1) rotate(-6deg); } 50% { transform:scale(.94,1.05) rotate(4deg); } 75% { transform:scale(1.1) rotate(-3deg); } }
        .sp-section-heading { margin:8px 0 0; font-family:'Playfair Display',Georgia,serif; font-size:clamp(22px,3vw,30px); font-weight:800; letter-spacing:-.5px; color:var(--espresso); }
        .sp-section-sub { margin:4px 0 0; font-size:13.5px; color:var(--coffee); }
        .sp-head-pills { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .sp-curated-pill { display:inline-flex; align-items:center; padding:7px 14px; border-radius:99px; background:var(--card); border:1px solid var(--line); font-size:12px; font-weight:700; color:var(--coffee); }
        .sp-live-pill { display:inline-flex; align-items:center; gap:8px; padding:7px 14px; border-radius:99px; background:#fff; border:1px solid rgba(209,74,74,.25); font-size:11px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; color:#b3402f; }
        .sp-live-pill i { width:8px; height:8px; border-radius:50%; background:#d14a4a; animation:live-dot 1.4s ease-out infinite; }
        @keyframes live-dot { 0% { box-shadow:0 0 0 0 rgba(209,74,74,.55); } 70%,100% { box-shadow:0 0 0 9px rgba(209,74,74,0); } }

        .sp-bs-flex { display:flex; flex-wrap:wrap; gap:16px; position:relative; }
        .sp-card-mini { flex:1 1 250px; max-width:calc(33.333% - 11px); border-radius:24px; border:1.5px solid rgba(217,154,105,.3); box-shadow:0 4px 14px rgba(84,40,18,.07); }
        .sp-card-mini:hover, .sp-card-mini:focus-within { transform:translateY(-5px); box-shadow:0 16px 34px rgba(84,40,18,.15); }
        .sp-card-mini .sp-img { aspect-ratio:16/9; }
        .sp-card-mini .sp-ph { font-size:44px; }
        .sp-card-mini .sp-heart { top:10px; right:10px; width:34px; height:34px; }
        .sp-card-mini .sp-heart svg { width:16px; height:16px; }
        .sp-card-mini .sp-body { padding:14px 16px 16px; }
        .sp-card-mini .sp-top { align-items:center; }
        .sp-card-mini .sp-name { font-size:17px; letter-spacing:-.3px; display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden; }
        .sp-card-mini .sp-price { padding:5px 11px; font-size:12px; }
        .sp-card-mini .sp-form { gap:8px; margin-top:12px; }
        .sp-card-mini .sp-select { padding:9px 36px 9px 14px; border-radius:14px; font-size:13px; background-position:right 14px center; }
        .sp-card-mini .sp-buy { gap:8px; }
        .sp-card-mini .sp-qty { height:40px; }
        .sp-card-mini .sp-qty button { width:30px; height:30px; font-size:17px; }
        .sp-card-mini .sp-qty input { width:26px; font-size:14px; }
        .sp-card-mini .sp-add { height:40px; padding:0 12px; gap:6px; font-size:13px; box-shadow:0 6px 14px rgba(84,40,18,.22); }
        .sp-card-mini .sp-add svg { width:15px; height:15px; }
        .sp-card-mini .sp-now { flex-shrink:0; height:40px; padding:0 12px; border-width:1.5px; font-size:13px; }
        .sp-card-mini .sp-unavail { margin-top:12px; padding:10px; font-size:13px; }

        .sp-rank-badge { position:absolute; top:10px; left:10px; z-index:3; display:inline-flex; align-items:center; gap:5px; overflow:hidden; padding:5px 11px; border-radius:99px; background:rgba(84,40,18,.94); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); color:#FFF7EF; font-size:11px; font-weight:800; letter-spacing:.02em; box-shadow:0 4px 14px rgba(0,0,0,.22); }
        .sp-rank-badge svg { width:12px; height:12px; fill:#FFD166; }
        .sp-rank-badge.rank-1 { background:linear-gradient(135deg,#c75f26,#8a3b10); box-shadow:0 4px 16px rgba(199,95,38,.4); }
        .sp-rank-badge.rank-1::after { content:""; position:absolute; inset:0 auto 0 -60%; width:50%; background:linear-gradient(100deg,transparent,rgba(255,255,255,.55),transparent); transform:skewX(-20deg); animation:badge-shine 3s ease-in-out 1s infinite; }
        @keyframes badge-shine { 0% { left:-60%; } 55%,100% { left:130%; } }
        .sp-rank-badge.rank-1 svg { animation:twinkle 2s ease-in-out infinite; }
        @keyframes twinkle { 0%,100% { transform:scale(1) rotate(0); } 50% { transform:scale(1.3) rotate(18deg); } }

        .sp-meta-row { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:10px; flex-wrap:wrap; }
        .sp-meta-row .sp-stock { font-size:12px; gap:6px; }
        .sp-meta-row .sp-stock i { width:7px; height:7px; }
        .sp-sold-count { display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:800; color:var(--label, #C2622A); font-variant-numeric:tabular-nums; }
        .sp-sold-count svg { width:13px; height:13px; stroke:currentColor; fill:none; stroke-width:2; animation:bag-bounce 2.4s ease-in-out infinite; }
        @keyframes bag-bounce { 0%,70%,100% { transform:translateY(0); } 80% { transform:translateY(-3px); } 90% { transform:translateY(0); } }
        .sp-heat { height:5px; margin-top:9px; overflow:hidden; border-radius:99px; background:var(--beige); }
        .sp-heat i { display:block; width:var(--w,50%); height:100%; border-radius:99px; background:linear-gradient(90deg,#D99A69,#C2622A,#D99A69); background-size:200% 100%; transform-origin:left; animation:heat-fill 1.2s .6s cubic-bezier(.2,.7,.2,1) backwards, heat-flow 2.8s linear infinite; }
        @keyframes heat-fill { from { transform:scaleX(0); } }
        @keyframes heat-flow { to { background-position:-200% 0; } }

        @media (max-width:900px) { .sp-card-mini { max-width:calc(50% - 8px); } }
        @media (max-width:560px) { .sp-card-mini { max-width:100%; flex-basis:100%; } .sp-bestsellers-section { padding:20px 16px; border-radius:24px; } }

        footer { position:relative; z-index:20; }
        .sp-foot { position:relative; z-index:1; padding:24px 20px; text-align:center; font-size:12px; color:var(--muted); border-top:1px solid var(--beige); }

        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration:.01ms !important; animation-iteration-count:1 !important; transition-duration:.01ms !important; scroll-behavior:auto !important; } .sp-brush path { stroke-dashoffset:0; } }
    </style>
</head>
<body>
    @php
        $catSlug = match(strtolower($category->category_name)) {
            'milk tea' => 'milk-tea',
            'pastries' => 'pastries',
            default    => 'coffee',
        };
        // Optional local photo (e.g. a transparent cut-out): public/images/hero-coffee.png, hero-milk-tea.png, hero-pastries.png
        $heroLocal = file_exists(public_path('images/hero-'.$catSlug.'.png')) ? asset('images/hero-'.$catSlug.'.png') : null;
        $heroRemote = match($catSlug) {
            'milk-tea' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1000&q=80',
            'pastries' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=1000&q=80',
            default    => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=1000&q=80',
        };
        $navIcons = [
            'home'   => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>',
            'coffee' => '<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>',
            'tea'    => '<path d="M8 2h8l1 6H7L8 2z"/><rect x="5" y="8" width="14" height="12" rx="2"/><path d="M12 11v5"/><path d="M9.5 13.5h5"/>',
            'cookie' => '<path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/><path d="M11 17v.01"/><path d="M7 14v.01"/>',
        ];
        $links = [
            ['Home',       route('home'),                                         false,                  'home'],
            ['Coffee',     route('categories.show', 'coffee'),                    $catSlug === 'coffee',  'coffee'],
            ['Milk Tea',   route('categories.show', 'milk-tea'),                  $catSlug === 'milk-tea','tea'],
            ['Pastries',   route('categories.show', 'pastries'),                  $catSlug === 'pastries','cookie'],
            ['My Account', auth()->check() ? route('account') : route('login'),   false,                  'user'],
        ];
        // Used to size the animated "heat" bar relative to the top seller
        $maxSold = isset($bestSellers) && $bestSellers->isNotEmpty() ? max(1, (int) $bestSellers->max('total_sold')) : 1;
    @endphp

    {{-- Decorative background --}}
    <svg class="sp-deco sp-deco-leaf" viewBox="0 0 520 520" fill="none" aria-hidden="true">
        <path d="M360 40c70 40 110 120 90 220-70-10-130-70-130-150 0-30 10-55 40-70Z" fill="#F5E2D1"/>
        <path d="M250 200c60 10 110 60 120 130-60 20-120-10-150-60-15-25-5-55 30-70Z" fill="#F5E2D1" opacity=".8"/>
        <path d="M390 120c-20 120-60 240-130 380" stroke="#E8CDB4" stroke-width="3" stroke-linecap="round"/>
    </svg>
    <span class="sp-deco sp-deco-blob" aria-hidden="true"></span>

    {{-- ===== FLOATING NAVBAR ===== --}}
    <div class="sp-wrap sp-nav-wrap">
        <header>
            <div class="sp-nav">
                <a href="{{ route('home') }}" class="sp-brand" aria-label="SipPly home">
                    <img src="{{ asset('images/favicon.png') }}" alt="" width="46" height="46">
                    <span>SipPly</span>
                </a>

                <nav class="sp-links" aria-label="Main navigation">
                    @foreach ($links as [$label, $href, $active, $icon])
                        <a class="sp-link {{ $active ? 'is-active' : '' }}" href="{{ $href }}" @if ($active) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true">{!! $navIcons[$icon] !!}</svg>{{ $label }}
                        </a>
                    @endforeach
                </nav>

                <div class="sp-actions">
                    <button type="button" class="sp-icon-btn" id="search-toggle" aria-label="Search this category" aria-expanded="false" aria-controls="search-panel">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </button>
                    <a href="{{ route('cart.index') }}" class="sp-icon-btn dark" aria-label="Cart{{ $cartCount > 0 ? ', '.$cartCount.' items' : '' }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        @if ($cartCount > 0)<span class="sp-count">{{ $cartCount }}</span>@endif
                    </a>
                    <button type="button" class="sp-icon-btn sp-burger" id="mobile-nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-nav">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                </div>
            </div>

            <div class="sp-mobile" id="mobile-nav" hidden>
                @foreach ($links as [$label, $href, $active, $icon])
                    <a class="sp-link {{ $active ? 'is-active' : '' }}" href="{{ $href }}"><svg viewBox="0 0 24 24" aria-hidden="true">{!! $navIcons[$icon] !!}</svg>{{ $label }}</a>
                @endforeach
            </div>

            <form class="sp-search" id="search-panel" role="search" onsubmit="return false" hidden>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <label class="sr-only" for="category-search" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">Search {{ $category->category_name }}</label>
                <input id="category-search" type="search" placeholder="Search {{ strtolower($category->category_name) }}…" autocomplete="off">
            </form>
        </header>
    </div>

    <main class="sp-wrap">
        {{-- ===== HERO ===== --}}
        <section class="sp-hero" aria-labelledby="category-title">
            <div class="sp-hero-copy">
                <a href="{{ route('products.index') }}" class="sp-back">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>All products
                </a>
                <span class="sp-label">Browse Category</span>
                <h1 class="sp-title" id="category-title">{{ $category->category_name }}</h1>
                <svg class="sp-brush" viewBox="0 0 280 24" aria-hidden="true">
                    <defs><linearGradient id="brushGrad" x1="0" x2="1"><stop offset="0" stop-color="#D99A69"/><stop offset="1" stop-color="#75452D"/></linearGradient></defs>
                    <path d="M4 16c40-8 86-10 130-5 40 4 84 2 142-6"/>
                </svg>
                <p class="sp-desc">
                    @if ($category->description)
                        {{ $category->description }}
                    @else
                        @switch($catSlug)
                            @case('coffee') Freshly prepared coffee favorites. @break
                            @case('milk-tea') Creamy, refreshing milk tea blends. @break
                            @case('pastries') Soft, freshly baked pastry delights. @break
                            @default Carefully crafted for every taste.
                        @endswitch
                    @endif
                </p>
                <span class="sp-countpill">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C7 2 4 6.5 4 11.5S7 22 12 22s8-5.5 8-10.5S17 2 12 2Zm.9 3.4c-1.2 2.3 1.6 3.6.2 6.3-1.2 2.3-.1 4.6.4 6.3-3.2-.6-5.5-3-5.5-6.2 0-3.1 2.1-5.8 4.9-6.4Z"/></svg>
                    <span id="hero-count">{{ $products->count() }} {{ \Illuminate\Support\Str::plural('product', $products->count()) }}</span>
                </span>
            </div>

            <div class="sp-art" aria-hidden="true">
                <span class="sp-shape a"></span>
                <span class="sp-shape b"></span>
                <div class="sp-photo"><img src="{{ $heroLocal ?? $heroRemote }}" alt="" loading="eager"></div>
                <span class="sp-bean" style="--r:-25deg;right:2%;top:14%"></span>
                <span class="sp-bean" style="--r:40deg;--delay:-2s;right:10%;bottom:6%"></span>
                <span class="sp-bean" style="--r:10deg;--delay:-4s;left:6%;top:10%;width:24px;height:15px"></span>
            </div>
        </section>

        {{-- ===== BEST SELLERS (Top 3 by number sold, compact flex row) ===== --}}
        @if (isset($bestSellers) && $bestSellers->isNotEmpty())
            <section class="sp-bestsellers-section" aria-labelledby="bestsellers-heading">
                <div class="sp-section-head">
                    <div>
                        <span class="sp-badge-tag">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2c.5 3-1 5-2.5 6.5C8 10 7 11.5 7 13.5 7 16.5 9.5 19 12 19s5-2.5 5-5.5c0-1.8-.8-3.4-2-4.5.3 1.5-.2 3-1.5 4-.2-2-.5-4.2-1.5-6.5Z"/></svg>
                            Customer Favorites
                        </span>
                        <h2 class="sp-section-heading" id="bestsellers-heading">Best Sellers in {{ $category->category_name }}</h2>
                        <p class="sp-section-sub">Top {{ min($bestSellers->count(), 3) }} based on number of items sold.</p>
                    </div>
                    <div class="sp-head-pills">
                        <span class="sp-live-pill"><i></i>Trending now</span>
                        <span class="sp-curated-pill">Top {{ min($bestSellers->count(), 3) }} {{ \Illuminate\Support\Str::plural('Pick', min($bestSellers->count(), 3)) }}</span>
                    </div>
                </div>

                <div class="sp-bs-flex">
                    @foreach ($bestSellers as $product)
                        @php
                            $sold = (int) ($product->total_sold ?? 0);
                            $heat = max(8, min(100, (int) round($sold / $maxSold * 100)));
                        @endphp
                        <article class="sp-card sp-card-mini" style="--i:{{ $loop->index }}" data-card data-product-id="{{ $product->product_id }}" data-haystack="{{ mb_strtolower($product->product_name.' '.$product->product_description) }}">
                            <div class="sp-img">
                                <span class="sp-rank-badge rank-{{ $loop->iteration }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    #{{ $loop->iteration }} Best Seller
                                </span>

                                @if ($product->image_path)
                                    <img src="{{ asset('storage/'.ltrim($product->image_path, '/')) }}" alt="{{ $product->product_name }}" loading="lazy">
                                @else
                                    <div class="sp-ph">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->product_name, 0, 1)) }}</div>
                                @endif
                                <button type="button" class="sp-heart" aria-label="Favourite {{ $product->product_name }}" aria-pressed="false" data-heart>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                </button>
                            </div>

                            <div class="sp-body">
                                <div class="sp-top">
                                    <h3 class="sp-name"><a href="{{ route('menu.show', $product->product_id) }}">{{ $product->product_name }}</a></h3>
                                    <span class="sp-price">From ₱{{ number_format((float) $product->base_price, 2) }}</span>
                                </div>

                                <div class="sp-meta-row">
                                    @if ($product->stock_quantity > 0)
                                        <p class="sp-stock {{ $product->stock_quantity <= 5 ? 'low' : 'ok' }}" style="margin:0"><i></i>{{ $product->stock_quantity }} in stock</p>
                                    @else
                                        <p class="sp-stock out" style="margin:0"><i></i>Sold out</p>
                                    @endif

                                    @if ($sold > 0)
                                        <span class="sp-sold-count">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                            <span><span data-countup="{{ $sold }}">{{ number_format($sold) }}</span> sold</span>
                                        </span>
                                    @endif
                                </div>

                                @if ($sold > 0)
                                    <div class="sp-heat" aria-hidden="true"><i style="--w:{{ $heat }}%"></i></div>
                                @endif

                                @if ($product->stock_quantity > 0)
                                    <form method="POST" action="{{ route('cart.items.store') }}" class="sp-form" data-cart-form>
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->product_id }}">

                                        @if ($product->sizes->isNotEmpty())
                                            <div>
                                                <label class="sr-only" for="size-bs-{{ $product->product_id }}" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">Size for {{ $product->product_name }}</label>
                                                <select class="sp-select" id="size-bs-{{ $product->product_id }}" name="size_id" required>
                                                    @foreach ($product->sizes as $size)
                                                        <option value="{{ $size->size_id }}">{{ $size->size_name }} · ₱{{ number_format((float) $size->price, 2) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <div class="sp-buy">
                                            <div class="sp-qty">
                                                <button type="button" data-qty-minus aria-label="Decrease quantity">−</button>
                                                <input type="number" name="quantity" value="1" min="1" max="{{ min($product->stock_quantity, 99) }}" required aria-label="Quantity for {{ $product->product_name }}">
                                                <button type="button" data-qty-plus aria-label="Increase quantity">+</button>
                                            </div>
                                            <button type="submit" class="sp-add" data-add>
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                                <span>Add</span>
                                            </button>
                                            <button type="submit" formaction="{{ route('buy-now', $product->product_id) }}" class="sp-now" data-add>Buy now</button>
                                        </div>
                                    </form>
                                @else
                                    <div class="sp-unavail">Currently unavailable</div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ===== ALL PRODUCTS ===== --}}
        <section class="sp-section" aria-label="{{ $category->category_name }} products">
            <div style="margin-bottom:24px">
                <span class="sp-label" style="margin:0 0 4px">Catalog</span>
                <h2 style="margin:0;font-family:'Playfair Display',Georgia,serif;font-size:clamp(22px,3vw,30px);font-weight:700;color:var(--espresso)">
                    All {{ $category->category_name }}
                </h2>
            </div>
            <div class="sp-grid" id="product-grid">
                @forelse ($products as $product)
                    <article class="sp-card" style="--i:{{ min($loop->index, 8) }}" data-card data-product-id="{{ $product->product_id }}" data-haystack="{{ mb_strtolower($product->product_name.' '.$product->product_description) }}">
                        <div class="sp-img">
                            @if ($product->image_path)
                                <img src="{{ asset('storage/'.ltrim($product->image_path, '/')) }}" alt="{{ $product->product_name }}" loading="lazy">
                            @else
                                <div class="sp-ph">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->product_name, 0, 1)) }}</div>
                            @endif
                            <button type="button" class="sp-heart" aria-label="Favourite {{ $product->product_name }}" aria-pressed="false" data-heart>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            </button>
                        </div>

                        <div class="sp-body">
                            <div class="sp-top">
                                <h2 class="sp-name"><a href="{{ route('menu.show', $product->product_id) }}">{{ $product->product_name }}</a></h2>
                                <span class="sp-price">From ₱{{ number_format((float) $product->base_price, 2) }}</span>
                            </div>

                            @if ($product->product_description)
                                <p class="sp-text">{{ $product->product_description }}</p>
                            @endif

                            @if ($product->stock_quantity > 0)
                                <p class="sp-stock {{ $product->stock_quantity <= 5 ? 'low' : 'ok' }}" style="margin-bottom:0"><i></i>{{ $product->stock_quantity }} in stock</p>
                            @else
                                <p class="sp-stock out" style="margin-bottom:0"><i></i>Sold out</p>
                            @endif

                            @if ($product->stock_quantity > 0)
                                <form method="POST" action="{{ route('cart.items.store') }}" class="sp-form" data-cart-form>
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->product_id }}">

                                    @if ($product->sizes->isNotEmpty())
                                        <div>
                                            <label class="sr-only" for="size-{{ $product->product_id }}" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">Size for {{ $product->product_name }}</label>
                                            <select class="sp-select" id="size-{{ $product->product_id }}" name="size_id" required>
                                                @foreach ($product->sizes as $size)
                                                    <option value="{{ $size->size_id }}">{{ $size->size_name }} · ₱{{ number_format((float) $size->price, 2) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="sp-buy">
                                        <div class="sp-qty">
                                            <button type="button" data-qty-minus aria-label="Decrease quantity">−</button>
                                            <input type="number" name="quantity" value="1" min="1" max="{{ min($product->stock_quantity, 99) }}" required aria-label="Quantity for {{ $product->product_name }}">
                                            <button type="button" data-qty-plus aria-label="Increase quantity">+</button>
                                        </div>
                                        <button type="submit" class="sp-add" data-add>
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                            <span>Add to cart</span>
                                        </button>
                                    </div>

                                    <button type="submit" formaction="{{ route('buy-now', $product->product_id) }}" class="sp-now" data-add>Buy now</button>
                                </form>
                            @else
                                <div class="sp-unavail">Currently unavailable</div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="sp-empty">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                        <p style="margin:0;font-size:17px;font-weight:700;color:var(--coffee)">No products available yet.</p>
                        <p style="margin:6px 0 0;font-size:14px;color:var(--muted)">Check back soon for new additions!</p>
                    </div>
                @endforelse
                <p class="sp-nomatch" id="no-match">Nothing here matches your search. Try another word.</p>
            </div>
        </section>
    </main>

    @include('partials.footer')

    <script>
    (function () {
        // Mobile nav
        var toggle = document.getElementById('mobile-nav-toggle');
        var mobileNav = document.getElementById('mobile-nav');
        if (toggle && mobileNav) {
            toggle.addEventListener('click', function () {
                mobileNav.hidden = !mobileNav.hidden;
                toggle.setAttribute('aria-expanded', String(!mobileNav.hidden));
            });
        }

        // Search (filters the products shown on this page)
        var sToggle = document.getElementById('search-toggle');
        var panel = document.getElementById('search-panel');
        var sInput = document.getElementById('category-search');
        var cards = Array.prototype.slice.call(document.querySelectorAll('[data-card]'));
        var none = document.getElementById('no-match');
        var heroCount = document.getElementById('hero-count');
        function filter() {
            var q = sInput.value.trim().toLowerCase(), shown = 0;
            cards.forEach(function (c) {
                var ok = !q || c.getAttribute('data-haystack').indexOf(q) !== -1;
                c.hidden = !ok; if (ok) { shown++; }
            });
            if (none) { none.classList.toggle('is-on', q !== '' && shown === 0 && cards.length > 0); }
        }
        if (sToggle && panel && sInput) {
            sToggle.addEventListener('click', function () {
                panel.hidden = !panel.hidden;
                sToggle.setAttribute('aria-expanded', String(!panel.hidden));
                if (!panel.hidden) { sInput.focus(); } else { sInput.value = ''; filter(); }
            });
            sInput.addEventListener('input', filter);
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !panel.hidden) { panel.hidden = true; sInput.value = ''; filter(); sToggle.setAttribute('aria-expanded', 'false'); sToggle.focus(); }
            });
        }

        // Quantity steppers
        cards.forEach(function (card) {
            var minus = card.querySelector('[data-qty-minus]');
            var plus = card.querySelector('[data-qty-plus]');
            var input = card.querySelector('.sp-qty input');
            if (!minus || !plus || !input) { return; }
            minus.addEventListener('click', function () {
                var v = parseInt(input.value, 10) || 1;
                if (v > parseInt(input.min, 10)) { input.value = v - 1; }
            });
            plus.addEventListener('click', function () {
                var v = parseInt(input.value, 10) || 1;
                if (v < parseInt(input.max, 10)) { input.value = v + 1; }
            });
        });

        // ===== Favorites (heart) – move product to top of the catalog grid =====
        var FAV_KEY = 'sipply-favorites';
        var grid = document.getElementById('product-grid');

        function loadFavorites() {
            try {
                var raw = localStorage.getItem(FAV_KEY);
                var arr = raw ? JSON.parse(raw) : [];
                return Array.isArray(arr) ? arr.map(String) : [];
            } catch (e) { return []; }
        }

        function saveFavorites(ids) {
            try { localStorage.setItem(FAV_KEY, JSON.stringify(ids)); } catch (e) {}
        }

        function getProductId(card) {
            return card ? String(card.getAttribute('data-product-id') || '') : '';
        }

        function setHeartState(btn, on) {
            if (!btn) return;
            btn.classList.toggle('liked', on);
            btn.setAttribute('aria-pressed', String(on));
        }

        function syncHearts(productId, on) {
            document.querySelectorAll('[data-card][data-product-id="' + productId + '"] [data-heart]').forEach(function (btn) {
                setHeartState(btn, on);
            });
        }

        function reorderGrid() {
            if (!grid) return;
            var favs = loadFavorites();
            var all = Array.prototype.slice.call(grid.querySelectorAll('[data-card]'));
            // Favorites first (in the order they were favorited), then the rest in original DOM order
            all.sort(function (a, b) {
                var aId = getProductId(a);
                var bId = getProductId(b);
                var aFav = favs.indexOf(aId);
                var bFav = favs.indexOf(bId);
                if (aFav !== -1 && bFav !== -1) return aFav - bFav; // both fav → keep fav order
                if (aFav !== -1) return -1;
                if (bFav !== -1) return 1;
                return 0; // both non-fav → keep current relative order
            });
            all.forEach(function (card) { grid.appendChild(card); });
        }

        // On load: restore liked state + move favorites to the top
        (function initFavorites() {
            var favs = loadFavorites();
            document.querySelectorAll('[data-card][data-product-id]').forEach(function (card) {
                var id = getProductId(card);
                if (id && favs.indexOf(id) !== -1) {
                    var btn = card.querySelector('[data-heart]');
                    setHeartState(btn, true);
                }
            });
            reorderGrid();
        })();

        // Click handler
        document.querySelectorAll('[data-heart]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var card = btn.closest('[data-card]');
                var id = getProductId(card);
                if (!id) return;

                var favs = loadFavorites();
                var idx = favs.indexOf(id);
                var nowLiked = idx === -1;

                if (nowLiked) {
                    favs.unshift(id); // newest favorite goes to the very top
                } else {
                    favs.splice(idx, 1);
                }
                saveFavorites(favs);

                // Update every heart for this product (main grid + best-sellers)
                syncHearts(id, nowLiked);

                // Pop animation on the clicked button
                btn.classList.remove('pop');
                void btn.offsetWidth;
                btn.classList.add('pop');

                // Move the product to the top of the catalog (or restore order)
                reorderGrid();

                // Smooth scroll so the user sees the card at the top
                if (nowLiked && grid) {
                    var target = grid.querySelector('[data-card][data-product-id="' + id + '"]');
                    if (target) {
                        setTimeout(function () {
                            target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }, 50);
                    }
                }
            });
        });

        // Button feedback once a cart form is submitted (the form still posts normally)
        document.querySelectorAll('[data-cart-form]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var btn = e.submitter;
                if (!btn) { return; }
                var label = btn.querySelector('span');
                if (label) { label.textContent = 'Adding…'; } else { btn.textContent = 'One moment…'; }
                setTimeout(function () { btn.disabled = true; }, 0);
            });
        });

        // Best sellers: count the "sold" numbers up when they scroll into view
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!reduce && 'IntersectionObserver' in window) {
            document.querySelectorAll('[data-countup]').forEach(function (el) {
                var end = parseInt(el.getAttribute('data-countup'), 10) || 0;
                el.textContent = '0';
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (en) {
                        if (!en.isIntersecting) { return; }
                        io.disconnect();
                        var start = null, dur = 1400;
                        function step(t) {
                            if (start === null) { start = t; }
                            var p = Math.min((t - start) / dur, 1);
                            el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))).toLocaleString();
                            if (p < 1) { requestAnimationFrame(step); }
                        }
                        requestAnimationFrame(step);
                    });
                }, { threshold: .4 });
                io.observe(el);
            });
        }
    }());
    </script>
</body>
</html>