{{--
    Toggles the system notice branches that depend on how the app is displayed:
    - `.use-app` (install hint) is hidden once the app already runs standalone,
    - `.app-login` (sign in hint) is revealed only when the app runs standalone.

    Also unwraps the add-to-homescreen trigger in the install hint when the application
    does not bundle `resources/js/add-to-homescreen.js`, so the hint degrades to plain
    text instead of a link that does nothing.
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

            const unwrapUnboundTrigger = () => {
                if (window.AddToHomeScreenInstance) {
                    return;
                }
                document.querySelectorAll('.use-app [data-add-to-homescreen]')
                    .forEach(el => el.replaceWith(...el.childNodes));
            };

            const run = () => {
                apply();
                unwrapUnboundTrigger();
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', run);
            } else {
                run();
            }

            window.matchMedia('(display-mode: standalone)').addEventListener('change', apply);
        })();
    </script>
@endonce
