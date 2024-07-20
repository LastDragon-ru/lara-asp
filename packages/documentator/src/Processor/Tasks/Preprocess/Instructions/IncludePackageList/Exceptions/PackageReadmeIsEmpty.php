<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\InstructionFailed;
use Throwable;

use function sprintf;

class PackageReadmeIsEmpty extends InstructionFailed {
    public function __construct(
        Context $context,
        private readonly Directory $package,
        private readonly File $readme,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            sprintf(
                'The package `%s` readme file `%s` is empty or not readable (in `%s`).',
                $this->package->getRelativePath($context->root),
                $this->readme->getRelativePath($context->root),
                $context->file->getRelativePath($context->root),
            ),
            $previous,
        );
    }

    public function getPackage(): Directory {
        return $this->package;
    }

    public function getReadme(): File {
        return $this->readme;
    }
}
