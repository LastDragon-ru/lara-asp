<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\App;

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Laravel\Prompts\Output\ConsoleOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function define;
use function defined;
use function microtime;

class App {
    public static function create(): Application {
        $app = new Application(__DIR__);

        $app->singleton(
            ConsoleKernelContract::class,
            ConsoleKernel::class,
        );
        $app->singleton(
            ExceptionHandlerContract::class,
            ExceptionHandler::class,
        );

        return $app;
    }

    public static function run(InputInterface $input, OutputInterface $output = null): never {
        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        $app    = static::create();
        $kernel = $app->make(ConsoleKernelContract::class);
        $status = $kernel->handle($input, $output ?? new ConsoleOutput());

        $kernel->terminate($input, $status);

        exit($status);
    }
}
