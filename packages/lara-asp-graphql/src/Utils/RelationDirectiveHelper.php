<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use Nuwave\Lighthouse\Pagination\PaginationType;
use Nuwave\Lighthouse\Schema\Directives\RelationDirective;

/**
 * @internal
 */
abstract class RelationDirectiveHelper extends RelationDirective {
    public static function getPaginationType(RelationDirective $directive): ?PaginationType {
        return $directive->paginationType();
    }
}
