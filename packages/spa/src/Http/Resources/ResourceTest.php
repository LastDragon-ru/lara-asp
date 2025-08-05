<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use JsonSerializable;
use LastDragon_ru\LaraASP\Spa\Package;
use LastDragon_ru\LaraASP\Spa\Package\TestCase;
use LogicException;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function get_class;
use function json_decode;

/**
 * @internal
 */
#[CoversClass(Resource::class)]
final class ResourceTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderConstruct')]
    public function testConstruct(bool|Exception $expected, mixed $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        // @phpstan-ignore-next-line expr.resultUnused
        new class($value) extends Resource {
            // empty
        };

        self::assertTrue($expected);
    }

    /**
     * @param class-string $expected
     */
    #[DataProvider('dataProviderCollection')]
    public function testCollection(string $expected, mixed $value): void {
        $class  = get_class(
            new class(null) extends Resource {
                // empty
            },
        );
        $actual = $class::collection($value);

        self::assertInstanceOf($expected, $actual);
    }

    /**
     * @param array<array-key, mixed>|Exception $expected
     */
    #[DataProvider('dataProviderMapResourceData')]
    public function testMapResourceData(array|Exception $expected, mixed $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $resource = new class($value) extends Resource {
            /**
             * @inheritDoc
             */
            #[Override]
            public function toArray($request): mixed {
                if ($this->resource instanceof Model) {
                    $properties = [];

                    foreach ($this->resource->getAttributes() as $key => $value) {
                        $properties[$key] = $this->resource->getAttribute($key);
                    }

                    return $properties;
                }

                return parent::toArray($request);
            }
        };

        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        self::assertEquals(
            $expected,
            json_decode(
                $resource->response()->content(),
                true,
            ),
        );
    }

    public function testMapResourceDataImplicitModel(): void {
        $model    = new class() extends Model {
            // empty
        };
        $resource = new class($model) extends Resource {
            // empty
        };

        self::expectExceptionObject(
            new LogicException(
                'Implicit conversions of Models is not supported, please redefine this method to make it explicit.',
            ),
        );

        self::assertIsArray($resource->toArray(new Request()));
    }

    public function testAdditional(): void {
        $resource = new class(123) extends Resource {
            /**
             * @inheritDoc
             */
            #[Override]
            protected function mapResourceData(array $data, array $path): array {
                throw new Exception(__FUNCTION__);
            }
        };

        self::expectExceptionObject(new Exception('mapResourceData'));

        $resource->additional([
            'additional' => 'value',
        ]);
    }

    public function testWith(): void {
        $resource = new class(123) extends Resource {
            /**
             * @inheritDoc
             */
            #[Override]
            protected function mapResourceData(array $data, array $path): array {
                throw new Exception(__FUNCTION__);
            }
        };

        self::expectExceptionObject(new Exception('mapResourceData'));

        $resource->with(new Request());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderConstruct(): array {
        return [
            'scalar'    => [true, 123],
            'array'     => [true, [1, 2, 3]],
            'model'     => [
                true,
                new class() extends Model {
                    // empty
                },
            ],
            'paginator' => [
                true,
                new class() extends AbstractPaginator {
                    // empty
                },
            ],
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderCollection(): array {
        return [
            'scalar'    => [
                ResourceCollection::class,
                [123],
            ],
            'array'     => [
                ResourceCollection::class,
                [1, 2, 3],
            ],
            'model'     => [
                ResourceCollection::class,
                [
                    new class() extends Model {
                        // empty
                    },
                ],
            ],
            'paginator' => [
                PaginatedCollection::class,
                new class() extends AbstractPaginator implements Arrayable {
                    public function __construct() {
                        $this->items = new Collection();
                    }

                    /**
                     * @inheritDoc
                     */
                    #[Override]
                    public function toArray() {
                        return [];
                    }
                },
            ],
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderMapResourceData(): array {
        $date   = new DateTimeImmutable();
        $format = 'Y-m-d H:i:s.v';

        return [
            'array of scalar with SafeResource' => [
                [
                    'string'           => '123',
                    'int'              => 123,
                    'null'             => null,
                    'array'            => [1, 2, 3, $date->format(Package::DateTimeFormat)],
                    'SafeResource'     => [
                        'string' => '123',
                        'int'    => 123,
                    ],
                    'Collection'       => [
                        'null'  => null,
                        'array' => $date->format(Package::DateTimeFormat),
                    ],
                    'JsonSerializable' => [
                        'bool' => true,
                        'date' => $date->format(Package::DateTimeFormat),
                    ],
                ],
                [
                    'string'           => '123',
                    'int'              => 123,
                    'null'             => null,
                    'array'            => [1, 2, 3, $date],
                    'SafeResource'     => new class() implements SafeResource, JsonSerializable {
                        /**
                         * @return array<array-key, mixed>
                         */
                        #[Override]
                        public function jsonSerialize(): array {
                            return [
                                'string' => '123',
                                'int'    => 123,
                            ];
                        }
                    },
                    'Collection'       => new Collection([
                        'null'  => null,
                        'array' => $date,
                    ]),
                    'JsonSerializable' => new class($date) implements JsonSerializable {
                        private DateTimeInterface $date;

                        public function __construct(DateTimeInterface $date) {
                            $this->date = $date;
                        }

                        /**
                         * @return array<array-key, mixed>
                         */
                        #[Override]
                        public function jsonSerialize(): array {
                            return [
                                'bool' => true,
                                'date' => $this->date,
                            ];
                        }
                    },
                ],
            ],
            'model inside data'                 => [
                new LogicException('Please do not return Models directly, use our Resources instead.'),
                [
                    'model' => new class() extends Model {
                        // model
                    },
                ],
            ],
            'model'                             => [
                [
                    'date'         => $date->format(Package::DateFormat),
                    'datetime'     => $date->format(Package::DateTimeFormat),
                    'date_no_cast' => $date->format(Package::DateTimeFormat),
                    'nested'       => [
                        'nested_date'         => $date->format(Package::DateTimeFormat),
                        'nested_datetime'     => $date->format(Package::DateTimeFormat),
                        'nested_date_no_cast' => $date->format(Package::DateTimeFormat),
                    ],
                    'collection'   => [
                        'collection_date'         => $date->format(Package::DateTimeFormat),
                        'collection_datetime'     => $date->format(Package::DateTimeFormat),
                        'collection_date_no_cast' => $date->format(Package::DateTimeFormat),
                    ],
                ],
                ResourceTest_Model::create($date, $format),
            ],
            'JsonResource inside properties'    => [
                new LogicException('Please do not return JsonResource directly, use our Resources instead.'),
                [
                    'resource' => new class(123) extends JsonResource {
                        // empty
                    },
                ],
            ],
            'Jsonable inside properties'        => [
                new LogicException('Value cannot be converted to JSON.'),
                [
                    'resource' => new class() implements Jsonable {
                        #[Override]
                        public function toJson(mixed $options = 0): mixed {
                            return 'null';
                        }
                    },
                ],
            ],
        ];
    }
    // </editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @property DateTimeInterface                        $date
 * @property DateTimeInterface                        $datetime
 * @property DateTimeInterface                        $date_no_cast
 * @property array<array-key, DateTimeInterface>      $nested
 * @property Collection<array-key, DateTimeInterface> $collection
 */
class ResourceTest_Model extends Model {
    public static function create(DateTimeInterface $date, string $format): self {
        $model               = new self();
        $model->dateFormat   = $format;
        $model->casts        = [
            'date'     => 'date',
            'datetime' => 'datetime',
        ];
        $model->date         = $date;
        $model->datetime     = $date;
        $model->date_no_cast = $date;
        $model->nested       = [
            'nested_date'         => $date,
            'nested_datetime'     => $date,
            'nested_date_no_cast' => $date,
        ];
        $model->collection   = new Collection([
            'collection_date'         => $date,
            'collection_datetime'     => $date,
            'collection_date_no_cast' => $date,
        ]);

        return $model;
    }
}
