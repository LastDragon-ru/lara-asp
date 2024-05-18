<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use Throwable;

use function sprintf;

class PackageComposerJsonIsMissing extends InstructionFailed {
    public function __construct(
        Context $context,
        private readonly string $package,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            sprintf(
                "The package `%s` doesn't contain `composer.json` (in `%s`).",
                $this->package,
                $context->path,
            ),
            $previous,
        );
    }

    public function getPackage(): string {
        return $this->package;
    }
}
