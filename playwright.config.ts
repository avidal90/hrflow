import { defineConfig, devices } from '@playwright/test';

import {
    playwrightBaseUrl,
    playwrightServerEnv,
    playwrightServeHost,
    playwrightServePort,
} from './tests/e2e/support/environment';

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false,
    globalSetup: './tests/e2e/global.setup.ts',
    retries: process.env.CI ? 1 : 0,
    reporter: [['list'], ['html']],
    timeout: 30_000,
    use: {
        baseURL: playwrightBaseUrl,
        screenshot: 'only-on-failure',
        trace: 'on-first-retry',
        video: 'off',
    },
    webServer: {
        command: `php artisan serve --host=${playwrightServeHost} --port=${playwrightServePort}`,
        env: playwrightServerEnv,
        reuseExistingServer: !process.env.CI,
        stderr: 'pipe',
        stdout: 'pipe',
        timeout: 120_000,
        url: playwrightBaseUrl,
    },
    workers: 1,
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
});
