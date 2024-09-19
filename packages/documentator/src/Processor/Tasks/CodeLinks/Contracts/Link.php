<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts;

use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Stringable;

interface Link extends Stringable {
    public function getTitle(): ?string;

    /**
     * @return list<string>|string|null
     */
    public function getSource(Directory $root, File $file, Package $package): array|string|null;
}
