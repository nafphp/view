<?php

declare(strict_types=1);

namespace Naf\View;

use Naf\View\Core\Asset;
use Naf\View\Core\View;
use Psr\Http\Message\ResponseInterface;
use function Naf\app;
use function Naf\guard;
use function Naf\response;

function s(string|array|null $value): string|array|null
{
    return guard()->safeOutput($value);
}

function render(string $template, array $vars = []): ResponseInterface
{
    return response(view($template, $vars));
}

function view(string $tpl, array $vars = []): string
{
    return (new View())->setTemplate($tpl)->setVariables($vars)->render();
}

function asset(): Asset
{
    return app()->container()->get(Asset::class);
}