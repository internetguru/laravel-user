/**
 * Reusable Playwright E2E test helpers for laravel-user package.
 *
 * Usage in consuming apps:
 *
 *   import { registerUserTests } from '../../vendor/internetguru/laravel-user/tests/e2e/user-tests.js';
 *   registerUserTests(test, expect, { demo: true, mailpitUrl: 'http://localhost:8025' });
 */
import { rmSync } from 'node:fs';
import { join } from 'node:path';

export function registerUserTests(test, expect, options = {}) {
  const { demo = false, mailpitUrl = null } = options;

  // Whether we can perform a login at all (demo or PIN via Mailpit)
  const canLogin = demo || !!mailpitUrl;

  // ---------------------------------------------------------------------------
  // Helpers
  // ---------------------------------------------------------------------------

  /** Login as a demo user by position in the select (0 = lowest role, -1 = highest). */
  async function demoLogin(page, position = 0) {
    await page.goto('/login');
    const select = page.locator('select[name="email"]');
    const opts = select.locator('option:not([value=""])');
    const count = await opts.count();
    const index = position < 0 ? count + position : position;
    const value = await opts.nth(index).getAttribute('value');
    await select.selectOption(value);
    await page.locator('button[type="submit"], input[type="submit"]').first().click();
    await expect(page).not.toHaveURL(/\/login/);
    return value;
  }

  /** Snapshot current Mailpit message IDs (call BEFORE triggering an email). */
  async function mailpitSnapshot() {
    if (!mailpitUrl) return new Set();
    const res = await fetch(`${mailpitUrl}/api/v1/messages`);
    const data = await res.json();
    return new Set((data.messages || []).map(m => m.ID));
  }

  /**
   * Wait for a NEW Mailpit message addressed to `toEmail`.
   * @param {string} toEmail - recipient address to match
   * @param {Set} [excludeIds] - message IDs to ignore (from mailpitSnapshot)
   * @param {number} [timeoutMs=45000] - max wait time
   */
  async function mailpitGetMessage(toEmail, excludeIds, timeoutMs = 20000) {
    if (!excludeIds) excludeIds = await mailpitSnapshot();
    const start = Date.now();
    while (Date.now() - start < timeoutMs) {
      const res = await fetch(`${mailpitUrl}/api/v1/messages`);
      const data = await res.json();
      if (data.messages) {
        const msg = data.messages.find(m =>
          !excludeIds.has(m.ID) &&
          m.To && m.To.some(t => t.Address === toEmail)
        );
        if (msg) {
          const detail = await fetch(`${mailpitUrl}/api/v1/message/${msg.ID}`);
          return await detail.json();
        }
      }
      await new Promise(r => setTimeout(r, 500));
    }
    // Fetch final state for diagnostics
    const lastRes = await fetch(`${mailpitUrl}/api/v1/messages`);
    const lastData = await lastRes.json();
    const allIds = (lastData.messages || []).map(m => m.ID);
    const newMsgs = (lastData.messages || []).filter(m => !excludeIds.has(m.ID));
    const found = newMsgs.map(m => (m.To || []).map(t => t.Address).join(', ')).join(' | ');
    throw new Error(`No Mailpit message for ${toEmail} within ${timeoutMs}ms. New messages: [${found}] (total: ${allIds.length}, excluded: ${excludeIds.size})`);
  }

  /** Extract a 6-digit PIN from email body text (format IG-XXXXXX). */
  function extractPin(text) {
    const m = text.match(/IG-(\d{6})/);
    if (!m) throw new Error('PIN not found in email body');
    return m[1];
  }

  /** Fill the 6-box PIN input component on the verify page. */
  async function fillPinInput(page, pin) {
    // Use paste event — the component's handlePaste fills all boxes at once, avoiding race conditions
    await page.locator('.pin-input-box').first().click();
    await page.evaluate((pinValue) => {
      const el = document.querySelector('.pin-input-box');
      const dt = new DataTransfer();
      dt.setData('text/plain', pinValue);
      el.dispatchEvent(new ClipboardEvent('paste', { clipboardData: dt, bubbles: true }));
    }, pin);
    await expect(page.locator('input[name="pin"]')).toHaveValue(pin);
  }

  /** Login via PIN + Mailpit for a given email. Returns the email. */
  async function pinLogin(page, email) {
    const before = await mailpitSnapshot();
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('.section-login button[type="submit"], .section-login input[type="submit"]').first().click();
    await expect(page).toHaveURL(/\/pin-login\/verify/);
    const msg = await mailpitGetMessage(email, before);
    const pin = extractPin(msg.Text || msg.HTML);
    await fillPinInput(page, pin);
    await page.locator('.section-pin-verify button[type="submit"], .section-pin-verify input[type="submit"]').first().click();
    await expect(page).not.toHaveURL(/\/login|\/pin-login/);
    return email;
  }

  /** Register + login via PIN + Mailpit for a new email. Returns the email. */
  async function pinRegisterAndLogin(page, email) {
    const before = await mailpitSnapshot();
    await page.goto('/login?register=true');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('.section-login button[type="submit"], .section-login input[type="submit"]').first().click();
    await expect(page).toHaveURL(/\/pin-login\/verify/);
    const msg = await mailpitGetMessage(email, before);
    const pin = extractPin(msg.Text || msg.HTML);
    await fillPinInput(page, pin);
    await page.locator('.section-pin-verify button[type="submit"], .section-pin-verify input[type="submit"]').first().click();
    await expect(page).not.toHaveURL(/\/login|\/pin-login/);
    return email;
  }

  /**
   * Login as a manager+ user. Works in both demo and non-demo modes.
   * Returns the email used.
   */
  async function loginAsManager(page) {
    if (demo) return demoLogin(page, -1);
    // Non-demo seed: george@internetguru.io (admin)
    return pinLogin(page, 'george@internetguru.io');
  }

  /**
   * Login as a low-privilege user (customer). Works in both demo and non-demo modes.
   * Returns the email used.
   */
  async function loginAsCustomer(page) {
    if (demo) return demoLogin(page, 0);
    // Non-demo: register a fresh customer via PIN
    const email = `test-customer-${Date.now()}@example.com`;
    return pinRegisterAndLogin(page, email);
  }

  // ---------------------------------------------------------------------------
  // Login page — common
  // ---------------------------------------------------------------------------

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

  // ---------------------------------------------------------------------------
  // Access control — unauthenticated
  // ---------------------------------------------------------------------------

  test.describe('laravel-user: access control', () => {

    test('unauthenticated user is redirected from protected pages', async ({ page }) => {
      await page.goto('/users');
      await expect(page).toHaveURL(/\/login/);
    });

  });

  // ---------------------------------------------------------------------------
  // Non-demo: PIN login page structure
  // ---------------------------------------------------------------------------

  if (!demo) {

    test.describe('laravel-user: pin login form', () => {

      test('login page has email input for PIN', async ({ page }) => {
        await page.goto('/login');
        await expect(page.locator('input[name="email"][type="email"]')).toBeVisible();
      });

      test('login page has remember me checkbox', async ({ page }) => {
        await page.goto('/login');
        await expect(page.locator('#remember_check')).toBeAttached();
      });

      test('login page has register checkbox', async ({ page }) => {
        await page.goto('/login');
        await expect(page.locator('#register_check')).toBeAttached();
      });

    });

  }

  // ---------------------------------------------------------------------------
  // Reusable test definitions for role access & user profile
  // (used in both demo and non-demo contexts)
  // ---------------------------------------------------------------------------

  function defineRoleAccessTests() {
    test('manager can access users list', async ({ page }) => {
      await loginAsManager(page);
      await page.goto('/users');
      await expect(page).toHaveURL(/\/users/);
      await expect(page.locator('.section-user-list')).toBeVisible();
    });

    test('customer cannot see users list', async ({ page }) => {
      await loginAsCustomer(page);
      await page.goto('/users');
      // Customer should NOT see the user-list section (403 error page is rendered instead)
      await expect(page.locator('.section-user-list')).not.toBeVisible();
    });
  }

  function defineProfileTests() {
    test('authenticated user is redirected away from login', async ({ page }) => {
      await loginAsManager(page);
      await page.goto('/login');
      await expect(page).not.toHaveURL(/\/login/);
    });

    test('user detail page loads from users list', async ({ page }) => {
      await loginAsManager(page);
      await page.goto('/users');
      await expect(page.locator('.section-user-list')).toBeVisible();
      const userLink = page.locator('.section-user-list a[href*="/users/"]').first();
      await userLink.click();
      await expect(page.locator('.section-user-detail')).toBeVisible();
    });

    test('user detail shows user information', async ({ page }) => {
      await loginAsManager(page);
      await page.goto('/users');
      const userLink = page.locator('.section-user-list a[href*="/users/"]').first();
      await userLink.click();
      await expect(page.locator('.section-user-detail')).toBeVisible();
      // Should have labeled fields (name, email, phone, role)
      await expect(page.locator('.section-user-detail dt').first()).toBeVisible();
    });

    test('manager can edit user name', async ({ page }) => {
      await loginAsManager(page);
      await page.goto('/users');
      const userLink = page.locator('.section-user-list a[href*="/users/"]').first();
      await userLink.click();
      await expect(page.locator('.section-user-detail')).toBeVisible();

      // Click edit button for name
      const nameEditBtn = page.locator('dt:has-text("Name") button, dt:has-text("Jméno") button').first();
      await nameEditBtn.click();

      // Fill new name and submit
      const nameInput = page.locator('input[name="name"]');
      await expect(nameInput).toBeVisible();
      const originalName = await nameInput.inputValue();
      const newName = originalName + ' Test';
      await nameInput.fill(newName);
      await nameInput.locator('xpath=..').locator('button[type="submit"]').click();

      // Success toast should appear
      await expect(page.locator('[data-testid="system-message-success"]')).toBeVisible();

      // Name should be updated on page
      await expect(page.locator('.section-user-detail')).toContainText(newName);

      // Restore original name
      const restoreBtn = page.locator('dt:has-text("Name") button, dt:has-text("Jméno") button').first();
      await restoreBtn.click();
      await page.locator('input[name="name"]').fill(originalName);
      await page.locator('input[name="name"]').locator('xpath=..').locator('button[type="submit"]').click();
    });
  }

  // ---------------------------------------------------------------------------
  // Non-demo + Mailpit: all email-dependent tests run serially
  // (queue may process jobs slowly — serial avoids overwhelming it)
  // ---------------------------------------------------------------------------

  if (!demo && mailpitUrl) {

    test.describe('laravel-user: email-dependent', () => {

      // set retries
      test.describe.configure({ mode: 'serial', retries: 10, retryDelay: 5000 });

      test.beforeAll(async () => {
        await fetch(`${mailpitUrl}/api/v1/messages`, { method: 'DELETE' });
      });

      // Clear rate limiter cache before each test to avoid throttle:5,10 on PIN verify route
      test.beforeEach(async () => {
        try { rmSync(join(process.cwd(), 'storage/framework/cache/data'), { recursive: true, force: true }); } catch {}
      });

      test.describe('pin login flow', () => {

        test('PIN send redirects to verify page', async ({ page }) => {
          const uniqueEmail = `redirect-test-${Date.now()}@example.com`;
          await page.goto('/login?register=true');
          await page.locator('input[name="email"]').fill(uniqueEmail);
          await page.locator('.section-login button[type="submit"], .section-login input[type="submit"]').first().click();

          await expect(page).toHaveURL(/\/pin-login\/verify/);
          await expect(page.locator('.section-pin-verify')).toBeVisible();
        });

        test('PIN verify page has PIN input boxes', async ({ page }) => {
          await page.goto('/pin-login/verify?email=test@example.com');
          await expect(page.locator('.pin-input')).toBeVisible();
          await expect(page.locator('.pin-input-box')).toHaveCount(6);
        });

        test('PIN verify page has resend button', async ({ page }) => {
          await page.goto('/pin-login/verify?email=test@example.com');
          await expect(page.locator('button:has-text("resend"), button:has-text("Resend"), button:has-text("znovu"), button:has-text("Znovu")').first()).toBeVisible();
        });

        test('full PIN login and logout', async ({ page }) => {
          const testEmail = 'george@internetguru.io';
          const before = await mailpitSnapshot();

          // Send PIN
          await page.goto('/login');
          await page.locator('input[name="email"]').fill(testEmail);
          await page.locator('.section-login button[type="submit"], .section-login input[type="submit"]').first().click();
          await expect(page).toHaveURL(/\/pin-login\/verify/);

          // Retrieve PIN from Mailpit
          const msg = await mailpitGetMessage(testEmail, before);
          const pin = extractPin(msg.Text || msg.HTML);

          // Enter PIN
          await fillPinInput(page, pin);
          await page.locator('.section-pin-verify button[type="submit"], .section-pin-verify input[type="submit"]').first().click();

          // Should be logged in (redirected away from login/verify)
          await expect(page).not.toHaveURL(/\/login|\/pin-login/);

          // Authenticated user should be redirected away from login (guest middleware)
          await page.goto('/login');
          await expect(page).not.toHaveURL(/\/login/);

          // Logout
          await page.goto('/logout');
          await expect(page).toHaveURL('/');
        });

        test('invalid PIN shows error', async ({ page }) => {
          await page.goto('/pin-login/verify?email=george@internetguru.io');
          await fillPinInput(page, '000000');
          await page.locator('.section-pin-verify button[type="submit"], .section-pin-verify input[type="submit"]').first().click();

          // Should stay on verify page with error
          await expect(page).toHaveURL(/\/pin-login\/verify/);
          await expect(page.locator('[data-testid="system-message-danger"]')).toBeVisible();
        });

        test('non-existent email without register shows error', async ({ page }) => {
          await page.goto('/login');
          await page.locator('input[name="email"]').fill('nonexistent@example.com');
          await page.locator('.section-login button[type="submit"], .section-login input[type="submit"]').first().click();

          // Should redirect back to login with error
          await expect(page.locator('[data-testid="system-message-danger"]')).toBeVisible();
        });

        test('PIN login with register creates account', async ({ page }) => {
          const newEmail = `testuser-${Date.now()}@example.com`;
          const before = await mailpitSnapshot();

          // Enable register checkbox
          await page.goto('/login?register=true');
          await page.locator('input[name="email"]').fill(newEmail);
          await page.locator('.section-login button[type="submit"], .section-login input[type="submit"]').first().click();
          await expect(page).toHaveURL(/\/pin-login\/verify/);

          // Retrieve PIN from Mailpit and complete login
          const msg = await mailpitGetMessage(newEmail, before);
          const pin = extractPin(msg.Text || msg.HTML);
          await fillPinInput(page, pin);
          await page.locator('.section-pin-verify button[type="submit"], .section-pin-verify input[type="submit"]').first().click();

          // Should be logged in
          await expect(page).not.toHaveURL(/\/login|\/pin-login/);
        });

      });

      test.describe('register', () => {

        test('/register redirects to /login', async ({ page }) => {
          await page.goto('/register');
          await expect(page).toHaveURL(/\/login/);
        });

        test('register checkbox enables account creation', async ({ page }) => {
          const newEmail = `register-${Date.now()}@example.com`;

          // Without register checkbox — should show error for unknown email
          await page.goto('/login');
          await page.locator('input[name="email"]').fill(newEmail);
          await page.locator('.section-login button[type="submit"], .section-login input[type="submit"]').first().click();
          await expect(page.locator('[data-testid="system-message-danger"]')).toBeVisible();

          // With register checkbox — should send PIN and redirect to verify
          await page.goto('/login?register=true');
          await page.locator('input[name="email"]').fill(newEmail);
          await page.locator('.section-login button[type="submit"], .section-login input[type="submit"]').first().click();
          await expect(page).toHaveURL(/\/pin-login\/verify/);
        });

        test('newly registered user can log in', async ({ page }) => {
          const newEmail = `newuser-${Date.now()}@example.com`;
          const before = await mailpitSnapshot();

          // Register via PIN
          await page.goto('/login?register=true');
          await page.locator('input[name="email"]').fill(newEmail);
          await page.locator('.section-login button[type="submit"], .section-login input[type="submit"]').first().click();
          await expect(page).toHaveURL(/\/pin-login\/verify/);

          // Complete PIN verification
          const msg = await mailpitGetMessage(newEmail, before);
          const pin = extractPin(msg.Text || msg.HTML);
          await fillPinInput(page, pin);
          await page.locator('.section-pin-verify button[type="submit"], .section-pin-verify input[type="submit"]').first().click();
          await expect(page).not.toHaveURL(/\/login|\/pin-login/);

          // Logout
          await page.goto('/logout');
          await expect(page).toHaveURL('/');

          // Login again with the same email (no register needed now)
          const before2 = await mailpitSnapshot();
          await page.goto('/login');
          await page.locator('input[name="email"]').fill(newEmail);
          await page.locator('.section-login button[type="submit"], .section-login input[type="submit"]').first().click();
          await expect(page).toHaveURL(/\/pin-login\/verify/);

          const msg2 = await mailpitGetMessage(newEmail, before2);
          const pin2 = extractPin(msg2.Text || msg2.HTML);
          await fillPinInput(page, pin2);
          await page.locator('.section-pin-verify button[type="submit"], .section-pin-verify input[type="submit"]').first().click();
          await expect(page).not.toHaveURL(/\/login|\/pin-login/);
        });

        test('PIN email contains verification link and PIN code', async ({ page }) => {
          const newEmail = `emailcheck-${Date.now()}@example.com`;
          const before = await mailpitSnapshot();

          await page.goto('/login?register=true');
          await page.locator('input[name="email"]').fill(newEmail);
          await page.locator('.section-login button[type="submit"], .section-login input[type="submit"]').first().click();
          await expect(page).toHaveURL(/\/pin-login\/verify/);

          const msg = await mailpitGetMessage(newEmail, before);
          const body = msg.Text || msg.HTML;

          // Email should contain a formatted PIN (IG-XXXXXX)
          expect(body).toMatch(/IG-\d{6}/);

          // Email should contain the verify URL
          expect(body).toMatch(/\/pin-login\/verify/);
        });

      });

      test.describe('role access', () => {
        defineRoleAccessTests();
      });

      test.describe('user profile', () => {
        defineProfileTests();
      });

    });

  }

  // ---------------------------------------------------------------------------
  // Demo-specific tests
  // ---------------------------------------------------------------------------

  if (demo) {

    test.describe('laravel-user: demo login', () => {

      test('demo login page shows user select', async ({ page }) => {
        await page.goto('/login');
        await expect(page.locator('select[name="email"]')).toBeVisible();
      });

      test('demo login and logout', async ({ page }) => {
        await demoLogin(page);
        await page.goto('/logout');
        await expect(page).toHaveURL('/');
      });

      test('demo login session persists across navigation', async ({ page }) => {
        await demoLogin(page, -1);
        await page.goto('/users');
        await expect(page).not.toHaveURL(/\/login/);
      });

    });

    test.describe('laravel-user: role access', () => {
      defineRoleAccessTests();
    });

    test.describe('laravel-user: user profile', () => {
      defineProfileTests();
    });

  }
}
