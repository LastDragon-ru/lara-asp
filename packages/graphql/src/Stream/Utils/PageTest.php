<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Utils;

use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Page::class)]
final class PageTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array<array-key, mixed> $expected
     * @param int<0, max>             $offset
     * @param int<1, max>             $limit
     */
    #[DataProvider('dataProviderConstruct')]
    public function testConstruct(array $expected, int $offset, int $limit): void {
        $actual = new Page($limit, $offset);
        $actual = [
            'pageNumber' => $actual->pageNumber,
            'pageSize'   => $actual->pageSize,
            'length'     => $actual->length,
            'start'      => $actual->start,
            'end'        => $actual->end,
        ];

        self::assertEquals($expected, $actual);
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, array{
     *      array{
     *          pageNumber: int<1, max>,
     *          pageSize: int<1, max>,
     *          length: int<1, max>,
     *          start: int<0, max>,
     *          end: int<0,max>,
     *      },
     *      int<0, max>,
     *      int<1, max>,
     *      }>
     */
    public static function dataProviderConstruct(): array {
        return [
            [
                ['pageNumber' => 1, 'pageSize' => 1, 'length' => 1, 'start' => 0, 'end' => 0],
                0,
                1,
            ],
            [
                ['pageNumber' => 1, 'pageSize' => 10, 'length' => 10, 'start' => 0, 'end' => 0],
                0,
                10,
            ],
            [
                ['pageNumber' => 3, 'pageSize' => 1, 'length' => 1, 'start' => 0, 'end' => 0],
                2,
                1,
            ],
            [
                ['pageNumber' => 2, 'pageSize' => 2, 'length' => 2, 'start' => 0, 'end' => 0],
                2,
                2,
            ],
            [
                ['pageNumber' => 1, 'pageSize' => 8, 'length' => 5, 'start' => 3, 'end' => 0],
                3,
                5,
            ],
            [
                ['pageNumber' => 10, 'pageSize' => 136, 'length' => 123, 'start' => 10, 'end' => 3],
                1234,
                123,
            ],
        ];
    }
    //</editor-fold>
}
