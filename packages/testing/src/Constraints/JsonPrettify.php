<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints;

use LastDragon_ru\LaraASP\Testing\Utils\Args;

trait JsonPrettify {
    protected function prettify(mixed $value): string {
        return Args::getJsonPrettyString($value);
    }
}
