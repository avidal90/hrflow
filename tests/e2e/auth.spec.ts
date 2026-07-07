import { expect, test } from '@playwright/test';

import { loginToPortal, portalNavigation } from './support/portal';

test('el empleado puede iniciar sesion en el portal y llegar al dashboard', async ({ page }) => {
    await loginToPortal(page);

    await expect(page).toHaveURL(/\/portal\/northwind-demo\/dashboard$/);
    await expect(page.getByRole('heading', { level: 1, name: /Hola, Javier/i })).toBeVisible();
    await expect(portalNavigation(page).getByRole('link', { name: 'Horario', exact: true })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Salir' })).toBeVisible();
});
