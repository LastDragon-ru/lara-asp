<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as ResolverContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters\SymfonyFileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use Symfony\Component\Finder\Finder;

/**
 * @phpstan-require-extends TestCase
 * @internal
 */
trait WithProcessor {
    abstract protected function app(): Application;

    protected function getFileSystem(
        DirectoryPath|string $input,
        DirectoryPath|string|null $output = null,
    ): FileSystem {
        $input      = ($input instanceof DirectoryPath ? $input : new DirectoryPath($input))->normalized();
        $output     = $output !== null
            ? ($output instanceof DirectoryPath ? $output : new DirectoryPath($output))->normalized()
            : $input;
        $adapter    = new class() extends SymfonyFileSystem {
            /**
             * @inheritDoc
             */
            #[Override]
            protected function getFinder(
                DirectoryPath $directory,
                ?Closure $include,
                ?Closure $exclude,
                bool $hidden,
            ): Finder {
                return parent::getFinder($directory, $include, $exclude, $hidden)
                    ->sortByName(true);
            }

            #[Override]
            public function write(FilePath $path, string $content): void {
                // Skip
            }
        };
        $dispatcher = new Dispatcher(null);
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $output);

        return $filesystem;
    }

    protected function runProcessorFileTask(FileTask $task, FileSystem $fs, File $file): void {
        $task($this->getProcessorResolver($fs), $file);
    }

    protected function getProcessorResolver(FileSystem $filesystem): ResolverContract {
        $dispatcher = new Dispatcher(null);
        $container  = $this->app()->make(Container::class);
        $callback   = static function (): void {
            // empty
        };
        $resolver   = new class(
            $container,
            $dispatcher,
            $filesystem,
            $callback,
            $callback,
            $callback,
            $callback,
        ) extends Resolver {
            // empty
        };

        return $resolver;
    }
}
