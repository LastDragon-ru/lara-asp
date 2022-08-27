<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use Exception;
use GraphQL\Language\AST\DirectiveNode;
use Hamcrest\Core\IsNot;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\ScalarNoOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\ScalarUnknown;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\Builder\Scalars
 */
class ScalarsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::isScalar
     */
    public function testIsScalar(): void {
        $scalars = new class($this->app) extends Scalars {
            /**
             * @inheritdoc
             */
            protected array $scalars = [
                Scalars::ScalarInt => [
                    ScalarsTest__OperatorA::class,
                ],
            ];
        };

        self::assertTrue($scalars->isScalar(Scalars::ScalarInt));
        self::assertFalse($scalars->isScalar('unknown'));
    }

    /**
     * @covers ::addScalar
     *
     * @dataProvider dataProviderAddScalar
     */
    public function testAddScalar(Exception|bool $expected, string $scalar, mixed $operators): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $scalars = new class($this->app) extends Scalars {
            // empty
        };

        $scalars->addScalar($scalar, $operators);

        self::assertEquals($expected, $scalars->isScalar($scalar));
    }

    /**
     * @covers ::getScalarOperators
     */
    public function testGetScalarOperators(): void {
        $scalar  = __FUNCTION__;
        $alias   = 'alias';
        $scalars = new class($this->app) extends Scalars {
            // empty
        };

        $scalars->addScalar($scalar, [
            ScalarsTest__OperatorA::class,
            ScalarsTest__OperatorA::class,
        ]);
        $scalars->addScalar($alias, $scalar);
        $scalars->addScalar(Scalars::ScalarNull, [
            ScalarsTest__OperatorB::class,
            ScalarsTest__OperatorC::class,
        ]);

        self::assertEquals(
            [ScalarsTest__OperatorA::class],
            $this->toClassNames($scalars->getScalarOperators($scalar, false)),
        );
        self::assertEquals(
            [
                ScalarsTest__OperatorA::class,
                ScalarsTest__OperatorB::class,
                ScalarsTest__OperatorC::class,
            ],
            $this->toClassNames($scalars->getScalarOperators($scalar, true)),
        );
        self::assertEquals(
            $scalars->getScalarOperators($scalar, false),
            $scalars->getScalarOperators($alias, false),
        );
        self::assertEquals(
            $scalars->getScalarOperators($scalar, true),
            $scalars->getScalarOperators($alias, true),
        );
    }

    /**
     * @covers ::getScalarOperators
     */
    public function testGetScalarOperatorsExtends(): void {
        $scalars = new class($this->app) extends Scalars {
            /**
             * @inheritdoc
             */
            protected array $extends = [
                'test' => 'base',
            ];
        };

        $scalars->addScalar('test', [
            ScalarsTest__OperatorA::class,
            ScalarsTest__OperatorA::class,
        ]);
        $scalars->addScalar('base', [
            ScalarsTest__OperatorD::class,
        ]);
        $scalars->addScalar('alias', 'test');
        $scalars->addScalar(Scalars::ScalarNull, [
            ScalarsTest__OperatorB::class,
            ScalarsTest__OperatorC::class,
        ]);

        self::assertEquals(
            [ScalarsTest__OperatorA::class, ScalarsTest__OperatorD::class],
            $this->toClassNames($scalars->getScalarOperators('test', false)),
        );
        self::assertEquals(
            [
                ScalarsTest__OperatorA::class,
                ScalarsTest__OperatorD::class,
                ScalarsTest__OperatorB::class,
                ScalarsTest__OperatorC::class,
            ],
            $this->toClassNames($scalars->getScalarOperators('test', true)),
        );
        self::assertEquals(
            [
                ScalarsTest__OperatorA::class,
                ScalarsTest__OperatorD::class,
                ScalarsTest__OperatorB::class,
                ScalarsTest__OperatorC::class,
            ],
            $this->toClassNames($scalars->getScalarOperators('alias', true)),
        );
    }

    /**
     * @covers ::getScalarOperators
     */
    public function testGetScalarOperatorsUnknownScalar(): void {
        self::expectExceptionObject(new ScalarUnknown('unknown'));

        $scalars = new class($this->app) extends Scalars {
            // empty
        };

        $scalars->getScalarOperators('unknown', false);
    }

    /**
     * @covers ::getEnumOperators
     */
    public function testGetEnumOperators(): void {
        $enum    = __FUNCTION__;
        $alias   = 'alias';
        $scalars = new class($this->app) extends Scalars {
            // empty
        };

        $scalars->addScalar($enum, [
            ScalarsTest__OperatorA::class,
            ScalarsTest__OperatorA::class,
        ]);
        $scalars->addScalar($alias, $enum);
        $scalars->addScalar(Scalars::ScalarEnum, [
            ScalarsTest__OperatorD::class,
            ScalarsTest__OperatorD::class,
        ]);
        $scalars->addScalar(Scalars::ScalarNull, [
            ScalarsTest__OperatorB::class,
            ScalarsTest__OperatorC::class,
        ]);

        self::assertEquals(
            [
                ScalarsTest__OperatorD::class,
            ],
            $this->toClassNames($scalars->getEnumOperators('unknown', false)),
        );
        self::assertEquals(
            [
                ScalarsTest__OperatorD::class,
                ScalarsTest__OperatorB::class,
                ScalarsTest__OperatorC::class,
            ],
            $this->toClassNames($scalars->getEnumOperators('unknown', true)),
        );
        self::assertEquals(
            [ScalarsTest__OperatorA::class],
            $this->toClassNames($scalars->getEnumOperators($enum, false)),
        );
        self::assertEquals(
            [
                ScalarsTest__OperatorA::class,
                ScalarsTest__OperatorB::class,
                ScalarsTest__OperatorC::class,
            ],
            $this->toClassNames($scalars->getEnumOperators($enum, true)),
        );
        self::assertEquals(
            $scalars->getEnumOperators($enum, false),
            $scalars->getEnumOperators($alias, false),
        );
        self::assertEquals(
            $scalars->getEnumOperators($enum, true),
            $scalars->getEnumOperators($alias, true),
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderAddScalar(): array {
        return [
            'ok'              => [true, 'scalar', [IsNot::class]],
            'unknown scalar'  => [
                new ScalarUnknown('unknown'),
                'scalar',
                'unknown',
            ],
            'empty operators' => [
                new ScalarNoOperators('scalar'),
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
class ScalarsTest__Scalars extends Scalars {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
abstract class ScalarsTest__Operator implements Operator {
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
class ScalarsTest__OperatorA extends ScalarsTest__Operator {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ScalarsTest__OperatorB extends ScalarsTest__Operator {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ScalarsTest__OperatorC extends ScalarsTest__Operator {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ScalarsTest__OperatorD extends ScalarsTest__Operator {
    // empty
}
