<?php

declare(strict_types=1);

use function Naf\guard;

if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/Fixtures');
}

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../src/view_helpers.php";

// Only for testing purposes
guard()->register('safePath', fn($path) => $path);
guard()->register('safeOutput', fn($value) => $value);
