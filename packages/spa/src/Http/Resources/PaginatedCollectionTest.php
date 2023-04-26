<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function get_class;
use function json_decode;

/**
 * @internal
 */
#[CoversClass(PaginatedCollection::class)]
#[CoversClass(PaginatedResponse::class)]
class PaginatedCollectionTest extends TestCase {
    public function testToResponseLengthAwarePaginator(): void {
        $total     = 123;
        $perPage   = 25;
        $current   = 2;
        $items     = [
            1 => [
                'a' => 1,
                'b' => 2,
            ],
            2 => [
                'c' => 3,
                'd' => 4,
            ],
        ];
        $paginator = new LengthAwarePaginator($items, $total, $perPage, $current);
        $class     = get_class(new class(null) extends Resource {
            // empty
        });
        $resource  = $class::collection($paginator);
        $expected  = [
            'items' => [
                [
                    'a' => 1,
                    'b' => 2,
                ],
                [
                    'c' => 3,
                    'd' => 4,
                ],
            ],
            'meta'  => [
                'current_page' => $current,
                'last_page'    => 5,
                'per_page'     => $perPage,
                'total'        => $total,
                'from'         => 26,
                'to'           => 27,
            ],
        ];

        self::assertEquals($expected, json_decode(
            $resource->toResponse(new Request())->content(),
            true,
        ));
    }

    public function testToResponsePaginator(): void {
        $perPage   = 25;
        $current   = 2;
        $items     = [
            1 => [
                'a' => 1,
                'b' => 2,
            ],
            2 => [
                'c' => 3,
                'd' => 4,
            ],
        ];
        $paginator = new Paginator($items, $perPage, $current);
        $class     = get_class(new class(null) extends Resource {
            // empty
        });
        $resource  = $class::collection($paginator);
        $expected  = [
            'items' => [
                [
                    'a' => 1,
                    'b' => 2,
                ],
                [
                    'c' => 3,
                    'd' => 4,
                ],
            ],
            'meta'  => [
                'current_page' => $current,
                'last_page'    => null,
                'per_page'     => $perPage,
                'total'        => null,
                'from'         => 26,
                'to'           => 27,
            ],
        ];

        self::assertEquals($expected, json_decode(
            $resource->toResponse(new Request())->content(),
            true,
        ));
    }
}
