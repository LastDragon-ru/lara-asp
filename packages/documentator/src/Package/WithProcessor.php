<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Casts;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as ResolverContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult as Result;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters\SymfonyFileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
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
                array $include = [],
                array $exclude = [],
                ?int $depth = null,
            ): Finder {
                return parent::getFinder($directory, $include, $exclude, $depth)
                    ->sortByName(true);
            }

            #[Override]
            public function write(FilePath $path, string $content): void {
                // Skip
            }
        };
        $caster     = new Caster($this->app()->make(Casts::class));
        $dispatcher = new Dispatcher();
        $filesystem = new FileSystem($adapter, $dispatcher, $caster, $input, $output);

        return $filesystem;
    }

    protected function runProcessorFileTask(FileTask $task, FileSystem $fs, File $file): void {
        $task($this->getDependencyResolver($fs), $file);
    }

    protected function getDependencyResolver(FileSystem $fs): ResolverContract {
        $dispatcher = new Dispatcher();
        $callback   = static function (): void {
            // empty
        };
        $resolver   = new class($dispatcher, $fs, $callback, $callback) extends Resolver {
            #[Override]
            protected function notify(FilePath|File|string $path, Result $result): void {
                // Makes no sense anyway
            }
        };

        return $resolver;
    }
}
