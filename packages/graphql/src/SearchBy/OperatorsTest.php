<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
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

        $operators = Container::getInstance()->make(Operators::class);

        self::assertTrue($operators->hasOperators(Operators::ID));
        self::assertTrue($operators->hasOperators(Operators::Int));
        self::assertFalse($operators->hasOperators('unknown'));
        self::assertEquals(
            [
                SearchByOperatorEqualDirective::class,
            ],
            $this->toClassNames(
                $operators->getOperators(Operators::ID),
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
