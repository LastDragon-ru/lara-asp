<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators
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
            ->with(Package::Name.'.search_by.operators')
            ->andReturn([
                Operators::ID  => [
                    Equal::class,
                ],
                Operators::Int => [
                    NotEqual::class,
                ],
            ]);

        $operators = new class($config) extends Operators {
            // empty
        };

        self::assertTrue($operators->hasOperators(Operators::ID));
        self::assertTrue($operators->hasOperators(Operators::Int));
        self::assertFalse($operators->hasOperators('unknown'));
        self::assertEquals(
            [
                Equal::class,
            ],
            $this->toClassNames(
                $operators->getOperators(Operators::ID, false),
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
