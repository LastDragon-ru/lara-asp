<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as OperatorContract;

interface Operator extends OperatorContract {
    public function getFieldType(TypeProvider $provider, string $type): ?string;
}
