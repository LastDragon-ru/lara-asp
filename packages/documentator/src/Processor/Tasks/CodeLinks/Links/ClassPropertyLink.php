<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\Traits\ClassTitle;
use Override;

readonly class ClassPropertyLink implements Link {
    use ClassTitle;

    public function __construct(
        public string $class,
        public string $property,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSource(Directory $root, File $file, Package $package): array|string|null {
        return $package->resolve($this->class);
    }

    #[Override]
    public function __toString(): string {
        return "{$this->class}::\${$this->property}";
    }
}
