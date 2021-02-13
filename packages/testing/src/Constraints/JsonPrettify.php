<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints;

use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

trait JsonPrettify {
    protected function prettify($value): string {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
