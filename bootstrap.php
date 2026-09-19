<?php

declare(strict_types=1);

use Naf\View\Core\Asset;

use function Naf\app;

app()->container()->set(Asset::class, function () {
    return new Asset();
});
