<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils\Enum;

use GraphQL\Type\Definition\EnumType as GraphQLEnumType;
use LastDragon_ru\LaraASP\Core\Enum;

/**
 * Special wrapper for {@see Enum} that can be registered in the
 * {@see \Nuwave\Lighthouse\Schema\TypeRegistry}.
 *
 * @see https://lighthouse-php.com/master/the-basics/types.html#enum
 */
class EnumType extends GraphQLEnumType {
    /**
     * @param class-string<Enum> $enum
     */
    public function __construct(
        protected string $enum,
        ?string $name = null,
    ) {
        parent::__construct(Factory::getDefinition($this->enum, $name));
    }

    public function serialize(mixed $value): string {
        return $value instanceof Enum ? (string) $value : parent::serialize($value);
    }
}