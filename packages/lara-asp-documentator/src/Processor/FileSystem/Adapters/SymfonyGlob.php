<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Glob;
use Override;
use Stringable;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
readonly class SymfonyGlob extends Glob {
    /**
     * @param list<non-empty-string> $patterns
     */
    public function __construct(
        protected SymfonyPathMap $map,
        array $patterns,
        bool $hidden,
    ) {
        parent::__construct($patterns, $hidden);
    }

    #[Override]
    public function match(SplFileInfo|Stringable|string $string): bool {
        if ($string instanceof SplFileInfo) {
            $string = $this->map->get($string);
        }

        return parent::match($string);
    }
}
