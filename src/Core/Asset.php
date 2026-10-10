<?php

declare(strict_types=1);

namespace Naf\View\Core;

use function Naf\log;
use function Naf\View\s;

class Asset
{
    private const string TAG_CSS       = '<link rel="stylesheet" href="%s">';
    private const string TAG_JS        = '<script src="%s"></script>';
    private const string TAG_JS_MODULE = '<script type="module" src="%s"></script>';

    protected array $assets = [
        'css' => [],
        'js'  => [
            'classic' => [],
            'module'  => [],
        ],
    ];

    /**
     * Collect a stylesheet or script for the layout to render
     *
     * The type comes from the extension of the URL path, so a cache-busting
     * query string (`/css/app.css?v=2`) or a fragment does not hide it. `.mjs`
     * files are ES modules and always render as `type="module"`. A path whose
     * type cannot be told is not collected; the application log says so instead
     * of the asset disappearing without a trace.
     *
     * @param string $path URL of the asset, as it should appear in the tag
     * @param string $mode `classic` or `module` for JavaScript; anything else means `classic`
     */
    public function add(string $path, string $mode = 'classic'): void
    {
        $urlPath   = parse_url($path, PHP_URL_PATH);
        $extension = strtolower(pathinfo(is_string($urlPath) ? $urlPath : '', PATHINFO_EXTENSION));

        if ($extension === 'css') {
            $this->assets['css'][] = $path;

            return;
        }

        if ($extension === 'js' || $extension === 'mjs') {
            $mode = $extension === 'mjs' ? 'module' : strtolower($mode);

            if (!in_array($mode, ['classic', 'module'], true)) {
                $mode = 'classic';
            }

            $this->assets['js'][$mode][] = $path;

            return;
        }

        log()->warning('Asset not collected, its type is unknown: {path}', ['path' => $path]);
    }

    public function list(string $type, string $mode = 'classic'): array
    {
        if ($type === 'js') {
            return array_unique($this->assets['js'][$mode] ?? []);
        }

        return array_unique($this->assets[$type] ?? []);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function render(string $type): string
    {
        $html = '';

        if ($type === 'css') {
            foreach ($this->list('css') as $path) {
                $html .= sprintf(self::TAG_CSS, s($path)) . PHP_EOL;
            }
        } elseif ($type === 'js') {
            foreach ($this->list('js', 'classic') as $path) {
                $html .= sprintf(self::TAG_JS, s($path)) . PHP_EOL;
            }
            foreach ($this->list('js', 'module') as $path) {
                $html .= sprintf(self::TAG_JS_MODULE, s($path)) . PHP_EOL;
            }
        }

        return trim($html);
    }
}
