import fs from 'node:fs';
import path from 'node:path';

/**
 * The add-to-homescreen library loads its illustrations at runtime from `assetUrl`,
 * so they cannot go through the regular Vite asset pipeline. This plugin mirrors them
 * from `node_modules` into the application's public directory on both dev and build.
 * The demo images shipped with the package are skipped.
 *
 * Register it in the application's `vite.config.js`:
 *
 *     import addToHomescreenAssets from './vendor/internetguru/laravel-user/resources/js/vite-add-to-homescreen-assets';
 *
 *     plugins: [laravel({ ... }), addToHomescreenAssets()],
 *
 * @param {{root?: string, target?: string}} options
 * @return {import('vite').Plugin}
 */
export default function addToHomescreenAssets(options = {}) {
    const root = options.root ?? process.cwd();
    const target = options.target ?? 'public/vendor/add-to-homescreen/img';
    const skipped = /^(sample|aardvark-.*|your-app-icon\.svg)$/;

    return {
        name: 'ig-user-add-to-homescreen-assets',
        buildStart() {
            const source = path.resolve(root, 'node_modules/pwa-add-to-homescreen/dist/assets/img');
            const destination = path.resolve(root, target);

            if (! fs.existsSync(source)) {
                this.warn('pwa-add-to-homescreen is not installed, the add-to-homescreen guide will render without images.');

                return;
            }

            fs.rmSync(destination, { recursive: true, force: true });
            fs.mkdirSync(destination, { recursive: true });

            for (const entry of fs.readdirSync(source)) {
                if (skipped.test(entry)) {
                    continue;
                }
                fs.cpSync(path.join(source, entry), path.join(destination, entry), { recursive: true });
            }
        },
    };
}
