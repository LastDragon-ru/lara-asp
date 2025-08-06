<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use Exception;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Config\Config;
use LastDragon_ru\LaraASP\GraphQL\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Operator;
use Mockery;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Operators::class)]
final class OperatorsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testConstructor(): void {
        $this->setConfiguration(
            PackageConfig::class,
            static function (Config $config): void {
                $config->searchBy->operators = [
                    Operators::ID  => [
                        OperatorsTest__Operator::class,
                    ],
                    Operators::Int => [
                        OperatorsTest__Operator::class,
                    ],
                ];
            },
        );

        $source      = Mockery::mock(TypeSource::class);
        $context     = Mockery::mock(Context::class);
        $operators   = $this->app()->make(Operators::class);
        $manipulator = $this->app()->make(Manipulator::class, [
            'document' => Mockery::mock(DocumentAST::class),
        ]);

        self::assertEquals([], $operators->getOperators($manipulator, 'unknown', $source, $context));
        self::assertEquals(
            [
                OperatorsTest__Operator::class,
            ],
            $this->toClassNames(
                $operators->getOperators($manipulator, Operators::ID, $source, $context),
            ),
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
class OperatorsTest__Operator extends Operator {
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
