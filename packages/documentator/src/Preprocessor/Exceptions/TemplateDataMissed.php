<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use Throwable;

use function sprintf;

class TemplateDataMissed extends InstructionFailed {
    public function __construct(
        string $path,
        string $target,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $path,
            $target,
            sprintf(
                'The `data` is required for `%s`.',
                $path,
            ),
            $previous,
        );
    }
}
