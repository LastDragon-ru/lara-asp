<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use Override;

use function mb_strrpos;
use function mb_substr;

abstract class Base implements Link {
    public function __construct(
        public readonly string $class,
    ) {
        // empty
    }

    #[Override]
    public function getTitle(): ?string {
        $title    = (string) $this;
        $position = mb_strrpos($title, '\\');

        if ($position !== false) {
            $title = mb_substr($title, $position + 1);
        }

        return $title ?: null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSource(Directory $root, File $file, Package $package): array|string|null {
        return $package->resolve($this->class);
    }
}
