import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright configuration for E2E testing
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: './tests/E2E',

  // Maximum time one test can run for
  timeout: 30 * 1000,

  expect: {
    // Maximum time expect() should wait for the condition to be met
    timeout: 5000
  },

  // Run tests in files in parallel
  fullyParallel: false,

  // Fail the build on CI if you accidentally left test.only in the source code
  forbidOnly: !!process.env.CI,

  // Retry on CI only
  retries: process.env.CI ? 2 : 0,

  // Run tests sequentially (1 worker)
  workers: 1,

  // Reporter to use
  reporter: 'html',

  // Shared settings for all the projects below
  use: {
    // Base URL to use in actions like `await page.goto('/')`
    baseURL: 'http://localhost:8001',

    // Collect trace when retrying the failed test
    trace: 'on-first-retry',

    // Screenshot only on failure
    screenshot: 'only-on-failure',

    // Slow down operations by 500ms (only in headed mode)
    launchOptions: {
      slowMo: process.env.HEADED ? 500 : 0,
    },
  },

  // Configure projects for major browsers
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },

    // Uncomment to test on Firefox
    // {
    //   name: 'firefox',
    //   use: { ...devices['Desktop Firefox'] },
    // },

    // Uncomment to test on WebKit
    // {
    //   name: 'webkit',
    //   use: { ...devices['Desktop Safari'] },
    // },
  ],

  // Run your local dev server before starting the tests
  // Uncomment if you want Playwright to start the server automatically
  // webServer: {
  //   command: 'docker-compose up',
  //   url: 'http://localhost:8001',
  //   reuseExistingServer: !process.env.CI,
  // },
});
