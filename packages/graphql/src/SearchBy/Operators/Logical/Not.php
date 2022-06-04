<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

class Not extends Logical {
    public static function getName(): string {
        return 'not';
    }

    public function getFieldDescription(): string {
        return 'Not.';
    }

    protected function getBoolean(): string {
        return 'and not';
    }
}
