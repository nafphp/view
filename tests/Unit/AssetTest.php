<?php

declare(strict_types=1);

namespace Tests\Unit;

use Naf\View\Core\Asset;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Stringable;
use Tests\NafTestCase;

class AssetTest extends NafTestCase
{
    public function testAssetInternals()
    {
        $asset = new Asset();
        $asset->add('test.css');
        $asset->add('test.js');
        $this->assertSame(['test.css'], $asset->list('css'));
        $this->assertSame(['test.js'], $asset->list('js'));
    }

    public function testAssetRenderCss()
    {
        $asset = new Asset();
        $asset->add('test.css');
        $this->assertSame('<link rel="stylesheet" href="test.css">', $asset->render('css'));
    }

    public function testAssetRenderJs()
    {
        $asset = new Asset();
        $asset->add('test.js');
        $this->assertSame('<script src="test.js"></script>', $asset->render('js'));
    }

    public function testQueryStringsAndFragmentsKeepTheAssetType()
    {
        $asset = new Asset();
        $asset->add('/css/app.css?v=2');
        $asset->add('/js/app.js#main');
        $asset->add('https://cdn.example.com/lib.JS?build=7');

        $this->assertSame(['/css/app.css?v=2'], $asset->list('css'));
        $this->assertSame(['/js/app.js#main', 'https://cdn.example.com/lib.JS?build=7'], $asset->list('js'));
    }

    public function testMjsFilesAreModules()
    {
        $asset = new Asset();
        $asset->add('/js/editor.mjs');
        $asset->add('/js/legacy.mjs', 'classic');

        $this->assertSame([], $asset->list('js'));
        $this->assertSame(['/js/editor.mjs', '/js/legacy.mjs'], $asset->list('js', 'module'));
        $this->assertSame(
            '<script type="module" src="/js/editor.mjs"></script>' . PHP_EOL
                . '<script type="module" src="/js/legacy.mjs"></script>',
            $asset->render('js'),
        );
    }

    public function testUnknownTypesAreLoggedInsteadOfDroppedSilently()
    {
        $logger = new class extends AbstractLogger {
            public array $records = [];

            public function log($level, Stringable|string $message, array $context = []): void
            {
                $this->records[] = [$level, $message, $context];
            }
        };
        \Naf\app()->container()->set(LoggerInterface::class, $logger);

        $asset = new Asset();
        $asset->add('/images/logo.png');

        $this->assertSame([], $asset->list('css'));
        $this->assertSame([], $asset->list('js'));
        $this->assertSame(
            [['warning', 'Asset not collected, its type is unknown: {path}', ['path' => '/images/logo.png']]],
            $logger->records,
        );
    }
}
