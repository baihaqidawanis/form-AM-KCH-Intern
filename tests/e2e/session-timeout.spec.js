// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * E2E buat 2 fitur yang murni JavaScript-browser (gak bisa dites lewat
 * PHPUnit+Guzzle karena gak ada JS engine): idle session-timeout warning
 * (URS 1.3) dan draft form auto-save ke localStorage pas sesi berakhir
 * (Round 28). Diverifikasi manual pakai Chrome DevTools Protocol di Round 28
 * -- sekarang otomatis lewat Playwright.
 *
 * `SESSION_TIMEOUT_SECONDS` di-override lewat .env (lihat globalSetup/
 * globalTeardown di playwright.config.js kalau ada, atau dikerjain manual
 * sebelum/sesudah run -- lihat DOCS_MD/AUTOMATED_TESTING.md) supaya test ini
 * gak perlu nunggu 30 menit beneran.
 */
test.describe('Session timeout & draft auto-save', () => {
	test('idle timeout: warning muncul, draft ke-save, restore sukses', async ({ page }) => {
		// 1. Login
		await page.goto('./');
		await page.fill('input[name="username"]', 'superadmin');
		await page.fill('input[name="password"]', 'Admin@123');
		await page.click('button[type="submit"]');
		await expect(page).toHaveURL(/Home/i);

		// 2. Isi sebagian form (jangan submit) -- ini yang harus ke-draft.
		// NB: WARNING_MS di main_layout.php di-hardcode 5 menit (bukan proporsional
		// ke SESSION_TIMEOUT_SECONDS), jadi begitu kita pendekin timeout jadi ~10
		// detik, warningTimer delay-nya (IDLE_LIMIT_MS - WARNING_MS) jadi NEGATIF
		// -> browser treat sebagai 0ms -> modal peringatan nongol PRAKTIS LANGSUNG
		// pas halaman kebuka, nutupin form-nya (overlay full-screen). Makanya isi
		// form pakai { force: true } (skip actionability "receives pointer events"
		// check) -- kita nge-test logic draft-save-nya, bukan urutan visual klik
		// user yang udah diverifikasi manual pakai screenshot di Round 28.
		await page.goto('chimei/add');
		// { force: true } masih nyoba klik beneran di koordinat elemen -- overlay
		// full-screen-nya tetep "menangkap" klik itu (browser hit-testing biasa),
		// jadi radio-nya gak pernah keceklis. Manipulasi DOM + dispatch event
		// langsung lewat evaluate() biar gak lewat mouse sama sekali.
		const kendalaText = 'Draft test Playwright - kendala roller aus';
		await page.evaluate((text) => {
			const radio = document.querySelector('.part-kondisi[value="NOK"]');
			radio.checked = true;
			radio.dispatchEvent(new Event('change', { bubbles: true }));
			const box = radio.closest('.part-card').querySelector('.kendala-box textarea');
			box.value = text;
			box.dispatchEvent(new Event('input', { bubbles: true }));
		}, kendalaText);
		const firstNok = page.locator('.part-kondisi[value="NOK"]').first();
		const kendalaBox = page.locator('.kendala-box').first();
		await expect(kendalaBox).toBeVisible();

		// 3. Modal peringatan harus kelihatan (isinya bener).
		const warningModal = page.locator('#session-timeout-warning');
		await expect(warningModal).toBeVisible({ timeout: 15000 });
		await expect(warningModal).toContainText('Sesi Akan Berakhir');

		// 4. Tunggu timeout beneran -- doTimeout() nyimpen draft ke localStorage
		// terus redirect ke logout/login.
		await page.waitForURL((url) => !url.pathname.includes('chimei/add'), { timeout: 20000 });
		await expect(page.locator('input[name="password"]')).toBeVisible();

		// 5. Login ulang, balik ke halaman yang sama -- notice restore draft harus muncul.
		await page.fill('input[name="username"]', 'superadmin');
		await page.fill('input[name="password"]', 'Admin@123');
		await page.click('button[type="submit"]');
		await expect(page).toHaveURL(/Home/i);

		await page.goto('chimei/add');
		// Timer idle langsung jalan lagi begitu halaman kebuka, dan karena
		// WARNING_MS (5 menit, hardcode) > IDLE_LIMIT_MS (~10 detik, override
		// testing), modal peringatan bakal TERUS nongol ulang tiap kali di-reset
		// (klik "Saya Masih Di Sini" -> resetTimers() -> nongol lagi ~0ms
		// kemudian). Ini murni konsekuensi override super-pendek buat testing,
		// bukan reproduksi behavior real (di production delay-nya 5 menit
		// beneran, cuma sekali). Makanya klik restore-nya juga lewat evaluate(),
		// gak lewat overlay yang emang bakal terus-terusan di atas.
		const restoreNotice = page.locator('.draft-restore-notice');
		await expect(restoreNotice).toBeVisible();
		await page.evaluate(() => document.querySelector('#draft-restore-btn').click());

		// 6. Field yang tadi diisi harus balik persis kayak sebelum timeout.
		await expect(firstNok).toBeChecked();
		await expect(kendalaBox).toBeVisible();
		await expect(kendalaBox.locator('textarea')).toHaveValue(kendalaText);

		// Cleanup: gak submit form ini (biar gak nambah data test), cukup navigasi pergi --
		// localStorage draft udah dihapus otomatis pas restore (lihat main_layout.php).
	});
});
