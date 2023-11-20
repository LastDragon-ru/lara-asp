<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Override;

class NotEndsWith extends EndsWith {
    #[Override]
    public static function getName(): string {
        return 'notEndsWith';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Not ends with a string.';
    }

    #[Override]
    protected function isNegated(): bool {
        return true;
    }
}
