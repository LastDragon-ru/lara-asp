<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Testing\Http\Resources;

use Exception;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesSchema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(PaginatedCollection::class)]
final class PaginatedCollectionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @coversNothing
     *
     * @param array<array-key, mixed> $json
     */
    #[DataProvider('dataProviderSchema')]
    public function testSchema(bool $expected, array $json): void {
        $schema     = new PaginatedCollection(self::class);
        $constraint = new JsonMatchesSchema($schema);
        $message    = '';
        $actual     = null;

        try {
            $actual = $constraint->evaluate($json);
            $actual = true;
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            $actual  = false;
        }

        self::assertSame($expected, $actual, $message);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderSchema(): array {
        return [
            'object (invalid)'                 => [
                false,
                [
                    'value' => 123,
                ],
            ],
            'array (invalid)'                  => [
                false,
                [
                    [
                        'value' => 123,
                    ],
                ],
            ],
            'paginated (length aware)'         => [
                true,
                [
                    'items' => [
                        [
                            'value' => 123,
                        ],
                        [
                            'value' => 123,
                        ],
                    ],
                    'meta'  => [
                        'current_page' => 1,
                        'last_page'    => 5,
                        'per_page'     => 2,
                        'total'        => 3,
                        'from'         => 26,
                        'to'           => 27,
                    ],
                ],
            ],
            'paginated (length aware + empty)' => [
                true,
                [
                    'items' => [],
                    'meta'  => [
                        'current_page' => 1,
                        'last_page'    => 5,
                        'per_page'     => 2,
                        'total'        => 3,
                        'from'         => 26,
                        'to'           => 27,
                    ],
                ],
            ],
            'paginated (simple)'               => [
                true,
                [
                    'items' => [
                        [
                            'value' => 123,
                        ],
                        [
                            'value' => 123,
                        ],
                    ],
                    'meta'  => [
                        'current_page' => 1,
                        'last_page'    => null,
                        'per_page'     => 2,
                        'total'        => null,
                        'from'         => 1,
                        'to'           => 27,
                    ],
                ],
            ],
            'paginated (simple + empty)'       => [
                true,
                [
                    'items' => [],
                    'meta'  => [
                        'current_page' => 1,
                        'last_page'    => null,
                        'per_page'     => 2,
                        'total'        => null,
                        'from'         => 1,
                        'to'           => 27,
                    ],
                ],
            ],
            'paginated (invalid items)'        => [
                false,
                [
                    'items' => [
                        'value' => 123,
                    ],
                    'meta'  => [
                        'current_page' => 1,
                        'last_page'    => 5,
                        'per_page'     => 2,
                        'total'        => 3,
                        'from'         => 26,
                        'to'           => 27,
                    ],
                ],
            ],
            'paginated (invalid meta)'         => [
                false,
                [
                    'items' => [
                        [
                            'value' => 123,
                        ],
                        [
                            'value' => 123,
                        ],
                    ],
                    'meta'  => [
                        'current_page' => 1,
                        'per_page'     => 2,
                        'total'        => 3,
                        'from'         => 26,
                        'to'           => 27,
                    ],
                ],
            ],
        ];
    }
    // </editor-fold>
}
