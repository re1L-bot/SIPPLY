{{--
    SipPly shared footer partial.
    Usage: @include('partials.footer')
    Works on both Tailwind-based pages and inline-CSS pages.
--}}
<footer style="position:relative;z-index:20;background:#3b1c0e;color:#fff">
    <div style="width:min(1240px,calc(100% - 48px));margin-inline:auto;padding:56px 0 36px;display:grid;gap:40px;grid-template-columns:1.4fr .7fr .9fr">

        {{-- Brand + description --}}
        <div>
            <a href="{{ route('home') }}" style="display:inline-flex;align-items:center;gap:10px;text-decoration:none">
                <img src="{{ asset('images/favicon.png') }}" alt="SipPly" width="44" height="44" style="border-radius:50%;object-fit:cover">
                <span style="font-family:Georgia,'Times New Roman',serif;font-size:24px;font-weight:700;letter-spacing:-.8px;color:#fff">SipPly</span>
            </a>
            <p style="margin:14px 0 0;max-width:280px;font-size:13.5px;line-height:1.75;color:#e5cdbb">
                Your neighborhood specialty coffee shop in Bantayan — where every cup is crafted with care and every visit feels like home.
            </p>
            <p style="margin:20px 0 0;font-size:12px;color:#a08070">&copy; {{ now()->year }} SipPly. All rights reserved.</p>
        </div>

        {{-- Contact Information --}}
        <div>
            <p style="font-size:11px;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:#f0a672;margin:0 0 16px">Contact</p>
            <div style="display:flex;flex-direction:column;gap:12px">
                <div style="display:flex;align-items:flex-start;gap:10px;font-size:13px;color:#ecd9c9">
                    <svg width="15" height="15" fill="none" stroke="#f0a672" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px" aria-hidden="true">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span>Bantayan, Cebu, Philippines</span>
                </div>
                <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:#ecd9c9">
                    <svg width="15" height="15" fill="none" stroke="#f0a672" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="flex-shrink:0" aria-hidden="true">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.44 2 2 0 0 1 3.59 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6.09 6.09l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z"/>
                    </svg>
                    <a href="tel:+639000000000" style="color:#ecd9c9;text-decoration:none">+63 900 000 0000</a>
                </div>
                <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:#ecd9c9">
                    <svg width="15" height="15" fill="none" stroke="#f0a672" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="flex-shrink:0" aria-hidden="true">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <a href="mailto:hello@sipply.ph" style="color:#ecd9c9;text-decoration:none">hello@sipply.ph</a>
                </div>
                <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:#ecd9c9">
                    <svg width="15" height="15" fill="none" stroke="#f0a672" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="flex-shrink:0" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>Mon–Sun · 7:00 AM – 9:00 PM</span>
                </div>
            </div>
        </div>

        {{-- Social Media Links --}}
        <div>
            <p style="font-size:11px;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:#f0a672;margin:0 0 16px">Follow Along</p>
            <div style="display:flex;flex-direction:column;gap:12px">
                <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer"
                   style="display:inline-flex;align-items:center;gap:10px;font-size:13px;font-weight:600;color:#ecd9c9;text-decoration:none;transition:color .15s"
                   onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#ecd9c9'">
                    <span style="display:grid;place-items:center;width:36px;height:36px;border-radius:50%;border:1px solid rgba(255,255,255,.18);flex-shrink:0">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </span>
                    Facebook
                </a>
                <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer"
                   style="display:inline-flex;align-items:center;gap:10px;font-size:13px;font-weight:600;color:#ecd9c9;text-decoration:none"
                   onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#ecd9c9'">
                    <span style="display:grid;place-items:center;width:36px;height:36px;border-radius:50%;border:1px solid rgba(255,255,255,.18);flex-shrink:0">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                    </span>
                    Instagram
                </a>
                <a href="https://www.tiktok.com/" target="_blank" rel="noopener noreferrer"
                   style="display:inline-flex;align-items:center;gap:10px;font-size:13px;font-weight:600;color:#ecd9c9;text-decoration:none"
                   onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#ecd9c9'">
                    <span style="display:grid;place-items:center;width:36px;height:36px;border-radius:50%;border:1px solid rgba(255,255,255,.18);flex-shrink:0">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>
                    </span>
                    TikTok
                </a>
            </div>
        </div>
    </div>

    {{-- Bottom divider + copyright bar --}}
    <div style="border-top:1px solid rgba(255,255,255,.1);padding:16px 24px;text-align:center;font-size:11.5px;color:#a08070">
        &copy; {{ now()->year }} SipPly &mdash; Something good is brewing. &nbsp;·&nbsp; Bantayan, Cebu, Philippines
    </div>
</footer>

{{-- Mobile responsive override --}}
<style>
    @media (max-width: 720px) {
        footer > div:first-child {
            grid-template-columns: 1fr !important;
        }
    }
</style>
