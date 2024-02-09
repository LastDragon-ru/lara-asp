<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithScoutSupport;
use Override;

class Condition extends Operator {
    use HandlerOperator;
    use WithScoutSupport;

    #[Override]
    public static function getName(): string {
        return 'condition';
    }

    #[Override]
    public function getFieldDescription(): ?string {
        return 'Field condition.';
    }
}
