<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\DependencyResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters\SymfonyAdapter;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\Resolver;
use Override;
use Symfony\Component\Finder\Finder;

/**
 * @phpstan-require-extends TestCase
 * @internal
 */
trait WithProcessor {
    abstract protected function app(): Application;

    /**
     * @template V of object
     * @template R of MetadataResolver<V>
     *
     * @param array<array-key, R|class-string<R>> $resolvers
     */
    protected function getFileSystem(
        DirectoryPath|string $input,
        DirectoryPath|string|null $output = null,
        array $resolvers = [],
    ): FileSystem {
        $input      = ($input instanceof DirectoryPath ? $input : new DirectoryPath($input))->getNormalizedPath();
        $output     = $output !== null
            ? ($output instanceof DirectoryPath ? $output : new DirectoryPath($output))->getNormalizedPath()
            : $input;
        $adapter    = new class() extends SymfonyAdapter {
            /**
             * @inheritDoc
             */
            #[Override]
            protected function getFinder(
                string $directory,
                array|string|null $include = null,
                array|string|null $exclude = null,
                ?int $depth = null,
            ): Finder {
                return parent::getFinder($directory, $include, $exclude, $depth)
                    ->sortByName(true);
            }
        };
        $metadata   = new Metadata($this->app()->make(ContainerResolver::class));
        $dispatcher = new Dispatcher();
        $filesystem = new FileSystem($dispatcher, $metadata, $adapter, $input, $output);

        foreach ($resolvers as $resolver) {
            $metadata->addResolver($resolver);
        }

        return $filesystem;
    }

    protected function runProcessorTask(Task $task, FileSystem $fs, File $file): void {
        $task($this->getDependencyResolver($fs), $file);
    }

    protected function getDependencyResolver(FileSystem $fs): DependencyResolver {
        $dispatcher = new Dispatcher();
        $callback   = static function (): void {
            // empty
        };
        $resolver   = new Resolver($dispatcher, $fs, $callback, $callback);

        return $resolver;
    }
}
