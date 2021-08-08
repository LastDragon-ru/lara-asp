<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints;

use LastDragon_ru\LaraASP\Testing\Utils\Args;

use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

trait JsonPrettify {
    protected function prettify(mixed $value): string {
        return Args::getJsonPrettyString($value);
    }
}
