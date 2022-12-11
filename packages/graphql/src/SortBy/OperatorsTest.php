<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Extra\Random;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\Operators
 */
class OperatorsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__construct
     */
    public function testConstructor(): void {
        $config = Mockery::mock(Repository::class);
        $config
            ->shouldReceive('get')
            ->with(Package::Name.'.sort_by.operators')
            ->andReturn([
                Operators::Extra => [
                    Random::class,
                ],
            ]);

        $operators = new class($config) extends Operators {
            // empty
        };

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
