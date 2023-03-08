<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use Exception;
use GraphQL\Language\AST\DirectiveNode;
use Hamcrest\Core\IsNot;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeUnknown;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQL\Builder\Operators
 */
class OperatorsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testHasOperators(): void {
        $operators = new class() extends Operators {
            /**
             * @inheritDoc
             */
            protected array $operators = [
                Operators::Int => [
                    OperatorsTest__OperatorA::class,
                ],
            ];

            public function getScope(): string {
                return __METHOD__;
            }
        };

        self::assertTrue($operators->hasOperators(Operators::Int));
        self::assertFalse($operators->hasOperators('unknown'));
    }

    /**
     * @dataProvider dataProviderSetOperators
     *
     * @param array<class-string<Operator>|string> $typeOperators
     */
    public function testSetOperators(Exception|bool $expected, string $type, array $typeOperators): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $operators = new class() extends Operators {
            public function getScope(): string {
                return __METHOD__;
            }
        };

        $operators->setOperators($type, $typeOperators);

        self::assertEquals($expected, $operators->hasOperators($type));
    }

    public function testGetOperators(): void {
        $type      = __FUNCTION__;
        $alias     = 'alias';
        $operators = new class() extends Operators {
            public function getScope(): string {
                return __METHOD__;
            }
        };

        $operators->setOperators($type, [
            OperatorsTest__OperatorA::class,
            OperatorsTest__OperatorA::class,
        ]);
        $operators->setOperators($alias, [$type]);
        $operators->setOperators(Operators::Null, [
            OperatorsTest__OperatorB::class,
            OperatorsTest__OperatorC::class,
        ]);

        self::assertEquals(
            [OperatorsTest__OperatorA::class],
            $this->toClassNames($operators->getOperators($type, false)),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorB::class,
                OperatorsTest__OperatorC::class,
            ],
            $this->toClassNames($operators->getOperators($type, true)),
        );
        self::assertEquals(
            $operators->getOperators($type, false),
            $operators->getOperators($alias, false),
        );
        self::assertEquals(
            $operators->getOperators($type, true),
            $operators->getOperators($alias, true),
        );
    }

    public function testGetOperatorsUnknownType(): void {
        $operators = new class() extends Operators {
            public function getScope(): string {
                return __METHOD__;
            }
        };

        self::expectExceptionObject(new TypeUnknown($operators->getScope(), 'unknown'));

        $operators->getOperators('unknown', false);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderSetOperators(): array {
        return [
            'ok'              => [true, 'scalar', [IsNot::class]],
            'unknown scalar'  => [
                true,
                'scalar',
                ['unknown'],
            ],
            'empty operators' => [
                false,
                'scalar',
                [],
            ],
        ];
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @param array<object> $objects
     *
     * @return array<class-string>
     */
    protected function toClassNames(array $objects): array {
        $classes = [];

        foreach ($objects as $object) {
            $classes[] = $object::class;
        }

        return $classes;
    }
    // </editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
abstract class OperatorsTest__Operator implements Operator {
    public static function definition(): string {
        throw new Exception('Should not be called');
    }

    public static function getName(): string {
        throw new Exception('Should not be called');
    }

    public static function getDirectiveName(): string {
        throw new Exception('Should not be called');
    }

    public function getFieldType(TypeProvider $provider, TypeSource $source): string {
        throw new Exception('Should not be called');
    }

    public function getFieldDescription(): string {
        throw new Exception('Should not be called');
    }

    public function getFieldDirective(): ?DirectiveNode {
        throw new Exception('Should not be called');
    }

    public function isBuilderSupported(object $builder): bool {
        throw new Exception('Should not be called');
    }

    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        throw new Exception('Should not be called');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class OperatorsTest__OperatorA extends OperatorsTest__Operator {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class OperatorsTest__OperatorB extends OperatorsTest__Operator {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class OperatorsTest__OperatorC extends OperatorsTest__Operator {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class OperatorsTest__OperatorD extends OperatorsTest__Operator {
    // empty
}
