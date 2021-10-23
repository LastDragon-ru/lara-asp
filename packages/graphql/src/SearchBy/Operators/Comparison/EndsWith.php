<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

class EndsWith extends Contains {
    public function getName(): string {
        return 'endsWith';
    }

    protected function getDescription(): string {
        return 'Ends with a string.';
    }

    protected function value(string $value): string {
        return "%{$value}";
    }
}
