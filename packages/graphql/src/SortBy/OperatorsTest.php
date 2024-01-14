<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorRandomDirective;
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
            Package::Name.'.sort_by.operators' => [
                Operators::Extra => [
                    SortByOperatorRandomDirective::class,
                ],
            ],
        ]);

        $operators = Container::getInstance()->make(Operators::class);

        self::assertTrue($operators->hasOperators(Operators::Extra));
        self::assertFalse($operators->hasOperators('unknown'));
        self::assertEquals(
            [
                SortByOperatorRandomDirective::class,
            ],
            $this->toClassNames(
                $operators->getOperators(Operators::Extra),
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
