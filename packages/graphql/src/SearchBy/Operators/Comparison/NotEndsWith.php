<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

class NotEndsWith extends EndsWith {
    public static function getName(): string {
        return 'notEndsWith';
    }

    public function getFieldDescription(): string {
        return 'Not ends with a string.';
    }

    protected function isNegated(): bool {
        return true;
    }
}
