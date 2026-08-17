import 'pwa-add-to-homescreen/dist/add-to-homescreen.min.css';
import 'pwa-add-to-homescreen/dist/add-to-homescreen.min.js';
import '../sass/add-to-homescreen.scss';

/**
 * Path the images are copied to by the `addToHomescreenAssets` Vite plugin.
 *
 * @type {string}
 */
export const assetUrl = '/vendor/add-to-homescreen/img/';

const supportedLocales = ['cs', 'en'];

/**
 * Replacement for the library's first iOS Chrome step, which claims the share button
 * sits in the upper right corner. That only holds when the address bar is at the top,
 * and Chrome for iOS lets the user move it to the bottom. `%s` is the button image.
 *
 * @type {Object<string, string>}
 */
const iosChromeFirstStep = {
    cs: 'Klepněte na tlačítko %s v adresním řádku.',
    en: 'Tap the %s button in the address bar.',
};

/**
 * Resolve the locale the guide should be rendered in, falling back to the
 * library's own auto-detection when the page locale is not supported.
 *
 * @return {string|undefined}
 */
const guideLocale = () => {
    const locale = (document.documentElement.lang || '').slice(0, 2).toLowerCase();

    return supportedLocales.includes(locale) ? locale : undefined;
};

/**
 * Rewrite the first step of the freshly rendered iOS Chrome guide, keeping the
 * button image the library generated in place of the `%s` placeholder.
 *
 * @param {string|undefined} locale
 * @return {void}
 */
const fixIOSChromeInstruction = locale => {
    const instruction = document.querySelector('.adhs-container.adhs-ios.adhs-chrome .adhs-list-item .adhs-instruction');
    const button = instruction?.querySelector('.adhs-list-button');
    const template = iosChromeFirstStep[locale ?? 'en'] ?? iosChromeFirstStep.en;

    if (! button) {
        return;
    }

    const [before, after] = template.split('%s');
    instruction.replaceChildren(before, button, after);
};

/**
 * The instance has to be created on load, the guide itself may be shown later.
 * See https://github.com/philfung/add-to-homescreen#special-case-calling-the-ui-later
 */
const createInstance = () => window.AddToHomeScreen({
    appName: document.querySelector('meta[name="apple-mobile-web-app-title"]')?.content ?? document.title,
    appNameDisplay: 'inline',
    appIconUrl: '/apple-touch-icon.png',
    assetUrl,
    maxModalDisplayCount: -1,
    displayOptions: { showMobile: true, showDesktop: true },
    allowClose: true,
    showArrow: true,
});

/**
 * Bind the `use-app` system notice link to the add-to-homescreen guide.
 *
 * @return {void}
 */
const init = () => {
    const instance = window.AddToHomeScreenInstance = createInstance();

    // Delegated so the trigger keeps working after Livewire morphs the notice
    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-add-to-homescreen]');
        if (! trigger) {
            return;
        }

        event.preventDefault();

        const locale = guideLocale();
        instance.show(locale);

        if (instance.isBrowserIOSChrome()) {
            fixIOSChromeInstruction(locale);
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
