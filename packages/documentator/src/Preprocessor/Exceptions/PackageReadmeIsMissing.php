<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use Throwable;

use function sprintf;

class PackageReadmeIsMissing extends InstructionFailed {
    public function __construct(
        string $path,
        string $target,
        private readonly string $package,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $path,
            $target,
            sprintf(
                "The package `%s` doesn't contain readme (in `%s`).",
                $this->package,
                $path,
            ),
            $previous,
        );
    }

    public function getPackage(): string {
        return $this->package;
    }
}
