<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Helpers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View as ViewContract;
use LastDragon_ru\LaraASP\Core\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Viewer::class)]
final class ViewerTest extends TestCase {
    public function testGet(): void {
        $view    = 'view';
        $data    = ['a' => 123];
        $package = 'package';
        $factory = Mockery::mock(ViewFactory::class);
        $factory
            ->shouldReceive('make')
            ->with("{$package}::{$view}", $data)
            ->once()
            ->andReturn(
                Mockery::mock(ViewContract::class),
            );

        $viewer = new class($factory, $package) extends Viewer {
            public function __construct(
                ViewFactory $factory,
                private readonly string $package,
            ) {
                parent::__construct($factory);
            }

            #[Override]
            protected function getName(): string {
                return $this->package;
            }
        };

        $viewer->get($view, $data);
    }

    public function testRender(): void {
        $view     = 'view';
        $data     = ['a' => 123];
        $package  = 'package';
        $content  = 'content';
        $factory  = Mockery::mock(ViewFactory::class);
        $template = Mockery::mock(ViewContract::class);
        $template
            ->shouldReceive('render')
            ->once()
            ->andReturn($content);

        $viewer = Mockery::mock(Viewer::class, [$factory, $package]);
        $viewer->makePartial();
        $viewer
            ->shouldReceive('get')
            ->with($view, $data)
            ->once()
            ->andReturn($template);

        self::assertSame($content, $viewer->render($view, $data));
    }
}
