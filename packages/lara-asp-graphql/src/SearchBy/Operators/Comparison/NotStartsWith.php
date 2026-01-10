<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Override;

class NotStartsWith extends StartsWith {
    #[Override]
    public static function getName(): string {
        return 'notStartsWith';
    }

    #[Override]
    public function getFieldDescription(): ?string {
        return 'Not starts with a string.';
    }

    #[Override]
    protected function isNegated(): bool {
        return true;
    }
}
