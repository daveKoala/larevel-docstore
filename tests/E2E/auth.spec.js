import { test, expect } from '@playwright/test';

test.describe('Authentication', () => {
  test('should display login page', async ({ page }) => {
    await page.goto('/login');

    await expect(page.locator('h2')).toContainText('Order Management System');
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('should login as AcMe user', async ({ page }) => {
    await page.goto('/login');

    // Fill in login form
    await page.fill('input[name="email"]', 'wile@acme.com');
    await page.fill('input[name="password"]', 'password');

    // Submit form
    await page.click('button[type="submit"]');

    // Wait for navigation to dashboard
    await page.waitForURL('/dashboard');

    // Verify we're on the dashboard
    await expect(page).toHaveURL('/dashboard');
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
  });

  test('should login as Beta user', async ({ page }) => {
    await page.goto('/login');

    // Fill in login form
    await page.fill('input[name="email"]', 'admin@beta.com');
    await page.fill('input[name="password"]', 'password');

    // Submit form
    await page.click('button[type="submit"]');

    // Wait for navigation to dashboard
    await page.waitForURL('/dashboard');

    // Verify we're on the dashboard
    await expect(page).toHaveURL('/dashboard');
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
  });

  test('should show error with invalid credentials', async ({ page }) => {
    await page.goto('/login');

    // Fill in login form with invalid credentials
    await page.fill('input[name="email"]', 'invalid@example.com');
    await page.fill('input[name="password"]', 'wrongpassword');

    // Submit form
    await page.click('button[type="submit"]');

    // Should stay on login page or show error
    await page.waitForTimeout(1000);

    // Check for error message or still on login page
    const hasError = await page.locator('text=/error|invalid|incorrect/i').count() > 0;
    const onLoginPage = page.url().includes('/login');

    expect(hasError || onLoginPage).toBeTruthy();
  });

  test('should logout successfully', async ({ page }) => {
    // Login first
    await page.goto('/login');
    await page.fill('input[name="email"]', 'wile@acme.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('/dashboard');

    // Find and click logout button (try form submission)
    await page.locator('form[action*="logout"] button[type="submit"]').click();

    // Wait for redirect to login
    await page.waitForURL('/login');

    // Verify we're back on login page
    await expect(page).toHaveURL('/login');
  });
});
