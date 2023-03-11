<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Comparators;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory;
use stdClass;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Testing\Comparators\EloquentModelComparator
 */
class EloquentModelComparatorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderAccepts
     */
    public function testAccepts(bool $equals, mixed $expected, mixed $actual): void {
        self::assertEquals($equals, (new EloquentModelComparator())->accepts($expected, $actual));
    }

    /**
     * @dataProvider dataProviderAssertEquals
     */
    public function testAssertEquals(bool|string $equals, mixed $expected, mixed $actual): void {
        if ($equals !== true) {
            self::expectException(ComparisonFailure::class);
            self::expectExceptionMessageMatches($equals ?: '/Failed asserting that two models are equal/i');
        }

        $comparator = new EloquentModelComparator();

        $comparator->setFactory(Factory::getInstance());
        $comparator->assertEquals($expected, $actual);
        self::assertTrue($equals);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public static function dataProviderAccepts(): array {
        return [
            'model + model'  => [
                true,
                new class() extends Model {
                    // empty
                },
                new class() extends Model {
                    // empty
                },
            ],
            'model + object' => [
                false,
                new class() extends Model {
                    // empty
                },
                new stdClass(),
            ],
            'model + scalar' => [
                false,
                new class() extends Model {
                    // empty
                },
                1,
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function dataProviderAssertEquals(): array {
        $f = new class() extends Model {
            // empty
        };
        $a = $f->newFromBuilder(['id' => '1']);
        $b = $f->newFromBuilder(['id' => 1]);
        $c = (clone $a)->setAttribute('id', 2);

        return [
            'different classes'             => [
                '/.+? is not instance of expected class ".+?"/i',
                new class() extends Model {
                    // empty
                },
                new class() extends Model {
                    // empty
                },
            ],
            'same model'                    => [
                true,
                $a,
                $a,
            ],
            'same model + different types'  => [
                true,
                $a,
                $b,
            ],
            'same model + different values' => [
                false,
                $b,
                $c,
            ],
        ];
    }
    // </editor-fold>
}
