{{-- Shared show/hide password toggle. Include once per page. --}}
<style>
    .password-wrap { position: relative; }
    .password-wrap input { width: 100%; padding-right: 3rem; }
    .password-toggle {
        position: absolute; top: 50%; right: .6rem; transform: translateY(-50%);
        display: flex; align-items: center; justify-content: center;
        width: 2rem; height: 2rem; padding: 0; border: 0; border-radius: 50%;
        background: transparent; color: #909b92; cursor: pointer;
    }
    .password-toggle:hover, .password-toggle:focus-visible { color: #422312; outline: none; }
    .password-toggle:focus-visible { box-shadow: 0 0 0 2px rgba(66, 35, 18, .35); }
    .password-toggle svg { width: 20px; height: 20px; }
    .password-toggle .icon-hide { display: none; }
    .password-toggle.is-visible .icon-show { display: none; }
    .password-toggle.is-visible .icon-hide { display: block; }
    .forgot-link { display: inline-block; margin-top: .35rem; font-size: .875rem; }
</style>
<script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.password-toggle');
        if (!btn) return;
        var input = document.getElementById(btn.dataset.target);
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.classList.toggle('is-visible', show);
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
</script>
