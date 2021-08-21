<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Helpers;

use GraphQL\Type\Definition\EnumType;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\Core\Enum;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionMethod;

use function trim;

class EnumHelper extends Enum {
    /**
     * Converts {@link \LastDragon_ru\LaraASP\Core\Enum} into GraphQL enum that
     * can be reqistered in {@link \Nuwave\Lighthouse\Schema\TypeRegistry}.
     *
     * @see https://lighthouse-php.com/master/the-basics/types.html#enum
     *
     * @param class-string<Enum> $enum
     */
    public static function getType(string $enum, ?string $name = null): EnumType {
        $class      = new ReflectionClass($enum);
        $definition = [
            'name'        => $name ?: $class->getShortName(),
            'description' => static::description($class),
            'values'      => [],
        ];

        $enum::lookup(static function (ReflectionMethod $method) use (&$definition): void {
            $definition['values'][Str::studly($method->getName())] = [
                'value'       => $method->invoke(null)->getValue(),
                'description' => static::description($method),
            ];
        });

        return new EnumType($definition);
    }

    protected static function description(ReflectionClass|ReflectionMethod $object): ?string {
        $desc = null;

        if ($object->getDocComment()) {
            $doc  = DocBlockFactory::createInstance()->create($object);
            $desc = trim("{$doc->getSummary()}\n\n{$doc->getDescription()}") ?: null;
        }

        return $desc;
    }
}
