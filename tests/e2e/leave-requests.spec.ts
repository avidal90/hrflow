import { expect, test } from '@playwright/test';

import { loginToPortal, portalNavigation } from './support/portal';

function isoDate(daysFromToday: number): string {
    const date = new Date();
    date.setDate(date.getDate() + daysFromToday);

    return date.toISOString().slice(0, 10);
}

test('el empleado puede crear una solicitud de vacaciones desde el portal', async ({ page }) => {
    await loginToPortal(page);

    await portalNavigation(page).getByRole('link', { name: 'Solicitudes', exact: true }).click();

    await expect(page).toHaveURL(/\/portal\/northwind-demo\/solicitudes$/);
    await expect(page.getByRole('heading', { level: 1, name: 'Mis solicitudes' })).toBeVisible();

    const requestForm = page.locator('form').filter({
        has: page.getByRole('button', { name: 'Enviar solicitud' }),
    }).first();

    await requestForm.locator('select').selectOption('vacation');
    await requestForm.locator('input[type="date"]').nth(0).fill(isoDate(30));
    await requestForm.locator('input[type="date"]').nth(1).fill(isoDate(32));
    await requestForm.locator('textarea').fill('Prueba E2E vacaciones');
    await requestForm.getByRole('button', { name: 'Enviar solicitud' }).click();

    await expect(page.getByText('Solicitud enviada correctamente')).toBeVisible();
    await expect(page.locator('tbody').getByText('Pendiente').first()).toBeVisible();
    await expect(page.locator('tbody').getByText('Prueba E2E vacaciones')).toBeVisible();
});
