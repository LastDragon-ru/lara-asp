<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use Exception;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeUnknown;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Operators::class)]
final class OperatorsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testHasOperators(): void {
        $operators = new class() extends Operators {
            /**
             * @inheritDoc
             */
            protected array $default = [
                Operators::Int => [
                    OperatorsTest__OperatorA::class,
                ],
            ];

            #[Override]
            public function getScope(): string {
                return Scope::class;
            }
        };

        self::assertTrue($operators->hasOperators(Operators::Int));
        self::assertFalse($operators->hasOperators('unknown'));
    }

    public function testGetOperators(): void {
        $config    = [
            'alias'  => [
                'type-a',
            ],
            'type-a' => [
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorA::class,
            ],
            'type-b' => [
                OperatorsTest__OperatorA::class,
                'type-b',
            ],
        ];
        $default   = [
            'type-a' => [
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorB::class,
                OperatorsTest__OperatorC::class,
            ],
            'type-b' => [
                OperatorsTest__OperatorB::class,
                'type-b',
            ],
            'type-c' => [
                OperatorsTest__OperatorC::class,
                'type-b',
                'type-a',
            ],
        ];
        $operators = new class($config, $default) extends Operators {
            /**
             * @param array<string, list<class-string<Operator>|string>> $operators
             * @param array<string, list<class-string<Operator>|string>> $default
             */
            public function __construct(array $operators = [], array $default = []) {
                parent::__construct($operators);

                $this->default = $default;
            }

            #[Override]
            public function getScope(): string {
                return Scope::class;
            }
        };

        self::assertEquals(
            [
                OperatorsTest__OperatorA::class,
            ],
            $this->toClassNames($operators->getOperators('type-a')),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorB::class,
            ],
            $this->toClassNames($operators->getOperators('type-b')),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorC::class,
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorB::class,
            ],
            $this->toClassNames($operators->getOperators('type-c')),
        );
        self::assertEquals(
            $operators->getOperators('type-a'),
            $operators->getOperators('alias'),
        );
    }

    public function testGetOperatorsUnknownType(): void {
        $operators = new class() extends Operators {
            #[Override]
            public function getScope(): string {
                return Scope::class;
            }
        };

        self::expectExceptionObject(new TypeUnknown($operators->getScope(), 'unknown'));

        $operators->getOperators('unknown');
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @param array<array-key, object> $objects
     *
     * @return list<class-string>
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
    #[Override]
    public static function definition(): string {
        throw new Exception('Should not be called');
    }

    #[Override]
    public static function getName(): string {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): ?string {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function getFieldDescription(): string {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function isAvailable(string $builder, Context $context): bool {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function call(
        Handler $handler,
        object $builder,
        Field $field,
        Argument $argument,
        Context $context,
    ): object {
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
