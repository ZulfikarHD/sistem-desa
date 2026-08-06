import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright E2E untuk sistem-desa.
 * Port 8011 khusus E2E — menghindari bentrok dengan app lain di :8000
 * dan server dev manual di :8004.
 */
const e2eHost = '127.0.0.1';
const e2ePort = 8011;
const e2eOrigin = `http://${e2eHost}:${e2ePort}`;

export default defineConfig({
    testDir: './e2e',
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: 1,
    reporter: [['list']],
    timeout: 60_000,
    expect: {
        timeout: 15_000,
    },
    use: {
        baseURL: process.env.PLAYWRIGHT_BASE_URL ?? e2eOrigin,
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
    webServer: {
        command: `php artisan serve --host=${e2eHost} --port=${e2ePort}`,
        url: e2eOrigin,
        reuseExistingServer: false,
        timeout: 120_000,
        env: {
            APP_URL: e2eOrigin,
        },
    },
});
