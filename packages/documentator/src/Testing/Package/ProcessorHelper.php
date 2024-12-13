<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Testing\Package;

use Generator;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use Stringable;

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
     * @template P of Parameters
     *
     * @param Instruction<P> $instruction
     * @param P              $parameters
     */
    public static function runInstruction(
        Instruction $instruction,
        Context $context,
        Stringable|string $target,
        mixed $parameters,
    ): Document|string {
        $result = ($instruction)($context, (string) $target, $parameters);
        $result = $result instanceof Generator
            ? self::getResult($context->root, $context->file, $result)
            : $result;

        return $result;
    }

    /**
     * @template T
     *
     * @param Generator<mixed, Dependency<*>, mixed, T> $generator
     *
     * @return T
     */
    protected static function getResult(Directory $root, File $file, Generator $generator): mixed {
        $fs = new FileSystem($root);

        while ($generator->valid()) {
            $generator->send(($generator->current())($fs, $file));
        }

        return $generator->getReturn();
    }
}
