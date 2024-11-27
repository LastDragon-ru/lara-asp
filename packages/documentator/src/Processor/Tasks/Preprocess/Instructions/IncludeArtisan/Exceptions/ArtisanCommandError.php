<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\InstructionFailed;
use Throwable;

use function sprintf;

class ArtisanCommandError extends InstructionFailed {
    public function __construct(
        Context $context,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            sprintf(
                'Artisan command `%s` failed (in `%s`).',
                $context->node->getDestination(),
                $context->root->getRelativePath($context->file),
            ),
            $previous,
        );
    }
}
