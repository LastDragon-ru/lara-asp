<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Provider;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Core\Utils\Scheduler;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(WithSchedule::class)]
class WithScheduleTest extends TestCase {
    public function testRegistration(): void {
        $this->override(Scheduler::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('register')
                ->with(Mockery::type(Schedule::class), WithScheduleTest_Job::class)
                ->once()
                ->andReturn(true);
        });

        $provider = Container::getInstance()->make(WithScheduleTest_Provider::class, [
            'app' => $this->app,
        ]);

        $provider->boot();

        Container::getInstance()->make(Schedule::class);
    }

    public function testApplication(): void {
        $app = Mockery::mock(Application::class);
        $app
            ->shouldReceive('runningInConsole')
            ->once()
            ->andReturn(false);
        $app
            ->shouldReceive('afterResolving')
            ->with(Schedule::class, Mockery::any())
            ->never();

        $provider = Container::getInstance()->make(WithScheduleTest_Provider::class, [
            'app' => $app,
        ]);

        $provider->boot();
    }

    public function testConsoleApplication(): void {
        $app = Mockery::mock(Application::class);
        $app
            ->shouldReceive('runningInConsole')
            ->once()
            ->andReturn(true);
        $app
            ->shouldReceive('resolved')
            ->with(Schedule::class)
            ->andReturn(false);
        $app
            ->shouldReceive('afterResolving')
            ->with(Schedule::class, Mockery::any())
            ->once()
            ->andReturns();

        $provider = Container::getInstance()->make(WithScheduleTest_Provider::class, [
            'app' => $app,
        ]);

        $provider->boot();
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class WithScheduleTest_Provider extends ServiceProvider {
    use WithSchedule;

    public function boot(): void {
        $this->bootSchedule(
            WithScheduleTest_Job::class,
        );
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class WithScheduleTest_Job {
    // empty
}
