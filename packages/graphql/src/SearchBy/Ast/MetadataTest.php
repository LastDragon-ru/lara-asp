<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use Exception;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use Hamcrest\Core\IsNot;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Builder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ScalarNoOperators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ScalarUnknown;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNotNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\Utils\Property;
use Mockery;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Metadata
 */
class MetadataTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::isScalar
     */
    public function testIsScalar(): void {
        $metadata = new Metadata($this->app);

        self::assertTrue($metadata->isScalar(Directive::ScalarInt));
        self::assertFalse($metadata->isScalar('unknown'));
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

        $metadata = new Metadata($this->app);

        $metadata->addScalar($scalar, $operators);

        self::assertEquals($expected, $metadata->isScalar($scalar));
    }

    /**
     * @covers ::getScalarOperators
     */
    public function testGetScalarOperators(): void {
        $scalar   = __FUNCTION__;
        $alias    = 'alias';
        $metadata = new Metadata($this->app);

        $metadata->addScalar($scalar, [Equal::class, Equal::class]);
        $metadata->addScalar($alias, $scalar);

        self::assertEquals(
            [Equal::class],
            $this->toClassNames($metadata->getScalarOperators($scalar, false)),
        );
        self::assertEquals(
            [Equal::class, IsNull::class, IsNotNull::class],
            $this->toClassNames($metadata->getScalarOperators($scalar, true)),
        );
        self::assertEquals(
            $metadata->getScalarOperators($scalar, false),
            $metadata->getScalarOperators($alias, false),
        );
        self::assertEquals(
            $metadata->getScalarOperators($scalar, true),
            $metadata->getScalarOperators($alias, true),
        );
    }

    /**
     * @covers ::getScalarOperators
     */
    public function testGetScalarOperatorsUnknownScalar(): void {
        self::expectExceptionObject(new ScalarUnknown('unknown'));

        (new Metadata($this->app))->getScalarOperators('unknown', false);
    }

    /**
     * @covers ::getEnumOperators
     */
    public function testGetEnumOperators(): void {
        $enum     = __FUNCTION__;
        $alias    = 'alias';
        $metadata = new Metadata($this->app);

        $metadata->addScalar($enum, [Equal::class, Equal::class]);
        $metadata->addScalar($alias, $enum);
        $metadata->addScalar(Directive::ScalarEnum, [NotEqual::class, NotEqual::class]);

        self::assertEquals(
            [NotEqual::class],
            $this->toClassNames($metadata->getEnumOperators('unknown', false)),
        );
        self::assertEquals(
            [NotEqual::class, IsNull::class, IsNotNull::class],
            $this->toClassNames($metadata->getEnumOperators('unknown', true)),
        );
        self::assertEquals(
            [Equal::class],
            $this->toClassNames($metadata->getEnumOperators($enum, false)),
        );
        self::assertEquals(
            [Equal::class, IsNull::class, IsNotNull::class],
            $this->toClassNames($metadata->getEnumOperators($enum, true)),
        );
        self::assertEquals(
            $metadata->getEnumOperators($enum, false),
            $metadata->getEnumOperators($alias, false),
        );
        self::assertEquals(
            $metadata->getEnumOperators($enum, true),
            $metadata->getEnumOperators($alias, true),
        );
    }

    /**
     * @covers ::getOperatorInstance
     */
    public function testGetOperatorInstance(): void {
        $operator = new class() implements Operator {
            public static function getName(): string {
                return '';
            }

            public static function definition(): string {
                return '';
            }

            public static function getDirectiveName(): string {
                return '';
            }

            public function getFieldType(TypeProvider $provider, string $type): ?string {
                return null;
            }

            public function getFieldDescription(): string {
                return '';
            }

            public function getFieldDirective(): ?DirectiveNode {
                return null;
            }

            public function isBuilderSupported(object $builder): bool {
                return false;
            }

            public function call(Builder $search, object $builder, Property $property, Argument $argument): object {
                return $builder;
            }
        };
        $metadata = Mockery::mock(Metadata::class, [$this->app]);
        $metadata->makePartial();

        $a = $metadata->getOperatorInstance($operator::class);
        $b = $metadata->getOperatorInstance($operator::class);

        self::assertSame($a, $b);
    }

    /**
     * @covers ::getComplexOperatorInstance
     */
    public function testGetComplexOperatorInstance(): void {
        $operator  = new class() implements ComplexOperator {
            public static function getName(): string {
                return '';
            }

            public static function definition(): string {
                return '';
            }

            public function getDefinition(
                Manipulator $ast,
                InputValueDefinitionNode|InputObjectField $field,
                InputObjectTypeDefinitionNode|InputObjectType $type,
                string $name,
                bool $nullable,
            ): InputObjectTypeDefinitionNode {
                throw new Exception();
            }

            public static function getDirectiveName(): string {
                return '';
            }

            public function getFieldType(TypeProvider $provider, string $type): ?string {
                return null;
            }

            public function getFieldDescription(): string {
                return '';
            }

            public function getFieldDirective(): ?DirectiveNode {
                return null;
            }

            public function isBuilderSupported(object $builder): bool {
                return false;
            }

            public function call(Builder $search, object $builder, Property $property, Argument $argument): object {
                return $builder;
            }
        };
        $metadata  = $this->app->make(Metadata::class);
        $operatorA = $metadata->getComplexOperatorInstance($operator::class);
        $operatorB = $metadata->getComplexOperatorInstance($operator::class);

        self::assertSame($operatorA, $operatorB);
    }

    /**
     * @covers ::getDefinition
     */
    public function testGetDefinition(): void {
        $definition = new class() implements TypeDefinition {
            public function getTypeDefinitionNode(
                string $name,
                string $scalar = null,
                bool $nullable = null,
            ): ?TypeDefinitionNode {
                return null;
            }

            public static function getName(): string {
                return 'Test';
            }
        };

        $metadata    = $this->app->make(Metadata::class);
        $definitionA = $metadata->getDefinition($definition::class);
        $definitionB = $metadata->getDefinition($definition::class);

        self::assertSame($definitionA, $definitionB);
    }

    /**
     * @covers ::addType
     * @covers ::getType
     */
    public function testGetType(): void {
        $metadata = new Metadata($this->app);

        self::assertNull($metadata->getType('test'));

        $metadata->addType('test', 'TestType');

        self::assertEquals('TestType', $metadata->getType('test'));

        $metadata->addType('test', 'TestType2');

        self::assertEquals('TestType2', $metadata->getType('test'));
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
