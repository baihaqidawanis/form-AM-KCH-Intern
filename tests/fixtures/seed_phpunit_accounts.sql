-- Test-only seed. Jangan jalankan pada database produksi karena password fixture
-- diketahui dan sengaja disamakan dengan tests/Support/ApiClient.php.

UPDATE users
SET nama = 'Test Manager',
    email = 'manager@localhost',
    password = '$2y$10$TFuuMcK8x/nBu5FrnsmSDOOsLU25gixWLEz6PU8MpxWwXFLx2uQFa',
    account_status = 'Active',
    user_role_id = 2,
    area = 'Filling',
    mesin = NULL,
    failed_login_attempts = 0,
    login_session_key = NULL,
    password_reset_key = NULL,
    password_expire_date = NULL
WHERE username = 'MANAGE01';

INSERT INTO users (nama, email, username, password, account_status, user_role_id, area, mesin, failed_login_attempts)
SELECT 'Test Manager', 'manager@localhost', 'MANAGE01', '$2y$10$TFuuMcK8x/nBu5FrnsmSDOOsLU25gixWLEz6PU8MpxWwXFLx2uQFa', 'Active', 2, 'Filling', NULL, 0
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'MANAGE01');

UPDATE users
SET nama = 'Test Supervisor',
    email = 'supervisor@localhost',
    password = '$2y$10$TFuuMcK8x/nBu5FrnsmSDOOsLU25gixWLEz6PU8MpxWwXFLx2uQFa',
    account_status = 'Active',
    user_role_id = 3,
    area = 'Filling',
    mesin = NULL,
    failed_login_attempts = 0,
    login_session_key = NULL,
    password_reset_key = NULL,
    password_expire_date = NULL
WHERE username = 'SUPERV01';

INSERT INTO users (nama, email, username, password, account_status, user_role_id, area, mesin, failed_login_attempts)
SELECT 'Test Supervisor', 'supervisor@localhost', 'SUPERV01', '$2y$10$TFuuMcK8x/nBu5FrnsmSDOOsLU25gixWLEz6PU8MpxWwXFLx2uQFa', 'Active', 3, 'Filling', NULL, 0
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'SUPERV01');

UPDATE users
SET nama = 'Test Operator',
    email = 'operator@localhost',
    password = '$2y$10$TFuuMcK8x/nBu5FrnsmSDOOsLU25gixWLEz6PU8MpxWwXFLx2uQFa',
    account_status = 'Active',
    user_role_id = 4,
    area = 'Filling',
    mesin = '1',
    failed_login_attempts = 0,
    login_session_key = NULL,
    password_reset_key = NULL,
    password_expire_date = NULL
WHERE username = 'STAFOP01';

INSERT INTO users (nama, email, username, password, account_status, user_role_id, area, mesin, failed_login_attempts)
SELECT 'Test Operator', 'operator@localhost', 'STAFOP01', '$2y$10$TFuuMcK8x/nBu5FrnsmSDOOsLU25gixWLEz6PU8MpxWwXFLx2uQFa', 'Active', 4, 'Filling', '1', 0
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'STAFOP01');
