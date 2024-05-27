<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExample\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

interface Runner {
    public function __invoke(File $file): ?string;
}
