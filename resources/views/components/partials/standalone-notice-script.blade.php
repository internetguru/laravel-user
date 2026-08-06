{{--
    Toggles the system notice branches that depend on how the app is displayed:
    - `.use-app` (install hint) is hidden once the app already runs standalone,
    - `.app-login` (sign in hint) is revealed only when the app runs standalone.
--}}
@once
    <script>
        (function () {
            const isStandalone = () =>
                window.matchMedia('(display-mode: standalone)').matches
                || window.matchMedia('(display-mode: fullscreen)').matches
                || window.matchMedia('(display-mode: minimal-ui)').matches
                || window.navigator.standalone === true;

            const apply = () => {
                const standalone = isStandalone();
                document.querySelectorAll('.use-app').forEach(el => el.classList.toggle('d-none', standalone));
                document.querySelectorAll('.app-login').forEach(el => el.classList.toggle('d-none', !standalone));
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', apply);
            } else {
                apply();
            }

            window.matchMedia('(display-mode: standalone)').addEventListener('change', apply);
        })();
    </script>
@endonce
