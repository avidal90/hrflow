import { execSync } from 'node:child_process';
import { existsSync, mkdirSync, writeFileSync } from 'node:fs';
import path from 'node:path';

import {
    playwrightDatabasePath,
    playwrightServerEnv,
} from './support/environment';

export default async function globalSetup(): Promise<void> {
    const databaseDirectory = path.dirname(playwrightDatabasePath);

    if (!existsSync(databaseDirectory)) {
        mkdirSync(databaseDirectory, { recursive: true });
    }

    if (!existsSync(playwrightDatabasePath)) {
        writeFileSync(playwrightDatabasePath, '');
    }

    execSync('php artisan migrate:fresh --seed --no-interaction', {
        cwd: process.cwd(),
        env: playwrightServerEnv,
        stdio: 'inherit',
    });
}
