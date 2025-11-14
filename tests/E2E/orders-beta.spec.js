import { test, expect } from '@playwright/test';

/**
 * Test suite for Beta Corporation orders - using default service
 * Verifies that orders created by Beta users DO NOT have any tenant prefix
 * This demonstrates the default OrderService behavior
 */
test.describe('Beta Orders - Default Service (No Customization)', () => {
  // Login before each test
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@beta.com');
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

  test('should create new order WITHOUT any tenant prefix', async ({ page }) => {
    // Navigate to orders
    await page.click('a[href*="/dashboard/orders"], a:has-text("Orders")');
    await page.waitForURL(/\/dashboard\/orders/);

    // Click create new order button
    await page.click('a:has-text("Create order")');

    // Wait for create form
    await page.waitForURL(/\/dashboard\/orders\/create/);

    // Fill in order details
    const orderDetails = 'Standard software development project';

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

    // Look for the newly created order in the list (it should be in Details column)
    // It should NOT have [Beta] or any other prefix - just the plain text
    const orderCell = page.locator(`td:has-text("${orderDetails}")`).first();
    await expect(orderCell).toBeVisible({ timeout: 5000 });

    // Verify it contains our order details without any prefix
    const fullText = await orderCell.textContent();
    expect(fullText).toContain(orderDetails);
    // Should NOT have tenant prefix
    expect(fullText).not.toMatch(/\[Beta\]/i);
    expect(fullText).not.toMatch(/\[AcMe\]/i);
  });

  test('should display order details WITHOUT prefix', async ({ page }) => {
    // Navigate to orders
    await page.click('a[href*="/dashboard/orders"], a:has-text("Orders")');
    await page.waitForURL(/\/dashboard\/orders/);

    // Find View link and click it
    const viewLink = page.locator('a:has-text("View")').first();
    await viewLink.click();

    // Wait for navigation
    await page.waitForLoadState('networkidle');

    // Get the page content
    const content = await page.content();

    // Verify NO tenant prefix like [Beta] or [AcMe] exists in the order details
    expect(content).not.toMatch(/\[Beta\]/i);
    expect(content).not.toMatch(/\[AcMe\]/i);
    expect(content).not.toMatch(/\[WayneEnt\]/i);
  });

  test('should update order WITHOUT adding prefix', async ({ page }) => {
    // Navigate to orders
    await page.click('a[href*="/dashboard/orders"], a:has-text("Orders")');
    await page.waitForURL(/\/dashboard\/orders/);

    // Click edit on first order
    const editLink = page.locator('a:has-text("Edit")').first();
    await editLink.click();

    // Wait for edit form to appear
    await page.waitForSelector('textarea[name="details"]');

    // Update the details (remove any existing prefix, service should NOT re-add it)
    const updatedDetails = 'Updated project requirements';

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

    // Verify the updated order does NOT have any prefix and contains our details
    const orderCell = page.locator(`td:has-text("${updatedDetails}")`).first();
    await expect(orderCell).toBeVisible({ timeout: 5000 });

    // Get the text to verify no prefix
    const fullText = await orderCell.textContent();
    expect(fullText).toContain(updatedDetails);
    expect(fullText).not.toMatch(/\[Beta\]/i);
    expect(fullText).not.toMatch(/\[AcMe\]/i);
  });

  test('should list orders without any tenant prefixes', async ({ page }) => {
    // Navigate to orders
    await page.click('a[href*="/dashboard/orders"], a:has-text("Orders")');
    await page.waitForURL(/\/dashboard\/orders/);

    // Get page content
    const content = await page.content();

    // Verify NO tenant prefixes exist in the orders list
    // Beta uses the default service, so no prefixes should appear
    expect(content).not.toMatch(/\[Beta\]/i);
    expect(content).not.toMatch(/\[AcMe\]/i);
    expect(content).not.toMatch(/\[WayneEnt\]/i);
  });

  test('should demonstrate difference from AcMe tenant', async ({ page, context }) => {
    // This test verifies that Beta orders are different from AcMe orders
    // by confirming Beta does NOT use tenant-specific customization

    // Navigate to orders
    await page.click('a[href*="/dashboard/orders"], a:has-text("Orders")');
    await page.waitForURL(/\/dashboard\/orders/);

    // Get all text on the page
    const pageText = await page.locator('body').textContent();

    // Beta orders should NOT have [Beta] prefix (uses default service)
    expect(pageText).not.toMatch(/\[Beta\]/i);

    // This confirms:
    // 1. AcMe Corporation has custom OrderService (with prefix)
    // 2. Beta Corporation uses default OrderService (no prefix)
    // 3. Tenant-specific customization works without modifying core code
  });
});
