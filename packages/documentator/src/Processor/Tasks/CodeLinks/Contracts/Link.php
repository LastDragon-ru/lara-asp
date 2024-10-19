<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\LinkTarget;
use Stringable;

interface Link extends Stringable {
    /**
     * The returned value will be used as a link text. If `null` the `(string)`
     * cast will be used.
     */
    public function getTitle(): ?string;

    /**
     * Checks if the `$link` has a similar title. If links are not similar, the
     * {@see self::getTitle()} will be used as a link text. If similar, the
     * `(string)` form will be used (for both).
     */
    public function isSimilar(self $link): bool;

    /**
     * Returns one or more file paths to the source code of the link.
     *
     * @return list<FilePath>|FilePath|null
     */
    public function getSource(Directory $root, File $file, Package $package): array|FilePath|null;

    /**
     * Returns the url for the (first resolved) `$source` file.
     */
    public function getTarget(Directory $root, File $file, File $source): ?LinkTarget;
}
