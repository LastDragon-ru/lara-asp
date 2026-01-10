<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\String;

use LastDragon_ru\LaraASP\Formatter\Contracts\Format;
use Override;
use Stringable;

use function mb_trim;

/**
 * @implements Format<null, Stringable|string|null>
 */
readonly class StringFormat implements Format {
    public function __construct() {
        // empty
    }

    #[Override]
    public function __invoke(mixed $value): string {
        return mb_trim((string) $value);
    }
}
