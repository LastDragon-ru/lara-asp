<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

class NotStartsWith extends StartsWith {
    public static function getName(): string {
        return 'notStartsWith';
    }

    public function getFieldDescription(): string {
        return 'Not starts with a string.';
    }

    protected function isNegated(): bool {
        return true;
    }
}
