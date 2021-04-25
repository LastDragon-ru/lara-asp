<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use Exception;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Hamcrest\Core\IsNot;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinitionProvider;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNotNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchByException;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;
use stdClass;

use function array_map;
use function sprintf;

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

        $this->assertTrue($metadata->isScalar(Directive::ScalarInt));
        $this->assertFalse($metadata->isScalar('unknown'));
    }

    /**
     * @covers ::addScalar
     *
     * @dataProvider dataProviderAddScalar
     */
    public function testAddScalar(Exception|bool $expected, string $scalar, mixed $operators): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $metadata = new Metadata($this->app);

        $metadata->addScalar($scalar, $operators);

        $this->assertEquals($expected, $metadata->isScalar($scalar));
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

        $this->assertEquals(
            [Equal::class],
            $this->toClassNames($metadata->getScalarOperators($scalar, false)),
        );
        $this->assertEquals(
            [Equal::class, IsNull::class, IsNotNull::class],
            $this->toClassNames($metadata->getScalarOperators($scalar, true)),
        );
        $this->assertEquals(
            $metadata->getScalarOperators($scalar, false),
            $metadata->getScalarOperators($alias, false),
        );
        $this->assertEquals(
            $metadata->getScalarOperators($scalar, true),
            $metadata->getScalarOperators($alias, true),
        );
    }

    /**
     * @covers ::getScalarOperators
     */
    public function testGetScalarOperatorsUnknownScalar(): void {
        $this->expectExceptionObject(new SearchByException(sprintf(
            'Scalar `%s` is not defined.',
            'unknown',
        )));

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

        $this->assertEquals(
            [NotEqual::class],
            $this->toClassNames($metadata->getEnumOperators('unknown', false)),
        );
        $this->assertEquals(
            [NotEqual::class, IsNull::class, IsNotNull::class],
            $this->toClassNames($metadata->getEnumOperators('unknown', true)),
        );
        $this->assertEquals(
            [Equal::class],
            $this->toClassNames($metadata->getEnumOperators($enum, false)),
        );
        $this->assertEquals(
            [Equal::class, IsNull::class, IsNotNull::class],
            $this->toClassNames($metadata->getEnumOperators($enum, true)),
        );
        $this->assertEquals(
            $metadata->getEnumOperators($enum, false),
            $metadata->getEnumOperators($alias, false),
        );
        $this->assertEquals(
            $metadata->getEnumOperators($enum, true),
            $metadata->getEnumOperators($alias, true),
        );
    }

    /**
     * @covers ::getOperatorInstance
     */
    public function testGetOperatorInstance(): void {
        $operator = new class() implements Operator, TypeDefinitionProvider {
            public function getName(): string {
                return '';
            }

            public function getDefinition(TypeProvider $provider, string $scalar, bool $nullable): string {
                return '';
            }

            /**
             * @inheritDoc
             */
            public function getDefinitions(): array {
                return [];
            }
        };
        $metadata = Mockery::mock(Metadata::class, [$this->app]);
        $metadata->makePartial();
        $metadata
            ->shouldReceive('addDefinitions')
            ->once();

        $a = $metadata->getOperatorInstance($operator::class);
        $b = $metadata->getOperatorInstance($operator::class);

        $this->assertNotNull($a);
        $this->assertSame($a, $b);
    }

    /**
     * @covers ::getOperatorInstance
     */
    public function testGetOperatorInstanceNotOperator(): void {
        $this->expectExceptionObject(new SearchByException(sprintf(
            'Operator `%s` must implement `%s`.',
            stdClass::class,
            Operator::class,
        )));

        (new Metadata($this->app))->getOperatorInstance(stdClass::class);
    }

    /**
     * @covers ::getComplexOperatorInstance
     */
    public function testComplexOperatorInstance(): void {
        $operator = new class() implements ComplexOperator, TypeDefinitionProvider {
            /**
             * @inheritDoc
             */
            public function getDefinitions(): array {
                return [];
            }

            public function getName(): string {
                return '';
            }

            public function getDefinition(
                Manipulator $ast,
                InputValueDefinitionNode $field,
                InputObjectTypeDefinitionNode $type,
                string $name,
                bool $nullable,
            ): TypeDefinitionNode {
                throw new Exception();
            }

            /**
             * @inheritDoc
             */
            public function apply(
                SearchBuilder $search,
                EloquentBuilder|QueryBuilder $builder,
                string $property,
                array $conditions,
            ): EloquentBuilder|QueryBuilder {
                return $builder;
            }
        };
        $metadata = Mockery::mock(Metadata::class, [$this->app]);
        $metadata->makePartial();
        $metadata
            ->shouldReceive('addDefinitions')
            ->once();

        $a = $metadata->getComplexOperatorInstance($operator::class);
        $b = $metadata->getComplexOperatorInstance($operator::class);

        $this->assertNotNull($a);
        $this->assertSame($a, $b);
    }

    /**
     * @covers ::getComplexOperatorInstance
     */
    public function testGetComplexOperatorInstance(): void {
        $this->expectExceptionObject(new SearchByException(sprintf(
            'Operator `%s` must implement `%s`.',
            stdClass::class,
            ComplexOperator::class,
        )));

        (new Metadata($this->app))->getComplexOperatorInstance(stdClass::class);
    }

    /**
     * @covers ::addDefinitions
     */
    public function testAddDefinitions(): void {
        $provider = Mockery::mock(TypeDefinitionProvider::class);
        $provider
            ->shouldReceive('getDefinitions')
            ->once()
            ->andReturn([
                'a' => Mockery::mock(TypeDefinition::class)::class,
                'b' => Mockery::mock(TypeDefinition::class)::class,
            ]);
        $metadata = Mockery::mock(Metadata::class);
        $metadata->makePartial();
        $metadata
            ->shouldReceive('addDefinition')
            ->twice();

        $metadata->addDefinitions($provider);

        $this->assertTrue(true);
    }

    /**
     * @covers ::addDefinition
     */
    public function testAddDefinition(): void {
        $metadata   = new Metadata($this->app);
        $definition = Mockery::mock(TypeDefinition::class);

        $metadata->addDefinition('test', $definition::class);

        // The second call must be fine, because definition the same
        $metadata->addDefinition('test', $definition::class);

        $this->assertTrue(true);
    }

    /**
     * @covers ::addDefinition
     */
    public function testAddDefinitionNotADefinition(): void {
        $this->expectExceptionObject(new SearchByException(sprintf(
            'Definition `%s` must implement `%s`.',
            stdClass::class,
            TypeDefinition::class,
        )));

        (new Metadata($this->app))->addDefinition('type', stdClass::class);
    }

    /**
     * @covers ::addDefinition
     */
    public function testAddDefinitionOverride(): void {
        $metadata = new Metadata($this->app);
        $a        = new class() implements TypeDefinition {
            public function get(string $name, string $scalar = null, bool $nullable = null): ?TypeDefinitionNode {
                return null;
            }
        };
        $b        = new class() implements TypeDefinition {
            public function get(string $name, string $scalar = null, bool $nullable = null): ?TypeDefinitionNode {
                return null;
            }
        };

        $this->expectExceptionObject(new SearchByException(sprintf(
            'Definition `%s` already defined.',
            'test',
        )));

        $metadata->addDefinition('test', $a::class);
        $metadata->addDefinition('test', $b::class);
    }

    /**
     * @covers ::getDefinition
     */
    public function testGetDefinition(): void {
        $metadata   = new Metadata($this->app);
        $definition = Mockery::mock(TypeDefinition::class);

        $metadata->addDefinition('test', $definition::class);

        $actual = $metadata->getDefinition('test');

        $this->assertInstanceOf(Definition::class, $actual);
        $this->assertInstanceOf(TypeDefinition::class, $actual);
        $this->assertEquals($definition::class, $actual->getDefinition());
    }

    /**
     * @covers ::getDefinition
     */
    public function testGetDefinitionUnknownDefinition(): void {
        $this->expectExceptionObject(new SearchByException(sprintf(
            'Definition `%s` is not defined.',
            'unknown',
        )));

        (new Metadata($this->app))->getDefinition('unknown');
    }

    /**
     * @covers ::addType
     * @covers ::getType
     */
    public function testGetType(): void {
        $metadata = new Metadata($this->app);

        $this->assertNull($metadata->getType('test'));

        $metadata->addType('test', 'TestType');

        $this->assertEquals('TestType', $metadata->getType('test'));

        $metadata->addType('test', 'TestType2');

        $this->assertEquals('TestType2', $metadata->getType('test'));
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
                new SearchByException(sprintf(
                    'Scalar `%s` is not defined.',
                    'unknown',
                )),
                'scalar',
                'unknown',
            ],
            'empty operators' => [
                new SearchByException(sprintf(
                    'Operator list for scalar `%s` cannot be empty.',
                    'scalar',
                )),
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
        return array_map(static function (object $object): string {
            return $object::class;
        }, $objects);
    }
    // </editor-fold>
}
