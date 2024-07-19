<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\InstructionFailed;
use Throwable;

use function sprintf;

class ArtisanCommandFailed extends InstructionFailed {
    public function __construct(
        Context $context,
        private readonly int $result,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            sprintf(
                'Artisan command `%s` exited with status code `%s` (in `%s`).',
                $context->target,
                $this->result,
                $context->file->getRelativePath($context->root),
            ),
            $previous,
        );
    }

    public function getResult(): int {
        return $this->result;
    }
}
