<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

class EndsWith extends Contains {
    public static function getName(): string {
        return 'endsWith';
    }

    public function getFieldDescription(): string {
        return 'Ends with a string.';
    }

    protected function value(string $value): string {
        return "%{$value}";
    }
}
