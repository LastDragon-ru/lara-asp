<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Testing\Package;

use Generator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileDependencyNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use SplFileInfo;

use function dirname;

/**
 * @internal
 */
class ProcessorHelper {
    public static function runTask(Task $task, Directory $root, File $file): mixed {
        $result = ($task)($root, $file);
        $result = $result instanceof Generator
            ? self::getResult($root, $file, $result)
            : $result;

        return $result;
    }

    /**
     * @template P of object|null
     * @template T
     *
     * @param Instruction<T, P> $instruction
     * @param T                 $target
     * @param P                 $parameters
     */
    public static function runInstruction(
        Instruction $instruction,
        Context $context,
        mixed $target,
        mixed $parameters,
    ): string {
        $result = ($instruction)($context, $target, $parameters);
        $result = $result instanceof Generator
            ? Cast::toString(self::getResult($context->root, $context->file, $result))
            : $result;

        return $result;
    }

    /**
     * @param Generator<mixed, SplFileInfo|File|string, File, mixed> $generator
     */
    protected static function getResult(Directory $root, File $file, Generator $generator): mixed {
        while ($generator->valid()) {
            $generator->send(self::getFile($root, $file, $generator));
        }

        return $generator->getReturn();
    }

    /**
     * @param Generator<mixed, SplFileInfo|File|string, File, mixed> $generator
     */
    protected static function getFile(Directory $root, File $file, Generator $generator): File {
        $path = $generator->current();
        $path = match (true) {
            $path instanceof SplFileInfo => $path->getPathname(),
            $path instanceof File        => $path->getPath(),
            default                      => $path,
        };
        $directory  = dirname($file->getPath());
        $dependency = $root->getFile(Path::getPath($directory, $path));

        if (!$dependency) {
            throw new FileDependencyNotFound($root, $file, $path);
        }

        return $dependency;
    }
}
