<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#542812">
    <title>@yield('title', 'Welcome') | SipPly</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --cream:#FFF7EF; --espresso:#542812; --coffee:#75452D; --caramel:#D99A69; --beige:#F5E2D1; --card:#FFFCF8; --ink:#382015; --muted:#8C7769; --line:rgba(84,40,18,.12); --danger:#b3402f; --ok:#2f6a45; }
        @view-transition { navigation: auto; }
        *, *::before, *::after { box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body { margin:0; min-height:100vh; font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,sans-serif; background:var(--cream); color:var(--ink); -webkit-font-smoothing:antialiased; animation:fade .5s ease; }
        @keyframes fade { from { opacity:0; } }
        a { color:inherit; }
        [hidden] { display:none !important; }
        :focus-visible { outline:2px solid var(--caramel); outline-offset:3px; border-radius:10px; }

        .auth-shell { display:grid; grid-template-columns:minmax(0,46fr) minmax(0,54fr); min-height:100vh; padding:14px; gap:14px; }

        /* ===== Story panel ===== */
        .story { position:relative; overflow:hidden; display:flex; flex-direction:column; justify-content:space-between; padding:34px 40px 30px; border-radius:40px; color:#fff7ef; background:radial-gradient(120% 90% at 20% 0%, #7a4426 0%, #542812 55%, #3f1d0d 100%); isolation:isolate; }
        .story::before, .story::after { content:""; position:absolute; z-index:-1; border-radius:50%; }
        .story::before { width:420px; height:420px; right:-170px; top:-150px; background:rgba(217,154,105,.16); animation:drift 14s ease-in-out infinite; }
        .story::after { width:360px; height:360px; left:-150px; bottom:-170px; background:rgba(245,226,209,.09); animation:drift 18s ease-in-out -6s infinite reverse; }
        @keyframes drift { 0%,100% { transform:translate(0,0) scale(1); } 50% { transform:translate(-26px,22px) scale(1.07); } }
        .brand { display:inline-flex; align-items:center; gap:12px; width:fit-content; text-decoration:none; }
        .brand img { width:48px; height:48px; border-radius:50%; object-fit:cover; background:#fff; }
        .brand span { font-family:'Playfair Display',serif; font-size:28px; font-weight:700; letter-spacing:-.5px; }
        .story-body { position:relative; display:flex; flex-direction:column; align-items:center; text-align:center; padding:18px 0; }
        .story-title { margin:28px 0 0; max-width:15ch; font-family:'Playfair Display',serif; font-size:clamp(32px,3.6vw,50px); line-height:1.08; font-weight:800; letter-spacing:-.02em; }
        .story-copy { margin:16px 0 0; max-width:36ch; font-size:16px; line-height:1.65; color:rgba(255,247,239,.78); }
        .story-foot { font-size:13px; color:rgba(255,247,239,.6); display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .back-link { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:99px; background:rgba(255,247,239,.12); color:#fff7ef; font-size:13px; font-weight:700; text-decoration:none; transition:background-color .25s ease, transform .25s ease; }
        .back-link svg { width:15px; height:15px; fill:none; stroke:currentColor; stroke-width:2.4; stroke-linecap:round; stroke-linejoin:round; transition:transform .25s ease; }
        .back-link:hover { background:rgba(255,247,239,.22); transform:translateX(-2px); }
        .back-link:hover svg { transform:translateX(-3px); }

        /* Floating beans (outer = mouse parallax, inner = float) */
        .pb { position:absolute; z-index:0; pointer-events:none; transform:translate(calc(var(--mx,0) * var(--depth,10) * 1px), calc(var(--my,0) * var(--depth,10) * 1px)); transition:transform .25s ease-out; }
        .pb i { display:block; position:relative; width:var(--w,30px); height:calc(var(--w,30px) * .62); border-radius:50%; background:linear-gradient(135deg,#8a5030,#3a1a0b); box-shadow:0 6px 12px rgba(0,0,0,.28); animation:bean var(--dur,6s) ease-in-out var(--delay,0s) infinite; }
        .pb i::after { content:""; position:absolute; left:14%; right:14%; top:46%; height:2px; border-radius:2px; background:#1d0c04; }
        @keyframes bean { 0%,100% { transform:translateY(0) rotate(var(--r)); } 50% { transform:translateY(-14px) rotate(calc(var(--r) + 18deg)); } }

        /* Mascot */
        .mascot { position:relative; width:min(250px,56%); animation:bob 4.2s ease-in-out infinite; }
        .mascot svg { display:block; width:100%; height:auto; overflow:visible; filter:drop-shadow(0 24px 30px rgba(0,0,0,.35)); }
        @keyframes bob { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-9px); } }
        .steam path { fill:none; stroke:#F5E2D1; stroke-width:5; stroke-linecap:round; opacity:0; animation:steam 3.2s ease-in-out infinite; }
        .steam path:nth-child(2) { animation-delay:.8s; } .steam path:nth-child(3) { animation-delay:1.6s; }
        @keyframes steam { 0% { opacity:0; transform:translateY(10px); } 40% { opacity:.85; } 100% { opacity:0; transform:translateY(-16px); } }
        .eyes { animation:blink 5s infinite; transform-origin:106px 130px; transition:opacity .2s ease; }
        @keyframes blink { 0%,94%,100% { transform:scaleY(1); } 97% { transform:scaleY(.1); } }
        .pupils { transition:transform .12s ease-out; }
        .closed { opacity:0; transition:opacity .2s ease; }
        .hands { transform:translateY(70px); transition:transform .45s cubic-bezier(.3,1.5,.5,1); }
        .mascot.is-shy .hands { transform:translateY(0); }
        .mascot.is-shy .eyes { opacity:0; }
        .mascot.is-shy .closed { opacity:1; }
        .mascot.is-happy { animation:hop .6s ease; }
        @keyframes hop { 40% { transform:translateY(-22px) scale(1.04); } }

        /* ===== Form side ===== */
        .panel { position:relative; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:36px 20px; }
        .auth-card { width:min(100%,500px); }
        .auth-card > * { animation:rise .6s cubic-bezier(.2,.7,.2,1) backwards; }
        .auth-card > :nth-child(1) { animation-delay:.05s; } .auth-card > :nth-child(2) { animation-delay:.12s; } .auth-card > :nth-child(3) { animation-delay:.19s; }
        .auth-card > :nth-child(4) { animation-delay:.26s; } .auth-card > :nth-child(5) { animation-delay:.33s; } .auth-card > :nth-child(6) { animation-delay:.4s; } .auth-card > :nth-child(n+7) { animation-delay:.47s; }
        @keyframes rise { from { opacity:0; transform:translateY(18px); } }

        .eyebrow { margin:0; font-size:12px; font-weight:800; letter-spacing:.2em; text-transform:uppercase; color:#C2622A; }
        .auth-card h2 { margin:8px 0 0; font-family:'Playfair Display',serif; font-size:clamp(34px,4vw,46px); line-height:1.05; font-weight:800; letter-spacing:-.025em; color:var(--espresso); }
        .form-intro { margin:12px 0 0; font-size:15px; line-height:1.6; color:var(--muted); }
        .auth-card form { margin-top:28px; }
        .form-message { display:flex; align-items:center; gap:10px; margin:20px 0 0; padding:13px 18px; border-radius:16px; font-size:14px; font-weight:600; }
        .form-message.success { background:#e5f3e3; color:var(--ok); animation:pop .5s cubic-bezier(.3,1.5,.5,1); }
        .form-message.error { background:#ffe3df; color:#a4473d; }
        @keyframes pop { from { opacity:0; transform:scale(.94) translateY(-6px); } }

        .form-grid { display:grid; gap:0 16px; grid-template-columns:1fr 1fr; }
        @media (max-width:560px) { .form-grid { grid-template-columns:1fr; } }
        .field { margin-bottom:18px; }
        .field label { display:block; margin-bottom:7px; font-size:13px; font-weight:700; color:var(--coffee); transition:color .2s ease; }
        .field:focus-within label { color:var(--espresso); }
        .field input { width:100%; height:54px; padding:0 18px; border-radius:18px; border:1.5px solid var(--line); background:#fff; font:500 15px 'Plus Jakarta Sans',sans-serif; color:var(--ink); transition:border-color .2s ease, box-shadow .25s ease, transform .2s ease; }
        .field input:hover { border-color:var(--caramel); }
        .field input:focus { outline:0; border-color:var(--caramel); box-shadow:0 0 0 5px rgba(217,154,105,.25); transform:translateY(-1px); }
        .field input[aria-invalid="true"] { border-color:var(--danger); background:#fff8f6; animation:shake .4s ease; }
        .field input[aria-invalid="true"]:focus { box-shadow:0 0 0 5px rgba(179,64,47,.15); }
        @keyframes shake { 20%,60% { transform:translateX(-5px); } 40%,80% { transform:translateX(5px); } }
        .field-error { margin:7px 0 0; font-size:13px; font-weight:600; color:var(--danger); animation:pop .35s ease; }
        .password-wrap { position:relative; }
        .password-wrap input { padding-right:56px; }
        .password-toggle { position:absolute; top:50%; right:8px; display:grid; place-items:center; width:40px; height:40px; margin-top:-20px; border:0; border-radius:50%; background:transparent; color:var(--coffee); cursor:pointer; transition:background-color .2s ease, transform .2s ease; }
        .password-toggle:hover { background:var(--beige); transform:scale(1.08); }
        .password-toggle svg { width:20px; height:20px; }
        .password-toggle .icon-hide { display:none; }
        .password-toggle[aria-pressed="true"] .icon-show { display:none; }
        .password-toggle[aria-pressed="true"] .icon-hide { display:block; }
        .forgot-link { display:inline-block; margin-top:10px; font-size:13px; font-weight:700; color:var(--coffee); text-decoration:none; background:linear-gradient(currentColor,currentColor) left bottom/0 2px no-repeat; transition:background-size .3s ease, color .2s ease; }
        .forgot-link:hover { color:var(--espresso); background-size:100% 2px; }

        .submit-button { position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center; gap:10px; width:100%; height:58px; margin-top:8px; border:0; border-radius:99px; background:var(--espresso); color:#fff7ef; font:700 16px 'Plus Jakarta Sans',sans-serif; cursor:pointer; box-shadow:0 14px 28px rgba(84,40,18,.28); transition:transform .25s ease, background-color .25s ease, box-shadow .25s ease; }
        .submit-button::before { content:""; position:absolute; inset:0; background:linear-gradient(105deg,transparent 35%,rgba(255,255,255,.22) 50%,transparent 65%); transform:translateX(-120%); }
        .submit-button:hover { background:var(--coffee); transform:translateY(-2px); box-shadow:0 18px 32px rgba(84,40,18,.34); }
        .submit-button:hover::before { animation:sweep .9s ease; }
        @keyframes sweep { to { transform:translateX(120%); } }
        .submit-button:active { transform:scale(.98); }
        .submit-button.is-loading { pointer-events:none; opacity:.9; }
        .submit-button.is-loading::after { content:""; width:18px; height:18px; border-radius:50%; border:2.5px solid rgba(255,255,255,.35); border-top-color:#fff; animation:spin .7s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }

        .form-switch { margin:20px 0 0; text-align:center; font-size:14px; color:var(--muted); }
        .form-switch + .form-switch { margin-top:8px; }
        .form-switch a { font-weight:700; color:var(--espresso); text-decoration:none; background:linear-gradient(var(--caramel),var(--caramel)) left bottom/100% 2px no-repeat; padding-bottom:2px; transition:background-size .3s ease, color .2s ease; }
        .form-switch a:hover { background-size:100% 100%; color:var(--espresso); }

        /* Live password helpers (added by script) */
        .strength { margin-top:10px; }
        .strength-bars { display:grid; grid-template-columns:repeat(4,1fr); gap:6px; }
        .strength-bars i { height:6px; border-radius:99px; background:var(--beige); transition:background-color .3s ease, transform .3s ease; }
        .strength[data-level="1"] i:nth-child(-n+1) { background:#e0584a; transform:scaleY(1.2); }
        .strength[data-level="2"] i:nth-child(-n+2) { background:#e3a03c; transform:scaleY(1.2); }
        .strength[data-level="3"] i:nth-child(-n+3) { background:#9bbf5a; transform:scaleY(1.2); }
        .strength[data-level="4"] i:nth-child(-n+4) { background:#3f9b5f; transform:scaleY(1.2); }
        .strength-text { margin:7px 0 0; font-size:12px; font-weight:700; color:var(--muted); min-height:16px; transition:color .3s ease; }
        .match-text { margin:7px 0 0; font-size:12px; font-weight:700; min-height:16px; transition:color .3s ease; }
        .match-text.ok { color:var(--ok); } .match-text.no { color:var(--danger); }

        .legal { margin-top:26px; font-size:12px; color:var(--muted); text-align:center; }

        /* ===== Responsive ===== */
        @media (max-width:960px) {
            .auth-shell { grid-template-columns:1fr; padding:10px; gap:10px; }
            .story { padding:22px 24px; border-radius:30px; }
            .story-body { flex-direction:row; align-items:center; gap:18px; text-align:left; padding:10px 0 4px; }
            .mascot { width:92px; flex-shrink:0; }
            .story-title { margin:0; font-size:26px; max-width:none; }
            .story-copy, .story-foot { display:none; }
            .pb.far { display:none; }
            .panel { padding:28px 14px 36px; }
        }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration:.01ms !important; animation-iteration-count:1 !important; transition-duration:.01ms !important; scroll-behavior:auto !important; } }
    </style>
    @stack('styles')
</head>
<body>
    <div class="auth-shell">
        {{-- ===== Story side ===== --}}
        <aside class="story" id="story" aria-label="SipPly">
            <span class="pb" style="--depth:22;--w:34px;--r:-25deg;--dur:6s;left:8%;top:26%"><i></i></span>
            <span class="pb far" style="--depth:34;--w:26px;--r:35deg;--dur:7s;--delay:-2s;right:10%;top:20%"><i></i></span>
            <span class="pb" style="--depth:16;--w:22px;--r:12deg;--dur:5s;--delay:-3s;left:16%;bottom:24%"><i></i></span>
            <span class="pb far" style="--depth:28;--w:30px;--r:-48deg;--dur:6.5s;--delay:-1s;right:14%;bottom:30%"><i></i></span>
            <span class="pb far" style="--depth:12;--w:20px;--r:60deg;--dur:5.5s;--delay:-4s;right:30%;top:10%"><i></i></span>
            <span class="pb far" style="--depth:30;--w:24px;--r:-10deg;--dur:7.5s;--delay:-5s;left:30%;bottom:10%"><i></i></span>

            <a class="brand" href="{{ route('home') }}" aria-label="SipPly home">
                <img src="{{ asset('images/favicon.png') }}" alt="" width="48" height="48"><span>SipPly</span>
            </a>

            <div class="story-body">
                <div class="mascot" id="mascot" aria-hidden="true">
                    <svg viewBox="0 0 220 230">
                        <g class="steam"><path d="M82 52c-8-10 8-16 0-28"/><path d="M110 46c-8-10 8-16 0-30"/><path d="M138 52c-8-10 8-16 0-28"/></g>
                        <path d="M170 98h10a22 22 0 0 1 0 44h-14" fill="none" stroke="#D99A69" stroke-width="12" stroke-linecap="round"/>
                        <path d="M40 78h132v62a52 52 0 0 1-52 52h-28a52 52 0 0 1-52-52Z" fill="#FFFCF8" stroke="#F5E2D1" stroke-width="7" stroke-linejoin="round"/>
                        <path d="M40 78h132v22H40Z" fill="#D99A69"/>
                        <text x="106" y="95" text-anchor="middle" font-family="Georgia,serif" font-style="italic" font-weight="700" font-size="17" fill="#542812">SipPly</text>
                        <g class="eyes">
                            <ellipse cx="84" cy="130" rx="9" ry="11" fill="#fff"/><ellipse cx="128" cy="130" rx="9" ry="11" fill="#fff"/>
                            <g class="pupils" id="pupils"><circle cx="84" cy="131" r="5.5" fill="#382015"/><circle cx="128" cy="131" r="5.5" fill="#382015"/></g>
                        </g>
                        <g class="closed" fill="none" stroke="#382015" stroke-width="4.5" stroke-linecap="round"><path d="M75 132q9 7 18 0"/><path d="M119 132q9 7 18 0"/></g>
                        <circle cx="70" cy="150" r="9" fill="#F3B79A" opacity=".75"/><circle cx="142" cy="150" r="9" fill="#F3B79A" opacity=".75"/>
                        <path d="M95 150q11 12 22 0" fill="none" stroke="#382015" stroke-width="5" stroke-linecap="round"/>
                        <g class="hands"><ellipse cx="82" cy="132" rx="19" ry="15" fill="#F3D3B8" stroke="#D99A69" stroke-width="3"/><ellipse cx="130" cy="132" rx="19" ry="15" fill="#F3D3B8" stroke="#D99A69" stroke-width="3"/></g>
                        <ellipse cx="106" cy="210" rx="64" ry="8" fill="rgba(0,0,0,.18)"/>
                    </svg>
                </div>
                <div>
                    <h1 class="story-title">@yield('story-title')</h1>
                    <p class="story-copy">@yield('story-copy')</p>
                </div>
            </div>

            <div class="story-foot">
                <span>SipPly · Something good is brewing.</span>
                <a class="back-link" href="{{ route('home') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>Back to store</a>
            </div>
        </aside>

        {{-- ===== Form side ===== --}}
        <main class="panel">
            <div class="auth-card">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
    (function () {
        var story = document.getElementById('story');
        var mascot = document.getElementById('mascot');
        var pupils = document.getElementById('pupils');
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Beans drift and the mascot's eyes follow the cursor
        if (!reduce) {
            document.addEventListener('mousemove', function (e) {
                var w = window.innerWidth, h = window.innerHeight;
                story.style.setProperty('--mx', ((e.clientX / w) - .5) * 2);
                story.style.setProperty('--my', ((e.clientY / h) - .5) * 2);
                if (pupils && mascot && !mascot.classList.contains('is-shy')) {
                    var r = mascot.getBoundingClientRect();
                    var dx = e.clientX - (r.left + r.width / 2), dy = e.clientY - (r.top + r.height * .55);
                    var d = Math.max(Math.hypot(dx, dy), 1), k = Math.min(d / 260, 1) * 4.5;
                    pupils.style.transform = 'translate(' + (dx / d * k).toFixed(2) + 'px,' + (dy / d * k).toFixed(2) + 'px)';
                }
            });
        }

        // The mascot covers its eyes while a hidden password is being typed
        function updateShy() {
            if (!mascot) { return; }
            var a = document.activeElement;
            var hidden = a && a.tagName === 'INPUT' && a.type === 'password';
            mascot.classList.toggle('is-shy', !!hidden);
        }
        document.addEventListener('focusin', updateShy);
        document.addEventListener('focusout', function () { setTimeout(updateShy, 0); });
        document.addEventListener('click', function (e) { if (e.target.closest('.password-toggle')) { setTimeout(updateShy, 30); } });

        // Little hop for a valid-looking email
        var email = document.querySelector('input[type="email"]');
        if (email && mascot) {
            email.addEventListener('change', function () {
                if (email.value && email.checkValidity()) {
                    mascot.classList.remove('is-happy'); void mascot.offsetWidth; mascot.classList.add('is-happy');
                }
            });
        }

        // Live password strength + match hint
        var pw = document.querySelector('input[name="password"][autocomplete="new-password"]');
        var confirmEl = document.querySelector('input[name="password_confirmation"]');
        function setText(el, t) { el.textContent = t; }
        if (pw) {
            var field = pw.closest('.field');
            var box = document.createElement('div');
            box.className = 'strength'; box.setAttribute('data-level', '0');
            box.innerHTML = '<div class="strength-bars" aria-hidden="true"><i></i><i></i><i></i><i></i></div><p class="strength-text" aria-live="polite"></p>';
            field.appendChild(box);
            var label = box.querySelector('.strength-text');
            var names = ['', 'Too weak', 'Getting there', 'Good', 'Strong'];
            pw.addEventListener('input', function () {
                var v = pw.value, s = 0;
                if (v.length >= 8) { s++; }
                if (/[a-z]/.test(v) && /[A-Z]/.test(v)) { s++; }
                if (/\d/.test(v)) { s++; }
                if (/[^A-Za-z0-9]/.test(v) || v.length >= 14) { s++; }
                if (!v) { s = 0; }
                box.setAttribute('data-level', s);
                setText(label, v ? names[Math.max(s, 1)] : '');
                checkMatch();
            });
        }
        var matchEl = null;
        function checkMatch() {
            if (!confirmEl || !matchEl) { return; }
            if (!confirmEl.value) { matchEl.className = 'match-text'; setText(matchEl, ''); return; }
            var ok = pw && confirmEl.value === pw.value;
            matchEl.className = 'match-text ' + (ok ? 'ok' : 'no');
            setText(matchEl, ok ? 'Passwords match' : 'Passwords don’t match yet');
        }
        if (confirmEl) {
            matchEl = document.createElement('p'); matchEl.className = 'match-text'; matchEl.setAttribute('aria-live', 'polite');
            confirmEl.closest('.field').appendChild(matchEl);
            confirmEl.addEventListener('input', checkMatch);
        }

        // Button feedback while the form is submitting (the form still posts normally)
        document.querySelectorAll('.auth-card form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('.submit-button');
                if (!btn) { return; }
                btn.classList.add('is-loading');
                var t = btn.textContent.trim();
                btn.setAttribute('data-label', t);
                btn.firstChild && (btn.firstChild.nodeType === 3) ? btn.firstChild.nodeValue = 'One moment…' : null;
                setTimeout(function () { btn.disabled = true; }, 0);
            });
        });

        // Restore the button if the user comes back with the browser's back button
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) { return; }
            document.querySelectorAll('.submit-button').forEach(function (b) {
                b.classList.remove('is-loading'); b.disabled = false;
                if (b.getAttribute('data-label')) { b.firstChild.nodeValue = b.getAttribute('data-label'); }
            });
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>