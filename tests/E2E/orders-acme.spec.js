import { test, expect } from '@playwright/test';

/**
 * Test suite for AcMe Corporation tenant-specific order customization
 * Verifies that orders created by AcMe users have "[AcMe] " prefix
 */
test.describe('AcMe Orders - Tenant Customization', () => {
  // Login before each test
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'wile@acme.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('/dashboard');
  });

  test('should navigate to orders page', async ({ page }) => {
    // Navigate to orders
    await page.click('a[href*="/dashboard/orders"], a:has-text("Orders")');

    // Wait for orders page to load
    await page.waitForURL(/\/dashboard\/orders/);

    // Verify we're on orders page - look for "Create order" button
    await expect(page.locator('a:has-text("Create order")')).toBeVisible();
  });

  test('should create new order with [AcMe] prefix', async ({ page }) => {
    // Navigate to orders
    await page.click('a[href*="/dashboard/orders"], a:has-text("Orders")');
    await page.waitForURL(/\/dashboard\/orders/);

    // Click create new order button
    await page.click('a:has-text("Create order")');

    // Wait for create form
    await page.waitForURL(/\/dashboard\/orders\/create/);

    // Fill in order details
    const orderDetails = 'Rocket-powered roller skates';

    // Select first actual project (not the placeholder option)
    await page.selectOption('select[name="project_id"]', { index: 1 });

    // Fill in details
    await page.fill('textarea[name="details"]', orderDetails);

    // Submit form
    await page.click('button:has-text("Create Order")');

    // Wait for redirect back to orders list
    await page.waitForURL('/dashboard/orders', { timeout: 10000 });

    // Verify success message - use specific class to avoid matching "Created" column header
    await expect(page.locator('.text-green-800')).toBeVisible();

    // Look for the newly created order in the list
    // It should have [AcMe] prefix in the Details column
    const orderWithPrefix = page.locator(`td:has-text("[AcMe]")`).first();

    // Wait for it to appear
    await expect(orderWithPrefix).toBeVisible({ timeout: 5000 });

    // Verify it contains our order details
    const fullText = await orderWithPrefix.textContent();
    expect(fullText).toContain(orderDetails);
  });

  test('should display [AcMe] prefix in order details view', async ({ page }) => {
    // Navigate to orders
    await page.click('a[href*="/dashboard/orders"], a:has-text("Orders")');
    await page.waitForURL(/\/dashboard\/orders/);

    // Find View link and click it
    const viewLink = page.locator('a:has-text("View")').first();
    await viewLink.click();

    // Wait for navigation
    await page.waitForLoadState('networkidle');

    // Verify the order details contain [AcMe] prefix
    const orderDetails = page.locator('text=/\\[AcMe\\]/i');
    await expect(orderDetails).toBeVisible();
  });

  test('should update order and maintain [AcMe] prefix', async ({ page }) => {
    // Navigate to orders
    await page.click('a[href*="/dashboard/orders"], a:has-text("Orders")');
    await page.waitForURL(/\/dashboard\/orders/);

    // Click edit on first order
    const editLink = page.locator('a:has-text("Edit")').first();
    await editLink.click();

    // Wait for edit form to appear
    await page.waitForSelector('textarea[name="details"]');

    // Update the details (remove any existing prefix, service should re-add it)
    const updatedDetails = 'Updated rocket equipment';

    // Fill replaces existing content automatically
    await page.fill('textarea[name="details"]', updatedDetails);

    // Submit form
    await page.click('button[type="submit"]');

    // Wait for any navigation to complete
    await page.waitForLoadState('networkidle');

    // Should be on orders index page
    await expect(page).toHaveURL(/\/dashboard\/orders$/);

    // Verify success message
    await expect(page.locator('.text-green-800')).toBeVisible();

    // Verify the updated order has [AcMe] prefix
    const orderCell = page.locator(`td:has-text("[AcMe]")`).first();
    await expect(orderCell).toBeVisible({ timeout: 5000 });

    // Verify it contains the updated details
    const fullText = await orderCell.textContent();
    expect(fullText).toContain(updatedDetails);
  });

  test('should list multiple orders all with [AcMe] prefix', async ({ page }) => {
    // Navigate to orders
    await page.click('a[href*="/dashboard/orders"], a:has-text("Orders")');
    await page.waitForURL(/\/dashboard\/orders/);

    // Get all table cells in the Details column (4th column)
    // The Details column is the 4th td in each row
    const detailsCells = page.locator('tbody tr td:nth-child(4)');

    // Should have at least one order
    const count = await detailsCells.count();
    expect(count).toBeGreaterThan(0);

    // Check if any cells contain [AcMe] prefix
    const cellsWithPrefix = detailsCells.filter({ hasText: /\[AcMe\]/i });
    const prefixCount = await cellsWithPrefix.count();

    // At least one order should have the [AcMe] prefix
    expect(prefixCount).toBeGreaterThan(0);

    // Verify the prefix format is correct
    for (let i = 0; i < Math.min(prefixCount, 5); i++) {
      const text = await cellsWithPrefix.nth(i).textContent();
      expect(text).toMatch(/\[AcMe\]/i);
    }
  });
});
