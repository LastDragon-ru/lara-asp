<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Traits\ScoutSupport;
use Override;

class Field extends BaseOperator {
    use HandlerOperator;
    use ScoutSupport;

    #[Override]
    public static function getName(): string {
        return 'field';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Field condition.';
    }
}
