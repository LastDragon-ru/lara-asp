<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetExecFailed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instruction;
use LastDragon_ru\LaraASP\Documentator\Utils\Process;

use function dirname;
use function explode;

class IncludeExec implements Instruction {
    public function __construct(
        protected readonly Process $process,
    ) {
        // empty
    }

    public static function getName(): string {
        return 'include:exec';
    }

    public static function getDescription(): string {
        return 'Executes the `<target>` and returns result.';
    }

    public static function getTargetDescription(): ?string {
        return 'Path to the executable.';
    }

    public function process(string $path, string $target): string {
        try {
            return $this->process->run(
                explode(' ', $target, 2), // todo(documentator): Probably we need to parse args?
                dirname($path),
            );
        } catch (Exception $exception) {
            throw new TargetExecFailed($path, $target, $exception);
        }
    }
}
