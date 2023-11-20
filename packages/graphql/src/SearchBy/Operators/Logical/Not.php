<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Override;

class Not extends Logical {
    #[Override]
    public static function getName(): string {
        return 'not';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Not.';
    }

    #[Override]
    protected function getBoolean(): string {
        return 'and not';
    }
}
