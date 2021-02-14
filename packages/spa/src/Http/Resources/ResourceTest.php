<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use JsonSerializable;
use LastDragon_ru\LaraASP\Spa\Package;
use LastDragon_ru\LaraASP\Spa\Testing\TestCase;
use LogicException;

use function get_class;
use function json_decode;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Spa\Http\Resources\Resource
 */
class ResourceTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__construct
     *
     * @dataProvider dataProviderConstruct
     */
    public function testConstruct(bool|Exception $expected, mixed $value): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        new class($value) extends Resource {
            // empty
        };

        $this->assertTrue($expected);
    }

    /**
     * @covers ::collection
     *
     * @dataProvider dataProviderCollection
     */
    public function testCollection(string $expected, mixed $value): void {
        $class  = get_class(new class(null) extends Resource {
            // empty
        });
        $actual = $class::collection($value);

        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @covers ::toArray
     * @covers ::toResponse
     * @covers ::response
     * @covers ::mapResourceData
     *
     * @dataProvider dataProviderMapResourceData
     */
    public function testMapResourceData(array|Exception $expected, mixed $value): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $resource = new class($value) extends Resource {
            /**
             * @inheritdoc
             */
            public function toArray($request): array {
                if ($this->resource instanceof Model) {
                    $properties = [];

                    foreach ($this->resource->getAttributes() as $key => $value) {
                        $properties[$key] = $this->resource->{$key};
                    }

                    return $properties;
                }

                return parent::toArray($request);
            }
        };

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $this->assertEquals($expected, json_decode(
            $resource->response()->content(),
            true,
        ));
    }

    /**
     * @covers ::mapResourceData
     */
    public function testMapResourceDataImplicitModel(): void {
        $model    = new class() extends Model {
            // empty
        };
        $resource = new class($model) extends Resource {
            // empty
        };

        $this->expectExceptionObject(new LogicException(
            'Implicit conversions of Models is not supported, please redefine this method to make it explicit.',
        ));

        $this->assertIsArray($resource->toArray(null));
    }

    /**
     * @covers ::additional
     */
    public function testAdditional(): void {
        $resource = new class(123) extends Resource {
            protected function mapResourceData(array $data, array $path): array {
                throw new Exception(__FUNCTION__);
            }
        };

        $this->expectExceptionObject(new Exception('mapResourceData'));

        $resource->additional([
            'additional' => 'value',
        ]);
    }

    /**
     * @covers ::with
     */
    public function testWith(): void {
        $resource = new class(123) extends Resource {
            protected function mapResourceData(array $data, array $path): array {
                throw new Exception(__FUNCTION__);
            }
        };

        $this->expectExceptionObject(new Exception('mapResourceData'));

        $resource->with(null);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderConstruct(): array {
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

    public function dataProviderCollection(): array {
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
                     * @inheritdoc
                     */
                    public function toArray() {
                        return [];
                    }
                },
            ],
        ];
    }

    public function dataProviderMapResourceData(): array {
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
                new class($date, $format) extends Model {
                    public function __construct(DateTimeInterface $date, string $format) {
                        parent::__construct([]);

                        $this->dateFormat   = $format;
                        $this->casts        = [
                            'date'     => 'date',
                            'datetime' => 'datetime',
                        ];
                        $this->date         = $date;
                        $this->datetime     = $date;
                        $this->date_no_cast = $date;
                        $this->nested       = [
                            'nested_date'         => $date,
                            'nested_datetime'     => $date,
                            'nested_date_no_cast' => $date,
                        ];
                        $this->collection   = new Collection([
                            'collection_date'         => $date,
                            'collection_datetime'     => $date,
                            'collection_date_no_cast' => $date,
                        ]);
                    }
                },
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
