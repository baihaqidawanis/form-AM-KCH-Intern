// Wrapper buat jalanin Playwright test session-timeout dengan
// SESSION_TIMEOUT_SECONDS dipendekin sementara (jangan 30 menit beneran).
// .env DIKEMBALIKAN LAGI ke isi semula di akhir -- baik playwright-nya
// sukses ATAUPUN gagal (finally) -- biar gak ada environment yang "nyangkut"
// abis testing.
const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');

const envPath = path.join(__dirname, '..', '..', '.env');
const original = fs.readFileSync(envPath, 'utf8');

const TEST_TIMEOUT_SECONDS = process.env.E2E_TIMEOUT_SECONDS || '10';

function setTimeoutOverride(seconds) {
	let content = original;
	if (/^SESSION_TIMEOUT_SECONDS=.*$/m.test(content)) {
		content = content.replace(/^SESSION_TIMEOUT_SECONDS=.*$/m, `SESSION_TIMEOUT_SECONDS=${seconds}`);
	} else {
		content = content.trimEnd() + `\nSESSION_TIMEOUT_SECONDS=${seconds}\n`;
	}
	fs.writeFileSync(envPath, content);
}

console.log(`[run-with-short-timeout] Set SESSION_TIMEOUT_SECONDS=${TEST_TIMEOUT_SECONDS} sementara di .env`);
setTimeoutOverride(TEST_TIMEOUT_SECONDS);

let exitCode = 1;
try {
	const result = spawnSync('npx', ['playwright', 'test', ...process.argv.slice(2)], {
		stdio: 'inherit',
		shell: true,
		cwd: path.join(__dirname, '..', '..'),
	});
	exitCode = result.status ?? 1;
} finally {
	console.log('[run-with-short-timeout] Balikin .env ke isi semula');
	fs.writeFileSync(envPath, original);
}

process.exit(exitCode);
