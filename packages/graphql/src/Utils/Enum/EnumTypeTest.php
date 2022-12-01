<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils\Enum;

use LastDragon_ru\LaraASP\Core\Enum;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Bodies\JsonBody;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Constraint\JsonMatches;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\Utils\Enum\Factory
 */
class EnumTypeTest extends TestCase {
    use MakesGraphQLRequests;

    /**
     * @covers ::getDefinition
     */
    public function testGetDefinition(): void {
        $enum     = new EnumType(EnumTypeTest__Enum::class);
        $registry = $this->app->make(TypeRegistry::class);

        $registry->register($enum);

        $this->mockResolver(static function (mixed $root, array $args): Enum {
            self::assertSame(EnumTypeTest__Enum::a(), $args['a']);

            return EnumTypeTest__Enum::b();
        });

        $this
            ->useGraphQLSchema(
                /** @lang GraphQL */
                <<<'GRAPHQL'
                type Query {
                  test(a: EnumTypeTest__Enum): EnumTypeTest__Enum! @mock
                }
                GRAPHQL,
            )
            ->graphQL(
                /** @lang GraphQL */
                <<<'GRAPHQL'
                query {
                    test(a: A)
                }
                GRAPHQL,
            )
            ->assertThat(new Response(
                new Ok(),
                new JsonContentType(),
                new JsonBody(
                    new JsonMatches(json_encode(
                        [
                            'data' => [
                                'test' => 'B',
                            ],
                        ],
                        JSON_THROW_ON_ERROR,
                    )),
                ),
            ));
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class EnumTypeTest__Enum extends Enum {
    public static function a(): static {
        return static::make(__FUNCTION__);
    }

    public static function b(): static {
        return static::make(__FUNCTION__);
    }
}
