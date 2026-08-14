// @ts-check
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
	testDir: './tests/e2e',
	timeout: 30 * 1000,
	fullyParallel: false, // session-timeout test manipulasi .env global -- jangan paralel
	retries: 0,
	reporter: 'list',
	use: {
		// WAJIB trailing slash -- baseURL punya subpath (/form-am), tanpa trailing
		// slash, page.goto('chimei/add') bakal salah resolve jadi
		// http://localhost/chimei/add (nge-drop /form-am), bukan
		// http://localhost/form-am/chimei/add. Ini standar WHATWG URL resolution,
		// bukan bug Playwright.
		baseURL: process.env.APP_BASE_URL || 'http://localhost/form-am/',
		trace: 'retain-on-failure',
	},
});
