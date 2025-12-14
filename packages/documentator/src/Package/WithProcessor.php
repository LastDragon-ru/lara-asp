<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as ResolverContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult as Result;
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
                ?Closure $include = null,
                ?Closure $exclude = null,
            ): Finder {
                return parent::getFinder($directory, $include, $exclude)
                    ->sortByName(true);
            }

            #[Override]
            public function write(FilePath $path, string $content): void {
                // Skip
            }
        };
        $dispatcher = new Dispatcher();
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $output);

        return $filesystem;
    }

    protected function runProcessorFileTask(FileTask $task, FileSystem $fs, File $file): void {
        $task($this->getProcessorResolver($fs), $file);
    }

    protected function getProcessorResolver(FileSystem $fs): ResolverContract {
        $dispatcher = new Dispatcher();
        $container  = $this->app()->make(ContainerResolver::class);
        $callback   = static function (): void {
            // empty
        };
        $resolver   = new class($container, $dispatcher, $fs, $callback, $callback) extends Resolver {
            #[Override]
            protected function notify(FilePath|File|string $path, Result $result): void {
                // Makes no sense anyway
            }
        };

        return $resolver;
    }
}
