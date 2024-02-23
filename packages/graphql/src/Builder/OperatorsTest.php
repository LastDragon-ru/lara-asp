<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use Exception;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\ExtendOperatorsDirective;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorsDirective;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Schema\AST\ASTBuilder;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Operators::class)]
final class OperatorsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testGetOperators(): void {
        // Directives
        $directives = Container::getInstance()->make(DirectiveLocator::class);

        $directives->setResolved('ignored', OperatorsTest__Ignored::class);
        $directives->setResolved('operators', OperatorsTest__OperatorsDirective::class);
        $directives->setResolved('extendOperators', OperatorsTest__ExtendOperatorsDirective::class);
        $directives->setResolved('aOperator', OperatorsTest__OperatorA::class);
        $directives->setResolved('bOperator', OperatorsTest__OperatorB::class);
        $directives->setResolved('cOperator', OperatorsTest__OperatorC::class);
        $directives->setResolved('externalOperator', OperatorsTest__OperatorExternal::class);

        // Schema
        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            scalar SchemaTypeA
            @aOperator
            @bOperator
            @cOperator
            @externalOperator

            scalar SchemaTypeIgnored
            @aOperator
            @ignored

            scalar SchemaTypeD
            @operators(type: "TypeD")
            @externalOperator

            scalar TypeD
            @extendOperators
            @externalOperator

            scalar SchemaTypeInfiniteLoop
            @operators(type: "InfiniteLoop")
            @aOperator

            type Query {
                test: Int @all
            }
            GRAPHQL,
        );

        // Config
        $config      = [
            'Alias'        => [
                'TypeA',
            ],
            'TypeA'        => [
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorExternal::class,
                OperatorsTest__OperatorNotAvailable::class,
            ],
            'TypeB'        => [
                OperatorsTest__OperatorNotAvailable::class,
                OperatorsTest__OperatorExternal::class,
                OperatorsTest__OperatorA::class,
                'TypeB',
            ],
            'TypeD'        => [
                'TypeD',
            ],
            'InfiniteLoop' => [
                OperatorsTest__OperatorNotAvailable::class,
                OperatorsTest__OperatorExternal::class,
                OperatorsTest__OperatorA::class,
                'SchemaTypeInfiniteLoop',
            ],
        ];
        $default     = [
            'TypeA' => [
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorB::class,
                OperatorsTest__OperatorC::class,
                OperatorsTest__OperatorExternal::class,
                OperatorsTest__OperatorNotAvailable::class,
            ],
            'TypeB' => [
                OperatorsTest__OperatorNotAvailable::class,
                OperatorsTest__OperatorExternal::class,
                OperatorsTest__OperatorB::class,
                'TypeB',
            ],
            'TypeC' => [
                OperatorsTest__OperatorNotAvailable::class,
                OperatorsTest__OperatorExternal::class,
                OperatorsTest__OperatorC::class,
                'TypeB',
                'TypeA',
            ],
            'TypeD' => [
                OperatorsTest__OperatorNotAvailable::class,
                OperatorsTest__OperatorExternal::class,
                OperatorsTest__OperatorA::class,
            ],
        ];
        $source      = Mockery::mock(TypeSource::class);
        $context     = Mockery::mock(Context::class);
        $operators   = new OperatorsTest__Operators($config, $default);
        $document    = Container::getInstance()->make(ASTBuilder::class)->documentAST();
        $manipulator = Container::getInstance()->make(Manipulator::class, [
            'document' => $document,
        ]);

        // Tests
        self::assertEquals(
            [
                OperatorsTest__OperatorA::class,
            ],
            $this->toClassNames($operators->getOperators($manipulator, 'TypeA', $source, $context)),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorB::class,
            ],
            $this->toClassNames($operators->getOperators($manipulator, 'TypeB', $source, $context)),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorC::class,
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorB::class,
            ],
            $this->toClassNames($operators->getOperators($manipulator, 'TypeC', $source, $context)),
        );
        self::assertEquals(
            $operators->getOperators($manipulator, 'TypeA', $source, $context),
            $operators->getOperators($manipulator, 'Alias', $source, $context),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorA::class,
                OperatorsTest__OperatorB::class,
                OperatorsTest__OperatorC::class,
            ],
            $this->toClassNames($operators->getOperators($manipulator, 'SchemaTypeA', $source, $context)),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorA::class,
            ],
            $this->toClassNames($operators->getOperators($manipulator, 'TypeD', $source, $context)),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorA::class,
            ],
            $this->toClassNames($operators->getOperators($manipulator, 'SchemaTypeD', $source, $context)),
        );
        self::assertEquals(
            [
                // empty
            ],
            $this->toClassNames($operators->getOperators($manipulator, 'SchemaTypeIgnored', $source, $context)),
        );
        self::assertEquals(
            [
                OperatorsTest__OperatorA::class,
            ],
            $this->toClassNames($operators->getOperators($manipulator, 'SchemaTypeInfiniteLoop', $source, $context)),
        );
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
interface OperatorsTest__Scope extends Scope {
    // empty
}

/**
 * @deprecated   5.6.0
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 */
class OperatorsTest__OperatorsDirective extends OperatorsDirective implements OperatorsTest__Scope {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class OperatorsTest__ExtendOperatorsDirective extends ExtendOperatorsDirective implements OperatorsTest__Scope {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class OperatorsTest__Operators extends Operators {
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
        return OperatorsTest__Scope::class;
    }
}

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
    public function isAvailable(TypeProvider $provider, TypeSource $source, Context $context): bool {
        return true;
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): ?string {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function getFieldDescription(): ?string {
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
class OperatorsTest__OperatorA extends OperatorsTest__Operator implements OperatorsTest__Scope {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class OperatorsTest__OperatorB extends OperatorsTest__Operator implements OperatorsTest__Scope {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class OperatorsTest__OperatorC extends OperatorsTest__Operator implements OperatorsTest__Scope {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class OperatorsTest__OperatorNotAvailable extends OperatorsTest__Operator implements OperatorsTest__Scope {
    #[Override]
    public function isAvailable(TypeProvider $provider, TypeSource $source, Context $context): bool {
        return false;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class OperatorsTest__OperatorExternal extends OperatorsTest__Operator {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class OperatorsTest__Ignored extends BaseDirective implements Ignored, OperatorsTest__Scope {
    #[Override]
    public static function definition(): string {
        return <<<'GRAPHQL'
            directive @ignored on SCALAR
        GRAPHQL;
    }
}
