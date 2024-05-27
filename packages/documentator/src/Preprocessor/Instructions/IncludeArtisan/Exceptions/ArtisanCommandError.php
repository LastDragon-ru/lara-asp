<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeArtisan\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\InstructionFailed;
use Throwable;

use function sprintf;

class ArtisanCommandError extends InstructionFailed {
    public function __construct(
        Context $context,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            sprintf(
                'Artisan command `%s` failed (in `%s`).',
                $context->target,
                $context->file->getRelativePath($context->root),
            ),
            $previous,
        );
    }
}
