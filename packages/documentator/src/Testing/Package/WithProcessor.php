<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Testing\Package;

use Generator;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;

/**
 * @phpstan-require-extends TestCase
 * @internal
 */
trait WithProcessor {
    protected function getFileSystem(DirectoryPath|string $input): FileSystem {
        return new FileSystem(
            ($input instanceof DirectoryPath ? $input : new DirectoryPath($input))->getNormalizedPath(),
        );
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
