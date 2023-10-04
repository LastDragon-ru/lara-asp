<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Contracts;

use Nuwave\Lighthouse\Execution\ResolveInfo;

/**
 * @template-covariant TValue
 */
interface FieldArgumentDirective {
    /**
     * @return TValue
     */
    public function getFieldArgumentValue(ResolveInfo $info, mixed $value): mixed;
}
