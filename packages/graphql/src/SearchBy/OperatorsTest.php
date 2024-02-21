<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotEqualDirective;
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
            Package::Name.'.search_by.operators' => [
                Operators::ID  => [
                    SearchByOperatorEqualDirective::class,
                ],
                Operators::Int => [
                    SearchByOperatorNotEqualDirective::class,
                ],
            ],
        ]);

        $operators   = Container::getInstance()->make(Operators::class);
        $manipulator = Container::getInstance()->make(Manipulator::class, [
            'document' => Mockery::mock(DocumentAST::class),
        ]);

        self::assertTrue($operators->hasType(Operators::ID));
        self::assertTrue($operators->hasType(Operators::Int));
        self::assertFalse($operators->hasType('unknown'));
        self::assertEquals(
            [
                SearchByOperatorEqualDirective::class,
            ],
            $this->toClassNames(
                $operators->getOperators($manipulator, Operators::ID),
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
