<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils\Directives;

use GraphQL\Type\Definition\PhpEnumType;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\Utils\PhpEnumTypeHelper;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
#[CoversClass(AsEnum::class)]
final class AsEnumTest extends TestCase {
    public function testResolveNode(): void {
        $class = AsEnumTest_Enum::class;
        $enum  = json_encode($class, JSON_THROW_ON_ERROR);
        $name  = 'TestEnum';

        $this->useGraphQLSchema(
            <<<GRAPHQL
            scalar {$name} @laraAspAsEnum(class: {$enum})
            GRAPHQL,
        );

        $registry = Container::getInstance()->make(TypeRegistry::class);
        $type     = $registry->get($name);

        self::assertInstanceOf(PhpEnumType::class, $type);
        self::assertEquals($name, $type->name());
        self::assertEquals($class, PhpEnumTypeHelper::getEnumClass($type));
    }

    public function testResolveNodeNotEnum(): void {
        $class = stdClass::class;
        $enum  = json_encode($class, JSON_THROW_ON_ERROR);
        $name  = 'TestEnum';

        $this->useGraphQLSchema(
            <<<GRAPHQL
            scalar {$name} @laraAspAsEnum(class: {$enum})
            GRAPHQL,
        );

        $registry = Container::getInstance()->make(TypeRegistry::class);

        self::expectException(DefinitionException::class);
        self::expectExceptionMessage("The `{$class}` is not an enum.");

        $registry->get($name);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum AsEnumTest_Enum {
    case A;
}
