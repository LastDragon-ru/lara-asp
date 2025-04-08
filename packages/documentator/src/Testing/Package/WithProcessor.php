<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Testing\Package;

use Generator;
use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Metadata;

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
        $metadata   = new Metadata($this->app()->make(ContainerResolver::class));
        $dispatcher = new Dispatcher();
        $filesystem = new FileSystem($dispatcher, $metadata, $input, $output, true);

        foreach ($resolvers as $resolver) {
            $metadata->addResolver($resolver);
        }

        return $filesystem;
    }

    /**
     * @template T
     *
     * @param T|Generator<mixed, Dependency<*>, mixed, T> $result
     *
     * @return T
     */
    protected function getProcessorResult(FileSystem $filesystem, mixed $result): mixed {
        if ($result instanceof Generator) {
            while ($result->valid()) {
                $dependency = $result->current();

                if ($dependency instanceof Dependency) {
                    $result->send(($dependency)($filesystem));
                }
            }

            $result = $result->getReturn();
        }

        return $result;
    }
}
