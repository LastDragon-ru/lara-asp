<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function get_class;
use function json_decode;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(ResourceCollection::class)]
final class ResourceCollectionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderConstruct
     */
    public function testConstruct(bool|Exception $expected, string $class): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        new class($class, []) extends ResourceCollection {
            // empty
        };

        self::assertTrue($expected);
    }

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

        self::assertEquals($expected, json_decode(
            $resource->toResponse(new Request())->content(),
            true,
        ));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderConstruct(): array {
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
                get_class(new class(new stdClass()) extends JsonResource implements SafeResource {
                    // empty
                }),
            ],
        ];
    }
    // </editor-fold>
}
