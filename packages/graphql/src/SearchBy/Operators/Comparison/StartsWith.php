<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

class StartsWith extends Contains {
    public static function getName(): string {
        return 'startsWith';
    }

    public function getFieldDescription(): string {
        return 'Starts with a string.';
    }

    protected function value(string $value): string {
        return "{$value}%";
    }
}
