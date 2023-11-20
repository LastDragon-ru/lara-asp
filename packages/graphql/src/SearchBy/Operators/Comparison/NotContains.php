<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Override;

class NotContains extends Contains {
    #[Override]
    public static function getName(): string {
        return 'notContains';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Not contains.';
    }

    #[Override]
    protected function isNegated(): bool {
        return true;
    }
}
