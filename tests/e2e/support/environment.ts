export const playwrightBaseUrl = process.env.PLAYWRIGHT_BASE_URL ?? 'http://127.0.0.1:8010';

const parsedBaseUrl = new URL(playwrightBaseUrl);

export const playwrightServeHost = parsedBaseUrl.hostname;
export const playwrightServePort = parsedBaseUrl.port || (parsedBaseUrl.protocol === 'https:' ? '443' : '80');
export const playwrightDatabasePath = `${process.cwd()}/database/playwright.sqlite`;
export const playwrightServerEnv: NodeJS.ProcessEnv = {
    ...process.env,
    APP_ENV: 'testing',
    APP_URL: playwrightBaseUrl,
    CACHE_STORE: 'array',
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: playwrightDatabasePath,
    MAIL_MAILER: 'log',
    QUEUE_CONNECTION: 'sync',
    SESSION_DRIVER: 'file',
};
