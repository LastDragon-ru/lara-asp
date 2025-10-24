<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem;

use Override;
use Stringable;

readonly class Content implements Stringable {
    public function __construct(
        public string $content,
    ) {
        // empty
    }

    #[Override]
    public function __toString(): string {
        return $this->content;
    }
}
