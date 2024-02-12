<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Type\Definition\CustomScalarType;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextBuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context as ContextContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorDirective;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorsDirective;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Schema\AST\ASTBuilder;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function array_map;
use function is_a;

/**
 * @internal
 */
#[CoversClass(Manipulator::class)]
final class ManipulatorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testGetTypeOperators(): void {
        // Operators
        $scope     = new class() implements Scope {
            // empty;
        };
        $builder   = new stdClass();
        $aOperator = ManipulatorTest_OperatorA::class;
        $bOperator = ManipulatorTest_OperatorB::class;
        $cOperator = ManipulatorTest_OperatorC::class;

        // Types
        $types = Container::getInstance()->make(TypeRegistry::class);

        $types->register(
            new CustomScalarType([
                'name' => 'TestScalar',
            ]),
        );
        $types->register(
            new CustomScalarType([
                'name' => 'TestOperators',
            ]),
        );
        $types->register(
            new CustomScalarType([
                'name' => 'TestBuiltinOperators',
            ]),
        );

        // Directives
        $directives = Container::getInstance()->make(DirectiveLocator::class);

        $directives->setResolved('operators', ManipulatorTest_Operators::class);
        $directives->setResolved('aOperator', $aOperator);
        $directives->setResolved('bOperator', $bOperator);
        $directives->setResolved('cOperator', $cOperator);

        // Schema
        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            scalar TestScalar
            @aOperator
            @bOperator
            @cOperator

            scalar TestOperators
            @operators(type: "TestScalar")

            scalar TestBuiltinOperators
            @operators(type: "TestBuiltinOperators")
            @aOperator

            type Query {
                test: Int @all
            }
            GRAPHQL,
        );

        // Operators
        $config    = [];
        $default   = [
            Operators::ID          => [
                $aOperator,
                $bOperator,
            ],
            Operators::Int         => [
                $bOperator,
                $cOperator,
            ],
            'TestBuiltinOperators' => [
                $cOperator,
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

        // Manipulator
        $source      = Mockery::mock(TypeSource::class);
        $context     = (new Context())->override([
            HandlerContextBuilderInfo::class => new HandlerContextBuilderInfo(
                new BuilderInfo($builder::class, $builder::class),
            ),
        ]);
        $document    = Container::getInstance()->make(ASTBuilder::class)->documentAST();
        $manipulator = Container::getInstance()->make(Manipulator::class, [
            'document' => $document,
        ]);

        $manipulator->addOperators($operators);

        // Test
        $map = static function (Operator $operator): string {
            return $operator::class;
        };

        self::assertEquals(
            [
                $aOperator,
            ],
            array_map($map, $manipulator->getTypeOperators($operators->getScope(), $source, $context, Operators::ID)),
        );
        self::assertEquals(
            [
                $aOperator,
                $cOperator,
            ],
            array_map(
                $map,
                $manipulator->getTypeOperators(
                    $operators->getScope(),
                    $source,
                    $context,
                    Operators::ID,
                    Operators::Int,
                ),
            ),
        );
        self::assertEquals(
            [
                // empty (another scope)
            ],
            array_map($map, $manipulator->getTypeOperators($scope::class, $source, $context, Operators::ID)),
        );
        self::assertEquals(
            [
                $aOperator,
            ],
            array_map($map, $manipulator->getTypeOperators($operators->getScope(), $source, $context, 'TestScalar')),
        );
        self::assertEquals(
            [
                $aOperator,
            ],
            array_map($map, $manipulator->getTypeOperators($operators->getScope(), $source, $context, 'TestOperators')),
        );
        self::assertEquals(
            [
                $cOperator,
                $aOperator,
            ],
            array_map(
                $map,
                $manipulator->getTypeOperators($operators->getScope(), $source, $context, 'TestBuiltinOperators'),
            ),
        );
        self::assertEquals(
            [
                // empty
            ],
            array_map(
                $map,
                $manipulator->getTypeOperators($operators->getScope(), $source, $context, 'Unknown', Operators::ID),
            ),
        );
    }
    // </editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ManipulatorTest_Operators extends OperatorsDirective implements Scope {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ManipulatorTest_OperatorA extends OperatorDirective implements Operator, Scope {
    #[Override]
    public static function getName(): string {
        return 'a';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, ContextContract $context): ?string {
        return $source->getTypeName();
    }

    #[Override]
    public function getFieldDescription(): ?string {
        return '';
    }

    #[Override]
    protected function isBuilderSupported(string $builder): bool {
        return is_a($builder, stdClass::class, true);
    }

    #[Override]
    public function call(
        Handler $handler,
        object $builder,
        Field $field,
        Argument $argument,
        ContextContract $context,
    ): object {
        return $builder;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ManipulatorTest_OperatorB extends OperatorDirective implements Operator {
    #[Override]
    public static function getName(): string {
        return 'b';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, ContextContract $context): ?string {
        return $source->getTypeName();
    }

    #[Override]
    public function getFieldDescription(): ?string {
        return '';
    }

    #[Override]
    protected function isBuilderSupported(string $builder): bool {
        return false;
    }

    #[Override]
    public function call(
        Handler $handler,
        object $builder,
        Field $field,
        Argument $argument,
        ContextContract $context,
    ): object {
        return $builder;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ManipulatorTest_OperatorC extends OperatorDirective implements Operator {
    #[Override]
    public static function getName(): string {
        return 'c';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, ContextContract $context): ?string {
        return $source->getTypeName();
    }

    #[Override]
    public function getFieldDescription(): ?string {
        return '';
    }

    #[Override]
    protected function isBuilderSupported(string $builder): bool {
        return is_a($builder, stdClass::class, true);
    }

    #[Override]
    public function call(
        Handler $handler,
        object $builder,
        Field $field,
        Argument $argument,
        ContextContract $context,
    ): object {
        return $builder;
    }
}
