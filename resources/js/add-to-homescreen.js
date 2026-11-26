import 'pwa-add-to-homescreen/dist/add-to-homescreen.min.css';
import 'pwa-add-to-homescreen/dist/add-to-homescreen.min.js';
import '../sass/add-to-homescreen.scss';

/**
 * Path the images are copied to by the `addToHomescreenAssets` Vite plugin.
 *
 * @type {string}
 */
export const assetUrl = '/vendor/add-to-homescreen/img/';

const supportedLocales = ['cs', 'da', 'en'];

/**
 * Replacement for the library's first iOS Chrome step, which claims the share button
 * sits in the upper right corner. That only holds when the address bar is at the top,
 * and Chrome for iOS lets the user move it to the bottom. `%s` is the button image.
 *
 * @type {Object<string, string>}
 */
const iosChromeFirstStep = {
    cs: 'Klepněte na tlačítko %s v adresním řádku.',
    da: 'Tryk på %s-knappen i adresselinjen.',
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
    // The icon the page already declares, so an application serving it from anywhere
    // but the library's default path is not illustrated with a broken image.
    appIconUrl: document.querySelector('link[rel="apple-touch-icon"]')?.href ?? '/apple-touch-icon.png',
    assetUrl,
    maxModalDisplayCount: -1,
    displayOptions: { showMobile: true, showDesktop: true },
    allowClose: true,
    // The arrow guesses where the browser's button is and points the wrong way with the address bar at the bottom;
    // the steps say where to tap
    showArrow: false,
});

/**
 * Label of the close button the library leaves out.
 *
 * @type {Object<string, string>}
 */
const closeLabel = {
    cs: 'Zavřít',
    da: 'Luk',
    en: 'Close',
};

/**
 * Make the freshly rendered guide a dialog: named by its title and its icon by the app's name, closed by a visible
 * button (unless the library offers its own "Later") or Escape, with the focus on that button so a keyboard reaches it
 * at once, and back on the trigger once the guide closes.
 *
 * @param {object} instance
 * @param {string|undefined} locale
 * @param {Element} trigger
 * @return {boolean} whether a new guide was found and completed
 */
const completeDialog = (instance, locale, trigger) => {
    // A guide closed a moment ago still fades out in the page, so the new one is the last
    const containers = document.querySelectorAll('.adhs-container');
    const container = containers[containers.length - 1];
    const modal = container?.querySelector('.adhs-modal');
    if (! modal || modal.hasAttribute('role')) {
        return false;
    }

    const title = modal.querySelector('h1');
    if (title) {
        title.id ||= 'adhs-dialog-title';
        modal.setAttribute('aria-labelledby', title.id);
    }
    modal.setAttribute('role', 'dialog');
    modal.querySelectorAll('img[alt="logo"]').forEach(image => {
        image.alt = instance.appName ?? document.title;
    });

    let focus = modal.querySelector('.adhs-button-cancel');
    if (! focus) {
        focus = document.createElement('button');
        focus.type = 'button';
        focus.className = 'adhs-close';
        focus.textContent = closeLabel[locale ?? 'en'] ?? closeLabel.en;
        focus.addEventListener('click', () => instance.closeModal());
        modal.append(focus);
    }
    focus.focus();

    const closed = new MutationObserver(() => {
        if (! container.classList.contains('visible') || ! container.isConnected) {
            closed.disconnect();
            trigger.focus?.();
        }
    });
    closed.observe(container, { attributes: true, attributeFilter: ['class'] });
    closed.observe(document.body, { childList: true });

    return true;
};

/**
 * Complete the guide once it shows: at once on a phone, while desktop Chrome and Edge draw it a moment later.
 *
 * @param {object} instance
 * @param {string|undefined} locale
 * @param {Element} trigger
 * @return {void}
 */
const completeWhenShown = (instance, locale, trigger) => {
    if (completeDialog(instance, locale, trigger)) {
        return;
    }

    const shown = new MutationObserver(() => {
        if (completeDialog(instance, locale, trigger)) {
            shown.disconnect();
        }
    });
    shown.observe(document.body, { childList: true, subtree: true });
    setTimeout(() => shown.disconnect(), 5000);
};

// Escape closes the guide while it is open
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && document.querySelector('.adhs-container.visible')) {
        window.AddToHomeScreenInstance?.closeModal();
    }
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
        completeWhenShown(instance, locale, trigger);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
