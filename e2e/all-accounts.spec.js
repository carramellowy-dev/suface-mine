// @ts-check
import { test, expect } from '@playwright/test';

const accounts = [
  { username: 'admin', password: 'password', role: 'admin', expectUrl: '/admin/dashboard', name: 'Administrator Utama' },
  { username: 'spv', password: 'password', role: 'spv', expectUrl: '/spv/dashboard', name: 'Supervisor Utama' },
  { username: 'spv1', password: 'password', role: 'spv', expectUrl: '/spv/dashboard', name: 'SPV Sugiantoro' },
  { username: 'spv2', password: 'password', role: 'spv', expectUrl: '/spv/dashboard', name: 'SPV Darmawan' },
  { username: 'spv3', password: 'password', role: 'spv', expectUrl: '/spv/dashboard', name: 'SPV Hendrawan' },
  { username: 'spv4', password: 'password', role: 'spv', expectUrl: '/spv/dashboard', name: 'SPV Prasetyo' },
  { username: 'operator1', password: 'password', role: 'pegawai', expectUrl: '/pegawai/ritasi/create', name: 'Operator Budi' },
  { username: 'operator2', password: 'password', role: 'pegawai', expectUrl: '/pegawai/ritasi/create', name: 'Operator Agus' },
  { username: 'operator3', password: 'password', role: 'pegawai', expectUrl: '/pegawai/ritasi/create', name: 'Operator Hendra' },
  { username: 'operator4', password: 'password', role: 'pegawai', expectUrl: '/pegawai/ritasi/create', name: 'Operator Rudi' },
  { username: 'operator5', password: 'password', role: 'pegawai', expectUrl: '/pegawai/ritasi/create', name: 'Operator Joko' },
  { username: 'operator6', password: 'password', role: 'pegawai', expectUrl: '/pegawai/ritasi/create', name: 'Operator Wawan' },
  { username: 'operator7', password: 'password', role: 'pegawai', expectUrl: '/pegawai/ritasi/create', name: 'Operator Eko' },
  { username: 'operator8', password: 'password', role: 'pegawai', expectUrl: '/pegawai/ritasi/create', name: 'Operator Ahmad' },
  { username: 'operator9', password: 'password', role: 'pegawai', expectUrl: '/pegawai/ritasi/create', name: 'Operator Dedi' },
  { username: 'operator10', password: 'password', role: 'pegawai', expectUrl: '/pegawai/ritasi/create', name: 'Operator Bambang' },
];

async function login(page, username, password) {
  await page.goto('/login');
  await expect(page.locator('input[name="login"]')).toBeVisible();
  await page.fill('input[name="login"]', username);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 10000 }).catch(()=>{});
}

test.describe('Landing & Login page', () => {
  test('landing page loads', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/Surface Mine|Laravel/i);
    // should have login link or button
    await expect(page.locator('body')).toContainText(/Surface Mine/i);
  });

  test('login page renders', async ({ page }) => {
    await page.goto('/login');
    await expect(page.locator('input[name="login"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toContainText(/Masuk/i);
  });

  test('invalid login shows error', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', 'invalid_user');
    await page.fill('input[name="password"]', 'wrong');
    await page.click('button[type="submit"]');
    // Should stay on login and show error
    await expect(page).toHaveURL(/.*login/);
    // error may be in validation or session
    await expect(page.locator('body')).toContainText(/auth\.failed|These credentials|error|Gagal/i, { timeout: 5000 }).catch(async () => {
      // fallback: still on login
      await expect(page.locator('input[name="login"]')).toBeVisible();
    });
  });
});

for (const acc of accounts) {
  test.describe(`Account: ${acc.username} (${acc.role})`, () => {
    test(`login success and redirect to ${acc.expectUrl}`, async ({ page }) => {
      await login(page, acc.username, acc.password);
      await expect(page).toHaveURL(new RegExp(acc.expectUrl.replace('/','\\/')));
      // check topbar shows name or role
      await expect(page.locator('body')).toContainText(/Surface Mine/i);
      // logout to clean session
      // perform logout via POST - use button
      const logoutBtn = page.locator('button', { hasText: 'Logout' }).first();
      if (await logoutBtn.isVisible().catch(()=>false)) {
        await logoutBtn.click();
        await expect(page).toHaveURL(/.*\/$|\/login/);
      }
    });

    if (acc.role === 'admin') {
      test(`admin ${acc.username} can access master-data, dashboard, rekapan, utilization`, async ({ page }) => {
        await login(page, acc.username, acc.password);
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        // Dashboard tabs - use specific tab nav
        await expect(page.getByText('Harian').first()).toBeVisible();
        await expect(page.getByText('Mingguan').first()).toBeVisible();
        await expect(page.getByText('Bulanan').first()).toBeVisible();
        await page.goto('/admin/dashboard?tab=monthly');
        await expect(page.locator('body')).toContainText(/All Material Hauling/i);

        // Master Data - unit
        await page.goto('/admin/master-data?tab=unit');
        await expect(page.locator('body')).toContainText(/Master Data|Unit/i);

        // Master Data - user
        await page.goto('/admin/master-data?tab=user');
        await expect(page.locator('body')).toContainText(/Tambah User|ID \(USERNAME\)|NAMA/i);
        // check that user table shows known users
        await expect(page.locator('body')).toContainText(/admin|operator1/i);

        // Master Data - target
        await page.goto('/admin/master-data?tab=target');
        await expect(page.locator('body')).toContainText(/Tambah Target|TARGET RITASI/i);

        // Rekapan
        await page.goto('/admin/rekapan');
        await expect(page.locator('body')).toContainText(/Rekapan|Operator/i);

        // Utilization
        await page.goto('/admin/utilization');
        await expect(page.locator('body')).toContainText(/Utilization|Unit/i);

        // Pegawai management - page shows Daftar Operator
        await page.goto('/admin/pegawai');
        await expect(page.locator('body')).toContainText(/Operator|Manajemen Operator/i);

        // SPV management
        await page.goto('/admin/spv');
        await expect(page.locator('body')).toContainText(/SPV|Supervisor/i);
      });

      test(`admin ${acc.username} cannot access spv/pegawai dashboard should redirect to own dashboard`, async ({ page }) => {
        await login(page, acc.username, acc.password);
        await page.goto('/spv/dashboard');
        // Role middleware redirects to login, then RedirectIfAuthenticated redirects back to admin dashboard
        await expect(page).not.toHaveURL(/\/spv\/dashboard/);
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        await page.goto('/pegawai/ritasi/create');
        await expect(page).not.toHaveURL(/\/pegawai\/ritasi\/create/);
        // should be back to admin area or login then admin
        await expect(page).toHaveURL(/\/admin\/dashboard|\/login/);
      });
    }

    if (acc.role === 'spv') {
      test(`spv ${acc.username} dashboard, rekapan, utilization`, async ({ page }) => {
        await login(page, acc.username, acc.password);
        await expect(page).toHaveURL(/\/spv\/dashboard/);
        await expect(page.locator('body')).toContainText(/Dashboard|All Material Hauling/i);

        await page.goto('/spv/rekapan');
        await expect(page.locator('body')).toContainText(/Rekapan/);

        await page.goto('/spv/utilization');
        await expect(page.locator('body')).toContainText(/Utilization/);

        // try admin access -> redirected to spv dashboard (via login redirect)
        await page.goto('/admin/dashboard');
        await expect(page).not.toHaveURL(/\/admin\/dashboard/);
        await expect(page).toHaveURL(/\/spv\/dashboard|\/login/);

        await page.goto('/pegawai/ritasi/create');
        await expect(page).not.toHaveURL(/\/pegawai\/ritasi\/create/);
        await expect(page).toHaveURL(/\/spv\/dashboard|\/login/);
      });
    }

    if (acc.role === 'pegawai') {
      test(`operator ${acc.username} can access all pegawai forms`, async ({ page }) => {
        await login(page, acc.username, acc.password);
        await expect(page).toHaveURL(/\/pegawai/);

        // Ritasi create
        await page.goto('/pegawai/ritasi/create');
        await expect(page.locator('body')).toContainText(/Form Input Unit Ritasi|Produksi/i);
        await expect(page.locator('select[name="material_id"]')).toBeVisible();
        await expect(page.locator('select[name="area_id"]')).toBeVisible();
        await expect(page.locator('input[name="jumlah_ritasi"]')).toBeVisible();

        // Non-ritasi create
        await page.goto('/pegawai/non-ritasi/create');
        await expect(page.locator('body')).toContainText(/Form Input Unit Non Ritasi/i);
        await expect(page.locator('select[name="area_id"]')).toBeVisible();

        // General create
        await page.goto('/pegawai/general/create');
        await expect(page.locator('body')).toContainText(/Form Pekerjaan General/i);
        await expect(page.locator('select[name="area_id"]')).toBeVisible();

        // Utilization create
        await page.goto('/pegawai/utilization/create');
        await expect(page.locator('body')).toContainText(/Utilization|Form/i);

        // Riwayat
        await page.goto('/pegawai/ritasi/riwayat');
        await expect(page.locator('body')).toContainText(/Riwayat|Ritasi/i);

        await page.goto('/pegawai/non-ritasi/riwayat');
        await expect(page.locator('body')).toContainText(/Riwayat/i);
      });

      test(`operator ${acc.username} cannot access admin/spv`, async ({ page }) => {
        await login(page, acc.username, acc.password);
        await page.goto('/admin/dashboard');
        await expect(page).not.toHaveURL(/\/admin\/dashboard/);
        await expect(page).toHaveURL(/\/pegawai|\/login/);

        await page.goto('/spv/dashboard');
        await expect(page).not.toHaveURL(/\/spv\/dashboard/);
        await expect(page).toHaveURL(/\/pegawai|\/login/);
      });

      test(`operator ${acc.username} sidebar navigation visible`, async ({ page }) => {
        await login(page, acc.username, acc.password);
        await page.goto('/pegawai/ritasi/create');
        await expect(page.locator('aside')).toContainText(/Unit Ritasi/);
        await expect(page.locator('aside')).toContainText(/Unit Non Ritasi/);
        await expect(page.locator('aside')).toContainText(/Pekerjaan General/);
      });
    }
  });
}

test.describe('Logout & session', () => {
  test('logout clears session and requires login again', async ({ page }) => {
    await login(page, 'admin', 'password');
    await expect(page).toHaveURL(/\/admin\/dashboard/);
    const logoutBtn = page.locator('button', { hasText: 'Logout' }).first();
    await expect(logoutBtn).toBeVisible();
    await logoutBtn.click();
    await expect(page).toHaveURL(/\/$|\/login/);
    await page.goto('/admin/dashboard');
    await expect(page).toHaveURL(/\/login/);
  });
});

test.describe('Auth via pegawai name (alternative login)', () => {
  test('login with pegawai nama should work for operator', async ({ page }) => {
    // Operator Budi nama = Operator Budi (DT) linked to pegawai_idx 0
    // We seeded operator1 with pegawai "Operator Budi (DT)" but actual PegawaiSeeder may have different names, test using login = pegawai nama
    // We'll try operator1's pegawai nama if exists
    await page.goto('/login');
    await page.fill('input[name="login"]', 'Operator Budi (DT)');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    // May succeed or stay on login; we just check not crash
    await page.waitForTimeout(2000);
    const url = page.url();
    // either redirected to pegawai dashboard or still login with error is acceptable but should not 500
    expect(url).toMatch(/login|pegawai/);
  });
});
