<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use InvalidArgumentException;
use Naf\Core\Config;
use Naf\View\Core\View;
use RuntimeException;
use Tests\NafTestCase;

use function Naf\app;
use function Naf\guard;
use function Naf\View\view;

class ViewTest extends NafTestCase
{
    public function testViewCreation()
    {
        $view = new View();
        $view->setTemplate('test');
        $this->assertSame('content', $view->render());
    }

    public function testTemplateNotFoundException()
    {
        $this->expectException(RuntimeException::class);
        $view = new View();
        $view->setTemplate('test_not_exists');
    }

    public function testViewCreationWithVariables()
    {
        $view = new View();
        $view->setTemplate('test_var');
        $view->setVariable('foo', 'bar');
        $this->assertSame('foo,bar', $view->render());
    }

    public function testViewCreationWithLayout()
    {
        $view = new View();
        $view->setTemplate('test_layout');
        $this->assertSame('layout,content', trim($view->render()));
    }

    public function testRespectsRelativeConfiguredViewPaths()
    {
        $this->withViewConfig([
            'view' => [
                'paths' => [
                    'overrides',
                    'views',
                ],
            ],
        ], function () {
            $view = new View();
            $view->setTemplate('test_relative');
            $this->assertSame('relative view', trim($view->render()));
        });
    }

    public function testLoadsViewsFromAbsoluteConfiguredPaths()
    {
        $absolutePath = BASE_PATH . '/absolute_views';

        $this->withViewConfig([
            'view' => [
                'paths' => [
                    $absolutePath,
                ],
            ],
        ], function () {
            $view = new View();
            $view->setTemplate('test_absolute');
            $this->assertSame('absolute view', trim($view->render()));
        });
    }

    public function testDefaultPathsIncludeTheSrcLayout()
    {
        // An application keeps its code in app/ or in src/. naf/framework accepts
        // either for a plugin's views and for plugins.php; an application laid out
        // the second way had nowhere conventional for its own until now.
        // An empty config falls back to DEFAULT_VIEW_PATHS, which is what is at stake.
        $this->withViewConfig([], function () {
            $view = new View();
            $view->setTemplate('test_src_layout');
            $this->assertSame('src layout view', trim($view->render()));
        });
    }

    public function testTheShippedConfigIncludesTheSrcLayout()
    {
        $shipped = require __DIR__ . '/../../src/config.php';

        $this->withViewConfig($shipped, function () {
            $view = new View();
            $view->setTemplate('test_src_layout');
            $this->assertSame('src layout view', trim($view->render()));
        });
    }

    public function testTheOlderLayoutWinsWhenAProjectHasBoth()
    {
        // The same template name exists in views/ and in src/views/. src/views is
        // listed last, so a project holding both resolves exactly as it did before
        // this was added -- which is the whole reason it was appended rather than
        // put in front.
        $this->withViewConfig([], function () {
            $view = new View();
            $view->setTemplate('test_layout_precedence');
            $this->assertSame('older layout wins', trim($view->render()));
        });
    }

    public function testMissingOpenedBlockInView()
    {
        $this->expectException(Exception::class);
        $view = new View();
        $view->setTemplate('test_missing_block');
        $view->render();
    }

    public function testMaliciousTemplatePath()
    {
        $this->expectException(InvalidArgumentException::class);
        $view = new View();
        guard()->register('safePath', fn($path) => throw new InvalidArgumentException('test'));
        $view->setTemplate('../../../../etc/passwd');
        $view->render();
    }

    public function testHelperFunction()
    {
        guard()->register('safePath', fn($path) => $path);
        $this->assertIsString(view('test'));
    }

    private function withViewConfig(array $settings, callable $callback): void
    {
        $container      = app()->container();
        $originalConfig = $container->get(Config::class);

        $container->reset(Config::class);
        $container->set(Config::class, new Config($settings));

        try {
            $callback();
        } finally {
            $container->reset(Config::class);
            $container->set(Config::class, $originalConfig);
        }
    }
}
