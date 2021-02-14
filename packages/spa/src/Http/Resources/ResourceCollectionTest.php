<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use Exception;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Spa\Testing\TestCase;
use stdClass;

use function get_class;
use function json_decode;
use function sprintf;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Spa\Http\Resources\ResourceCollection
 */
class ResourceCollectionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__construct
     *
     * @dataProvider dataProviderConstruct
     */
    public function testConstruct(bool|Exception $expected, string $class): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        new class($class, []) extends ResourceCollection {
            // empty
        };

        $this->assertTrue($expected);
    }

    /**
     * @covers ::toResponse
     */
    public function testToResponse(): void {
        $class    = get_class(new class(null) extends Resource {
            // empty
        });
        $resource = $class::collection([
            1 => [
                'a' => 1,
                'b' => 2,
            ],
            2 => [
                'c' => 3,
                'd' => 4,
            ],
        ]);
        $expected = [
            [
                'a' => 1,
                'b' => 2,
            ],
            [
                'c' => 3,
                'd' => 4,
            ],
        ];

        $this->assertEquals($expected, json_decode(
            $resource->toResponse(null)->content(),
            true,
        ));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderConstruct(): array {
        return [
            'class'        => [
                new InvalidArgumentException(sprintf(
                    'The `$class` must be instance of `%s`.',
                    SafeResource::class,
                )),
                stdClass::class,
            ],
            'SafeResource' => [
                true,
                get_class(new class() implements SafeResource {
                    // empty
                }),
            ],
        ];
    }
    // </editor-fold>
}
