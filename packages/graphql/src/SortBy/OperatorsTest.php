<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Extra\Random;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

use function config;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQL\SortBy\Operators
 */
class OperatorsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testConstructor(): void {
        config([
            Package::Name.'.sort_by.operators' => [
                Operators::Extra => [
                    Random::class,
                ],
            ],
        ]);

        $operators = $this->app->make(Operators::class);

        self::assertTrue($operators->hasOperators(Operators::Extra));
        self::assertFalse($operators->hasOperators('unknown'));
        self::assertEquals(
            [
                Random::class,
            ],
            $this->toClassNames(
                $operators->getOperators(Operators::Extra, false),
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