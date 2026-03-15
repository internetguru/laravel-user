/**
 * Reusable Playwright E2E test helpers for laravel-user package.
 *
 * Usage in consuming apps:
 *
 *   import { registerUserTests } from '../../vendor/internetguru/laravel-user/tests/e2e/user-tests.js';
 *   registerUserTests(test, expect, { demo: true });
 */
export function registerUserTests(test, expect, options = {}) {
  const { demo = false } = options;

  test.describe('laravel-user: login page', () => {

    test('login page loads', async ({ page }) => {
      await page.goto('/login');
      await expect(page).toHaveURL('/login');
      await expect(page.locator('.section-login')).toBeVisible();
    });

    test('login page has email field', async ({ page }) => {
      await page.goto('/login');
      await expect(page.locator('[name="email"]')).toBeVisible();
    });

    test('login page has submit button', async ({ page }) => {
      await page.goto('/login');
      await expect(page.locator('.section-login button[type="submit"], .section-login input[type="submit"]').first()).toBeVisible();
    });

  });

  test.describe('laravel-user: access control', () => {

    test('unauthenticated user is redirected from protected pages', async ({ page }) => {
      await page.goto('/users');
      await expect(page).toHaveURL(/\/login/);
    });

  });

  if (demo) {

    test.describe('laravel-user: demo login', () => {

      test('demo login page shows user select', async ({ page }) => {
        await page.goto('/login');
        await expect(page.locator('select[name="email"]')).toBeVisible();
      });

      test('demo login and logout', async ({ page }) => {
        // Login via demo select — pick the first non-empty option
        await page.goto('/login');
        const select = page.locator('select[name="email"]');
        const firstValue = await select.locator('option:not([value=""])').first().getAttribute('value');
        await select.selectOption(firstValue);
        await page.locator('button[type="submit"], input[type="submit"]').first().click();

        // Should be redirected away from login
        await expect(page).not.toHaveURL(/\/login/);

        // Logout
        await page.goto('/logout');
        await expect(page).toHaveURL('/');
      });

      test('demo login session persists across navigation', async ({ page }) => {
        // Login
        await page.goto('/login');
        const select = page.locator('select[name="email"]');
        const firstValue = await select.locator('option:not([value=""])').first().getAttribute('value');
        await select.selectOption(firstValue);
        await page.locator('button[type="submit"], input[type="submit"]').first().click();
        await expect(page).not.toHaveURL(/\/login/);

        // Visit a protected page — should stay (not redirect to login)
        await page.goto('/users');
        await expect(page).not.toHaveURL(/\/login/);
      });

    });

  }
}
