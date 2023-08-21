<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PreprocessFailed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instruction;
use LastDragon_ru\LaraASP\Documentator\Utils\Process;

use function sprintf;

class IncludeCommand implements Instruction {
    public function __construct(
        protected readonly Process $process,
    ) {
        // empty
    }

    public static function getName(): string {
        return 'include:command';
    }

    public function process(string $path, string $target): string {
        try {
            return $this->process->run([$target], $path);
        } catch (Exception $exception) {
            throw new PreprocessFailed(
                sprintf(
                    'Failed to execute command `%s`.',
                    $target,
                ),
                $exception,
            );
        }
    }
}
