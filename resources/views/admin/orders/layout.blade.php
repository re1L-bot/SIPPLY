{{-- resources/views/admin/layout.blade.php  (SipPly admin shell: sidebar + top bar + shared components) --}}
@php
    $user = auth()->user();
    $adminName = $user->name ?? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'Admin';
    $initials = collect(explode(' ', $adminName))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $avatar = $user->avatar_url ?? $user->profile_photo_url ?? null;
    $r = fn (string $name, $fallback = '#') => \Illuminate\Support\Facades\Route::has($name) ? route($name) : $fallback;
    // Adjust the route names on the left if yours differ. Missing routes fall back to '#', so nothing breaks.
    $nav = [
        ['Dashboard',            $r('admin.dashboard'),      'admin.dashboard',       '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>'],
        ['Products & Categories',$r('admin.products.index'), 'admin.products.*',      '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>'],
        ['Orders',               $r('admin.orders.index'),   'admin.orders.*',        '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/>'],
        ['Inventory',            $r('admin.inventory.index'),'admin.inventory.*',     '<path d="M21 8v13H3V8"/><path d="M1 3h22v5H1z"/><path d="M10 12h4"/>'],
        ['Customers',            $r('admin.customers.index'),'admin.customers.*',     '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
        ['Delivery Fees',        $r('admin.delivery-fees.index'),'admin.delivery-fees.*','<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · SipPly</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --bg:#FFF7EF; --side:#542812; --accent:#75452D; --caramel:#D99A69; --beige:#F5E2D1; --card:#FFFCF8; --ink:#382015; --muted:#8C7769; --line:rgba(84,40,18,.10); --sidew:272px; }
        body { margin:0; background:var(--bg); color:var(--ink); font-family:'Inter',ui-sans-serif,system-ui,sans-serif; }
        .ico { fill:none; stroke:currentColor; stroke-width:1.75; stroke-linecap:round; stroke-linejoin:round; flex-shrink:0; }
        .font-display { font-family:Georgia,'Times New Roman',serif; }

        /* Sidebar */
        .sidebar { position:fixed; inset:12px auto 12px 12px; width:calc(var(--sidew) - 24px); z-index:50; display:flex; flex-direction:column; overflow:hidden; border-radius:32px; background:linear-gradient(180deg,#5d2d15,var(--side) 55%,#47200e); color:#fff; transition:transform .3s ease; }
        .sidebar .brand { padding:34px 0 22px; text-align:center; font:italic 700 32px Georgia,serif; letter-spacing:-.5px; }
        .nav-link { display:flex; align-items:center; gap:14px; margin:4px 14px; padding:13px 18px; border-radius:999px; font-size:14px; font-weight:600; color:rgba(255,255,255,.88); transition:background-color .2s, transform .2s; }
        .nav-link:hover { background:rgba(255,255,255,.1); transform:translateX(3px); }
        .nav-link.is-active { background:#FFF7EF; color:var(--side); box-shadow:0 6px 16px rgba(0,0,0,.18); }
        .side-note { margin:auto 18px 18px; padding:22px 16px; border-radius:24px; text-align:center; font-size:14px; background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.1); }
        .scrim { position:fixed; inset:0; z-index:40; background:rgba(56,32,21,.45); opacity:0; pointer-events:none; transition:opacity .3s; }
        .shell { margin-left:var(--sidew); padding:20px 28px 40px 16px; }
        @media (max-width:1023px) {
            .sidebar { transform:translateX(-120%); }
            body.nav-open .sidebar { transform:none; }
            body.nav-open .scrim { opacity:1; pointer-events:auto; }
            .shell { margin-left:0; padding:16px; }
        }

        /* Top bar */
        .search { display:flex; align-items:center; gap:12px; flex:1; max-width:640px; padding:0 20px; height:48px; border-radius:999px; background:rgba(255,252,248,.85); border:1px solid var(--line); }
        .search input { flex:1; min-width:0; border:0; outline:0; background:transparent; font-size:14px; color:var(--ink); }
        .search:focus-within { border-color:var(--caramel); box-shadow:0 0 0 3px rgba(217,154,105,.25); }
        .avatar { display:grid; place-items:center; width:44px; height:44px; border-radius:999px; background:var(--accent); color:#fff7ef; font-weight:700; overflow:hidden; }
        .avatar img { width:100%; height:100%; object-fit:cover; }
        .menu { position:absolute; right:0; top:calc(100% + 10px); min-width:200px; padding:8px; border-radius:18px; background:var(--card); border:1px solid var(--line); box-shadow:0 12px 32px rgba(84,40,18,.16); z-index:30; }
        .menu a, .menu button { display:block; width:100%; text-align:left; padding:10px 14px; border-radius:12px; font-size:14px; font-weight:600; color:var(--ink); background:none; border:0; cursor:pointer; }
        .menu a:hover, .menu button:hover { background:var(--beige); }

        /* Shared components: use these on every admin page */
        .card { background:var(--card); border:1px solid var(--line); border-radius:28px; box-shadow:0 1px 2px rgba(84,40,18,.05), 0 8px 24px rgba(84,40,18,.06); }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:11px 22px; border-radius:999px; font-size:14px; font-weight:700; border:1px solid transparent; cursor:pointer; transition:background-color .2s, transform .2s; }
        .btn-primary { background:var(--side); color:#fff7ef; } .btn-primary:hover { background:var(--accent); transform:translateY(-1px); }
        .btn-soft { background:var(--beige); color:var(--accent); } .btn-soft:hover { background:#efd5bd; }
        .btn-ghost { background:transparent; color:#fff; border-color:rgba(255,255,255,.6); } .btn-ghost:hover { background:rgba(255,255,255,.12); }
        .btn-danger { background:#fff0ed; color:#a4473d; }
        .input { width:100%; height:46px; padding:0 16px; border-radius:14px; border:1px solid var(--line); background:#fff; color:var(--ink); font-size:14px; }
        .input:focus { outline:0; border-color:var(--caramel); box-shadow:0 0 0 3px rgba(217,154,105,.25); }
        .table-wrap { overflow-x:auto; border-radius:18px; }
        .table { width:100%; min-width:680px; border-collapse:separate; border-spacing:0; font-size:14px; }
        .table thead th { background:var(--beige); padding:13px 18px; text-align:left; font-size:12px; font-weight:700; color:var(--muted); white-space:nowrap; }
        .table thead th:first-child { border-radius:14px 0 0 14px; } .table thead th:last-child { border-radius:0 14px 14px 0; }
        .table th.num, .table td.num { text-align:right; font-variant-numeric:tabular-nums; }
        .table tbody td { padding:16px 18px; border-bottom:1px solid var(--line); vertical-align:middle; }
        .table tbody tr:last-child td { border-bottom:0; }
        .table tbody tr:hover { background:rgba(245,226,209,.45); }
        .badge { display:inline-block; border-radius:999px; padding:5px 13px; font-size:12px; font-weight:700; white-space:nowrap; }
        .badge-ok { background:#e5f3e3; color:#2f6a45; } .badge-warn { background:#fdf0d5; color:#8f5b17; } .badge-bad { background:#ffe3df; color:#a4473d; } .badge-neutral { background:var(--beige); color:#6b4a38; }
        .pager { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-top:16px; }
        .pager-nav { display:flex; align-items:center; gap:4px; }
        .pg { display:inline-grid; place-items:center; min-width:36px; height:36px; padding:0 10px; border-radius:12px; font-size:13px; font-weight:700; color:var(--accent); cursor:pointer; background:transparent; border:0; transition:background-color .15s; }
        .pg:hover { background:var(--beige); } .pg.is-current { background:var(--side); color:#fff7ef; cursor:default; }
        .pg[aria-disabled="true"], .pg:disabled { opacity:.4; pointer-events:none; }
        .modal-backdrop { position:fixed; inset:0; z-index:60; display:grid; place-items:center; padding:16px; background:rgba(56,32,21,.5); }
        .modal { width:100%; max-width:520px; padding:28px; border-radius:28px; background:var(--card); box-shadow:0 24px 60px rgba(56,32,21,.3); }
        [hidden] { display:none !important; }
        a:focus-visible, button:focus-visible, summary:focus-visible { outline:2px solid var(--caramel); outline-offset:2px; }
        @media (prefers-reduced-motion:reduce) { * { animation:none !important; transition:none !important; } }
    </style>
    @stack('styles')
</head>
<body>
    <div class="scrim" data-nav-close></div>

    <aside class="sidebar" aria-label="Admin navigation">
        <a href="{{ $r('admin.dashboard') }}" class="brand">
            <svg class="ico" width="46" height="46" viewBox="0 0 24 24" aria-hidden="true" style="display:block;margin:0 auto 4px"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><path d="M8 2v2"/><path d="M12 2v2"/></svg>
            SipPly
        </a>
        <nav class="mt-2">
            @foreach ($nav as [$label, $href, $pattern, $icon])
                <a href="{{ $href }}" class="nav-link {{ request()->routeIs($pattern) ? 'is-active' : '' }}" @if (request()->routeIs($pattern)) aria-current="page" @endif>
                    <svg class="ico" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">{!! $icon !!}</svg>
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <div class="side-note">
            <svg class="ico" width="30" height="30" viewBox="0 0 24 24" aria-hidden="true" style="margin:0 auto 8px;display:block"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/></svg>
            Good vibes,<br>great coffee
        </div>
    </aside>

    <div class="shell">
        <header class="flex items-center gap-3" style="margin-bottom:20px">
            <button type="button" class="btn btn-soft lg:hidden" data-nav-open aria-label="Open menu" style="padding:12px">
                <svg class="ico" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <form class="search" role="search" method="GET" action="{{ $r('admin.orders.index') }}">
                <svg class="ico" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search order ID or customer name…" aria-label="Search orders by order ID or customer name">
            </form>
            <div class="ml-auto flex items-center gap-4">
                <button type="button" class="btn btn-soft" aria-label="Notifications" style="padding:12px;position:relative">
                    <svg class="ico" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    @if (($pendingOrders ?? 0) > 0)<span style="position:absolute;top:8px;right:9px;width:9px;height:9px;border-radius:99px;background:#e0443a"></span>@endif
                </button>
                <div class="relative" style="position:relative">
                    <button type="button" class="flex items-center gap-3" data-menu-toggle aria-haspopup="true" aria-expanded="false" style="background:none;border:0;cursor:pointer;color:inherit">
                        <span class="avatar">@if ($avatar)<img src="{{ $avatar }}" alt="">@else{{ $initials }}@endif</span>
                        <span class="hidden sm:block" style="text-align:left;line-height:1.25">
                            <span style="display:block;font-size:14px;font-weight:700">{{ $adminName }}</span>
                            <span style="display:block;font-size:12px;color:var(--muted)">Owner</span>
                        </span>
                        <svg class="ico" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="menu" data-menu hidden>
                        <a href="{{ $r('admin.dashboard') }}">Dashboard</a>
                        @if (\Illuminate\Support\Facades\Route::has('logout'))
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Log out</button></form>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <main>@yield('content')</main>
    </div>

    <script>
        (function () {
            var b = document.body;
            document.querySelector('[data-nav-open]')?.addEventListener('click', function () { b.classList.add('nav-open'); });
            document.querySelectorAll('[data-nav-close]').forEach(function (el) { el.addEventListener('click', function () { b.classList.remove('nav-open'); }); });
            var t = document.querySelector('[data-menu-toggle]'), m = document.querySelector('[data-menu]');
            function set(open) { m.hidden = !open; t.setAttribute('aria-expanded', open); }
            t.addEventListener('click', function (e) { e.stopPropagation(); set(m.hidden); });
            document.addEventListener('click', function () { set(false); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { set(false); b.classList.remove('nav-open'); } });
        })();
    </script>
    @stack('scripts')
</body>
</html>
