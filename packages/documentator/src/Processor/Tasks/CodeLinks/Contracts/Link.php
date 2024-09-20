<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts;

use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\LinkTarget;
use Stringable;

interface Link extends Stringable {
    public function getTitle(): ?string;

    /**
     * @return list<string>|string|null
     */
    public function getSource(Directory $root, File $file, Package $package): array|string|null;

    public function getTarget(Directory $root, File $file, File $source): ?LinkTarget;
}
