<?php
require __DIR__ . '/../vendor/autoload.php';

putenv('APP_ENV=testing');
putenv('DB_NAME=form_am_plg_test');
require_once __DIR__ . '/../config.php';

if (!preg_match('/(?:^|_)test$/i', DB_NAME)) {
    throw new RuntimeException('PHPUnit diblokir: DB_NAME wajib menunjuk database khusus berakhiran _test.');
}
