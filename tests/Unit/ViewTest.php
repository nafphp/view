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
        $view->setTemplate('../../../../etc/passwd');
        $view->render();
    }

    public function testHelperFunction()
    {
        $this->assertIsString(view('test'));
    }

    public function testTemplateFailureDiscardsNestedBuffersAndPreservesCallerOutput(): void
    {
        $before = ob_get_level();
        ob_start();
        echo 'caller-output';

        try {
            $view = (new View())->setTemplate('test_failure')->setVariables(['bufferLevel' => -1]);
            $view->render();
            self::fail('Expected the template to throw.');
        } catch (RuntimeException $exception) {
            self::assertSame('template-secret', $exception->getMessage());
            self::assertSame($before + 1, ob_get_level());
            self::assertSame('caller-output', ob_get_contents());
        } finally {
            while (ob_get_level() > $before) {
                ob_end_clean();
            }
        }
        self::assertSame('content', (new View())->setTemplate('test')->render());
    }

    public function testLayoutFailureAlsoDiscardsOutput(): void
    {
        $before = ob_get_level();

        try {
            (new View())->setTemplate('test')->setLayout('test_failure')->render();
            self::fail('Expected the layout to throw.');
        } catch (RuntimeException $exception) {
            self::assertSame('template-secret', $exception->getMessage());
            self::assertSame($before, ob_get_level());
        }
    }

    public function testEscapingIsExplicitAndUsesTheRealFrameworkGuard(): void
    {
        $input   = '<script>alert("x")</script>\'&' . "\xff";
        $escaped = '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;&#039;&amp;' . "\u{fffd}";
        self::assertSame($escaped, \Naf\View\s($input));
        self::assertSame('', \Naf\View\s(null));
        self::assertSame(['value' => $escaped, 'empty' => ''], \Naf\View\s(['value' => $input, 'empty' => null]));
        self::assertSame($escaped . '|' . $input, view('test_escaping', ['value' => $input]));
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
