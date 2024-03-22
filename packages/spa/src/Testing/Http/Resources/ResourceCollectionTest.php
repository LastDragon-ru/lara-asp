<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Testing\Http\Resources;

use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesSchema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @internal
 */
#[CoversClass(ResourceCollection::class)]
final class ResourceCollectionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @coversNothing
     *
     * @param array<array-key, mixed> $json
     */
    #[DataProvider('dataProviderSchema')]
    public function testSchema(bool $expected, array $json): void {
        $schema     = new ResourceCollection(self::class);
        $constraint = new JsonMatchesSchema($schema);
        $message    = '';
        $actual     = null;

        try {
            $actual = $constraint->evaluate($json);
            $actual = true;
        } catch (ExpectationFailedException $exception) {
            $message = $exception->getMessage();
            $actual  = false;
        }

        self::assertEquals($expected, $actual, $message);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderSchema(): array {
        return [
            'object (invalid)'        => [
                false,
                [
                    'value' => 123,
                ],
            ],
            'array'                   => [
                true,
                [
                    [
                        'value' => 123,
                    ],
                    [
                        'value' => 123,
                    ],
                ],
            ],
            'array (empty)'           => [
                true,
                [],
            ],
            'paginated (not allowed)' => [
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
                        'last_page'    => 5,
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
