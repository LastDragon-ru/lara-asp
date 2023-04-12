<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

use function config;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators
 */
class OperatorsTest extends TestCase {
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

        $operators = $this->app->make(Operators::class);

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
