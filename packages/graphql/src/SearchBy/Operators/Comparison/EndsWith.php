<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Override;

class EndsWith extends Contains {
    #[Override]
    public static function getName(): string {
        return 'endsWith';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Ends with a string.';
    }

    #[Override]
    protected function value(string $value): string {
        return "%{$value}";
    }
}
