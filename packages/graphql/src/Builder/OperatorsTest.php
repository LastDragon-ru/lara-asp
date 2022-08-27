<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use Exception;
use GraphQL\Language\AST\DirectiveNode;
use Hamcrest\Core\IsNot;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeNoOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeUnknown;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\Builder\Operators
 */
class OperatorsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::hasOperators
     */
    public function testHasOperators(): void {
        $operators = new class($this->app) extends Operators {
            /**
             * @inheritdoc
             */
            protected array $operators = [
                Operators::Int => [
                    OperatorsTest__OperatorA::class,
                ],
            ];
        };

        self::assertTrue($operators->hasOperators(Operators::Int));
        self::assertFalse($operators->hasOperators('unknown'));
    }

    /**
     * @covers ::addOperators
     *
     * @dataProvider dataProviderAddOperators
     */
    public function testAddOperators(Exception|bool $expected, string $type, mixed $typeOperators): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $operators = new class($this->app) extends Operators {
            // empty
        };

        $operators->addOperators($type, $typeOperators);

        self::assertEquals($expected, $operators->hasOperators($type));
    }

    /**
     * @covers ::getOperators
     */
    public function testGetOperators(): void {
        $type      = __FUNCTION__;
        $alias     = 'alias';
        $operators = new class($this->app) extends Operators {
            // empty
        };

        $operators->addOperators($type, [
            OperatorsTest__OperatorA::class,
            OperatorsTest__OperatorA::class,
        ]);
        $operators->addOperators($alias, $type);
        $operators->addOperators(Operators::Null, [
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

    /**
     * @covers ::getOperators
     */
    public function testGetOperatorsExtends(): void {
        $operators = new class($this->app) extends Operators {
            /**
             * @inheritdoc
             */
            protected array $extends = [
                'test' => 'base',
            ];
        };

        $operators->addOperators('test', [
            OperatorsTest__OperatorA::class,
            OperatorsTest__OperatorA::class,
        ]);
        $operators->addOperators('base', [
            OperatorsTest__OperatorD::class,
        ]);
        $operators->addOperators('alias', 'test');
        $operators->addOperators(Operators::Null, [
            OperatorsTest__OperatorB::class,
            OperatorsTest__OperatorC::class,
        ]);

        self::assertEquals(
            [OperatorsTest__OperatorA::class, OperatorsTest__OperatorD::class],
            $this->toClassNames($operators->getOperators('test', false)),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorD::class,
                OperatorsTest__OperatorB::class,
                OperatorsTest__OperatorC::class,
            ],
            $this->toClassNames($operators->getOperators('test', true)),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorD::class,
                OperatorsTest__OperatorB::class,
                OperatorsTest__OperatorC::class,
            ],
            $this->toClassNames($operators->getOperators('alias', true)),
        );
    }

    /**
     * @covers ::getOperators
     */
    public function testGetOperatorsUnknownType(): void {
        self::expectExceptionObject(new TypeUnknown('unknown'));

        $operators = new class($this->app) extends Operators {
            // empty
        };

        $operators->getOperators('unknown', false);
    }

    /**
     * @covers ::getEnumOperators
     */
    public function testGetEnumOperators(): void {
        $enum      = __FUNCTION__;
        $alias     = 'alias';
        $operators = new class($this->app) extends Operators {
            // empty
        };

        $operators->addOperators($enum, [
            OperatorsTest__OperatorA::class,
            OperatorsTest__OperatorA::class,
        ]);
        $operators->addOperators($alias, $enum);
        $operators->addOperators(Operators::Enum, [
            OperatorsTest__OperatorD::class,
            OperatorsTest__OperatorD::class,
        ]);
        $operators->addOperators(Operators::Null, [
            OperatorsTest__OperatorB::class,
            OperatorsTest__OperatorC::class,
        ]);

        self::assertEquals(
            [
                OperatorsTest__OperatorD::class,
            ],
            $this->toClassNames($operators->getEnumOperators('unknown', false)),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorD::class,
                OperatorsTest__OperatorB::class,
                OperatorsTest__OperatorC::class,
            ],
            $this->toClassNames($operators->getEnumOperators('unknown', true)),
        );
        self::assertEquals(
            [OperatorsTest__OperatorA::class],
            $this->toClassNames($operators->getEnumOperators($enum, false)),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorB::class,
                OperatorsTest__OperatorC::class,
            ],
            $this->toClassNames($operators->getEnumOperators($enum, true)),
        );
        self::assertEquals(
            $operators->getEnumOperators($enum, false),
            $operators->getEnumOperators($alias, false),
        );
        self::assertEquals(
            $operators->getEnumOperators($enum, true),
            $operators->getEnumOperators($alias, true),
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderAddOperators(): array {
        return [
            'ok'              => [true, 'scalar', [IsNot::class]],
            'unknown scalar'  => [
                new TypeUnknown('unknown'),
                'scalar',
                'unknown',
            ],
            'empty operators' => [
                new TypeNoOperators('scalar'),
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
class OperatorsTest__Operators extends Operators {
    // empty
}

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

    public function getFieldType(TypeProvider $provider, string $type): ?string {
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
