<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\SchemaDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Override;

class Schema extends SchemaDirective {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getScalars(AstManipulator $manipulator): array {
        return Operators::getSchemaScalars($manipulator);
    }
}
