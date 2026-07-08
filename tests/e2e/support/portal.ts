import { expect, Page } from '@playwright/test';

export const portalTenant = 'northwind-demo';

const portalEmployeeCredentials = {
    email: 'javier.ramos@northwind.local',
    password: 'Hr@Flow2026!',
};

export function portalNavigation(page: Page) {
    return page.getByRole('navigation');
}

export async function loginToPortal(page: Page): Promise<void> {
    await page.goto(`/portal/${portalTenant}/login`);

    await expect(page).toHaveURL(new RegExp(`/portal/${portalTenant}/login$`));

    await page.getByLabel('Email').fill(portalEmployeeCredentials.email);
    await page.getByLabel('Contraseña').fill(portalEmployeeCredentials.password);
    await page.getByRole('button', { name: 'Entrar' }).click();

    await expect(page).toHaveURL(new RegExp(`/portal/${portalTenant}/dashboard$`));
    await expect(portalNavigation(page).getByRole('link', { name: 'Solicitudes', exact: true })).toBeVisible();
}
