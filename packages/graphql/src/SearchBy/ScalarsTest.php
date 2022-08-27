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
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Scalars
 */
class ScalarsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__construct
     */
    public function testConstructor(): void {
        $config = Mockery::mock(Repository::class);
        $config
            ->shouldReceive('get')
            ->with(Package::Name.'.search_by.scalars')
            ->andReturn([
                Scalars::ScalarID  => [
                    Equal::class,
                ],
                Scalars::ScalarInt => [
                    NotEqual::class,
                ],
            ]);

        $scalars = new class($this->app, $config) extends Scalars {
            // empty
        };

        self::assertTrue($scalars->isScalar(Scalars::ScalarID));
        self::assertTrue($scalars->isScalar(Scalars::ScalarInt));
        self::assertFalse($scalars->isScalar('unknown'));
        self::assertEquals(
            [
                Equal::class,
            ],
            $this->toClassNames(
                $scalars->getScalarOperators(Scalars::ScalarID, false),
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
