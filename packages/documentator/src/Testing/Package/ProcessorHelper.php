<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Testing\Package;

use Generator;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

/**
 * @internal
 */
class ProcessorHelper {
    public static function runTask(Task $task, Directory $root, File $file): mixed {
        $result = ($task)($root, $file);

        if ($result instanceof Generator) {
            $directory = $root->getDirectory($file);

            while ($result->valid()) {
                $result->send($directory?->getFile($result->current()));
            }

            $result = $result->getReturn();
        }

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

        if ($result instanceof Generator) {
            $directory = $context->root->getDirectory($context->file);

            while ($result->valid()) {
                $result->send($directory?->getFile($result->current()));
            }

            $result = $result->getReturn();
        }

        return $result;
    }
}
