<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Helpers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View as ViewContract;
use LastDragon_ru\LaraASP\Core\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Viewer::class)]
class ViewerTest extends TestCase {
    public function testGet(): void {
        $view       = 'view';
        $data       = ['a' => 123];
        $package    = 'package';
        $translator = Mockery::mock(Translator::class);
        $factory    = Mockery::mock(ViewFactory::class);
        $factory
            ->shouldReceive('make')
            ->with("{$package}::{$view}", ['translator' => $translator] + $data)
            ->once()
            ->andReturn(
                Mockery::mock(ViewContract::class),
            );

        $viewer = new class($translator, $factory, $package) extends Viewer {
            // empty
        };

        $viewer->get($view, $data);
    }

    public function testRender(): void {
        $view       = 'view';
        $data       = ['a' => 123];
        $package    = 'package';
        $content    = 'content';
        $translator = Mockery::mock(Translator::class);
        $factory    = Mockery::mock(ViewFactory::class);
        $template   = Mockery::mock(ViewContract::class);
        $template
            ->shouldReceive('render')
            ->once()
            ->andReturn($content);

        $viewer = Mockery::mock(Viewer::class, [$translator, $factory, $package]);
        $viewer->makePartial();
        $viewer
            ->shouldReceive('get')
            ->with($view, $data)
            ->once()
            ->andReturn($template);

        self::assertEquals($content, $viewer->render($view, $data));
    }
}
