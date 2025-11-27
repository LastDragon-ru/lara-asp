<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Glob;
use Override;
use Stringable;
use Symfony\Component\Finder\SplFileInfo;

readonly class SymfonyGlob extends Glob {
    #[Override]
    public function match(SplFileInfo|Stringable|string $string): bool {
        if ($string instanceof SplFileInfo) {
            $string = $string->getRelativePathname();
        }

        return parent::match($string);
    }
}
