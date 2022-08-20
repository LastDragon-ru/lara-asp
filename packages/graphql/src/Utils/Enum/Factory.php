<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils\Enum;

use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\Core\Enum;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionMethod;

use function trim;

/**
 * @internal {@see \LastDragon_ru\LaraASP\GraphQL\Utils\Enum\EnumType}
 * @deprecated Please use native PHP enums
 */
class Factory extends Enum {
    /**
     * Converts {@see Enum} into config definition for {@see \GraphQL\Type\Definition\EnumType}.
     *
     * @param class-string<Enum> $enum
     *
     * @return array<string,mixed>
     */
    public static function getDefinition(string $enum, ?string $name = null): array {
        $class      = new ReflectionClass($enum);
        $definition = [
            'name'        => $name ?: $class->getShortName(),
            'description' => static::description($class),
            'values'      => [],
        ];

        $enum::lookup(static function (ReflectionMethod $method) use (&$definition): void {
            $definition['values'][Str::studly($method->getName())] = [
                'value'       => $method->invoke(null),
                'description' => static::description($method),
            ];
        });

        return $definition;
    }

    /**
     * @param ReflectionClass<object>|ReflectionMethod $object
     */
    protected static function description(ReflectionClass|ReflectionMethod $object): ?string {
        $desc = null;

        if ($object->getDocComment()) {
            $doc  = DocBlockFactory::createInstance()->create($object);
            $desc = trim("{$doc->getSummary()}\n\n{$doc->getDescription()}") ?: null;
        }

        return $desc;
    }
}
