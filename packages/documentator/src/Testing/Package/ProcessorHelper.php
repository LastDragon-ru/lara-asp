<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Testing\Package;

use Generator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

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
     * @param Generator<mixed, Dependency<covariant mixed>, mixed, mixed> $generator
     */
    protected static function getResult(Directory $root, File $file, Generator $generator): mixed {
        while ($generator->valid()) {
            $generator->send(($generator->current())($root, $file));
        }

        return $generator->getReturn();
    }
}
