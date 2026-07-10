import { expect, Locator, test } from '@playwright/test';

import { loginToPortal, portalNavigation } from './support/portal';

const vacationRequest = {
    endDate: '2026-09-03',
    reason: 'Prueba E2E vacaciones',
    startDate: '2026-09-01',
};

async function setDateInputValue(input: Locator, value: string): Promise<void> {
    await input.evaluate((element, nextValue) => {
        const dateInput = element as HTMLInputElement;

        dateInput.value = nextValue;
        dateInput.dispatchEvent(new Event('input', { bubbles: true }));
        dateInput.dispatchEvent(new Event('change', { bubbles: true }));
    }, value);
}


test('el empleado puede crear una solicitud de vacaciones desde el portal', async ({ page }) => {
    await loginToPortal(page);

    await portalNavigation(page).getByRole('link', { name: 'Solicitudes', exact: true }).click();

    await expect(page).toHaveURL(/\/portal\/northwind-demo\/solicitudes$/);
    await expect(page.getByRole('heading', { level: 1, name: 'Mis solicitudes' })).toBeVisible();

    const requestForm = page.getByRole('button', { name: 'Enviar solicitud' }).locator('..');
    const requestTypeSelect = page.locator('select').first();
    const dateInputs = page.getByRole('textbox');
    const startDateInput = dateInputs.nth(0);
    const endDateInput = dateInputs.nth(1);
    const reasonInput = dateInputs.nth(2);

    await requestTypeSelect.selectOption('vacation');
    await expect(requestTypeSelect).toHaveValue('vacation');
    await expect(startDateInput).toBeVisible();
    await setDateInputValue(startDateInput, vacationRequest.startDate);
    await expect(startDateInput).toHaveValue(vacationRequest.startDate);
    await setDateInputValue(endDateInput, vacationRequest.endDate);
    await expect(endDateInput).toHaveValue(vacationRequest.endDate);
    await reasonInput.fill(vacationRequest.reason);
    await requestTypeSelect.selectOption('vacation');
    await expect(requestTypeSelect).toHaveValue('vacation');
    await expect(page.getByRole('button', { name: 'Enviar solicitud' })).toBeEnabled();
    await page.getByRole('button', { name: 'Enviar solicitud' }).click();

    await expect(page.getByText('Solicitud enviada correctamente')).toBeVisible();
    await expect(page.locator('tbody').getByText('Pendiente').first()).toBeVisible();
    await expect(page.locator('tbody').getByText(vacationRequest.reason)).toBeVisible();
});
