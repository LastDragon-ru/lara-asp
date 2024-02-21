<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorRandomDirective;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use PHPUnit\Framework\Attributes\CoversClass;

use function config;

/**
 * @internal
 */
#[CoversClass(Operators::class)]
final class OperatorsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testConstructor(): void {
        config([
            Package::Name.'.sort_by.operators' => [
                Operators::Extra => [
                    SortByOperatorRandomDirective::class,
                ],
            ],
        ]);

        $operators   = Container::getInstance()->make(Operators::class);
        $manipulator = Container::getInstance()->make(Manipulator::class, [
            'document' => Mockery::mock(DocumentAST::class),
        ]);

        self::assertEquals([], $operators->getOperators($manipulator, 'unknown'));
        self::assertEquals(
            [
                SortByOperatorRandomDirective::class,
            ],
            $this->toClassNames(
                $operators->getOperators($manipulator, Operators::Extra),
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
