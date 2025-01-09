<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\Type;
use UnitEnum;

readonly class TypeReference {
    /**
     * @param class-string<(Type&NamedType)|UnitEnum> $type
     */
    public function __construct(
        public string $name,
        public string $type,
    ) {
        // empty
    }
}
