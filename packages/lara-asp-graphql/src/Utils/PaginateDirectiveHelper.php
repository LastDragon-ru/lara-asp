<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Pagination\PaginationType;

/**
 * @internal
 */
abstract class PaginateDirectiveHelper extends PaginateDirective {
    public static function getPaginationType(PaginateDirective $directive): PaginationType {
        return $directive->paginationType();
    }
}
