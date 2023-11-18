<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

class NotContains extends Contains {
    public static function getName(): string {
        return 'notContains';
    }

    public function getFieldDescription(): string {
        return 'Not contains.';
    }

    protected function isNegated(): bool {
        return true;
    }
}
