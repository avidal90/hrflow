import { expect, test } from '@playwright/test';

import { loginToPortal, portalNavigation } from './support/portal';

test('el empleado puede iniciar y finalizar su jornada desde el portal', async ({ page }) => {
    await loginToPortal(page);

    await portalNavigation(page).getByRole('link', { name: 'Horario', exact: true }).click();

    await expect(page).toHaveURL(/\/portal\/northwind-demo\/control-horario$/);
    await expect(page.getByRole('heading', { level: 1, name: 'Registro de jornada' })).toBeVisible();

    await page.getByRole('button', { name: 'Iniciar jornada' }).click();

    await expect(page.getByText('Jornada en curso')).toBeVisible();
    const stopTrackingButton = page.getByRole('button', { name: 'Finalizar jornada' });
    const activeTimer = page.locator('[wire\\:key="tracker-active"] [x-text="formatted"]');

    await expect(stopTrackingButton).toBeVisible();
    await expect(activeTimer).not.toHaveText('00:00:00');

    await Promise.all([
        page.waitForResponse((response) => {
            const request = response.request();

            return response.url().includes('/livewire/update')
                && request.method() === 'POST'
                && (request.postData() ?? '').includes('stopTracking');
        }),
        stopTrackingButton.click(),
    ]);

    await page.reload();

    await expect(page.getByText('Sin jornada activa')).toBeVisible();
    await expect(page.locator('tbody').getByText('Completo').first()).toBeVisible();
});
