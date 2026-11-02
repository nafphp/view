<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/Fixtures');
}

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../src/view_helpers.php";

// Older supported cores only register these guards under HTTP. Exercise their real rules.
(new ReflectionMethod(\Naf\app(), 'loadGuards'))->invoke(\Naf\app());
