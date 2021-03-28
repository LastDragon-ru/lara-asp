<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Core\Utils\ConfigMerger
 */
class ConfigMergerTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::merge
     *
     * @dataProvider dataProviderMerge
     *
     * @param array<mixed> $target
     * @param array<mixed> $configs
     */
    public function testMerge(
        array|Exception $expected,
        array $target,
        array ...$configs,
    ): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $merger = new ConfigMerger();
        $actual = $merger->merge($target, ... $configs);

        $this->assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderMerge(): array {
        return [
            'array + array = ok'                    => [
                [
                    'scalar' => 321,
                    'null'   => null,
                    'array'  => [1, 2, 3],
                    'nested' => [
                        'scalar' => 123,
                        'null'   => 'qwe',
                        'array'  => [1, 2, 3],
                        'nested' => [
                            'scalar' => 123,
                            'null'   => null,
                            'array'  => [3, 2, 1],
                        ],
                    ],
                ],
                [
                    'scalar' => 123,
                    'null'   => null,
                    'array'  => [1, 2, 3],
                    'nested' => [
                        'scalar' => 123,
                        'null'   => null,
                        'array'  => [1, 2, 3],
                        'nested' => [
                            'scalar' => 123,
                            'null'   => null,
                            'array'  => [1, 2, 3],
                        ],
                    ],
                ],
                [
                    'scalar' => 321,
                    'nested' => [
                        'null'   => 'qwe',
                        'nested' => [
                            'array' => [3, 2, 1],
                        ],
                    ],
                ],
            ],
            'array + not scalar = error'            => [
                new InvalidArgumentException('Config may contain only scalar/null values and arrays of them.'),
                [
                    'scalar' => 123,
                    'nested' => [
                        'value' => 123,
                    ],
                ],
                [
                    'nested' => [
                        'value' => new stdClass(),
                    ],
                ],
            ],
            'unknown key = error'                   => [
                new InvalidArgumentException('Unknown key `unknown`.'),
                [
                    'scalar' => 123,
                ],
                [
                    'unknown' => 123,
                ],
            ],
            'scalar + array = error'                => [
                new InvalidArgumentException('Scalar/null value cannot be replaced by array.'),
                [
                    'value' => 123,
                ],
                [
                    'value' => [1, 2, 3],
                ],
            ],
            'array + scalar = error'                => [
                new InvalidArgumentException('Array cannot be replaced by scalar/null value.'),
                [
                    'value' => [1, 2, 3],
                ],
                [
                    'value' => 123,
                ],
            ],
            'not strict: unknown key'               => [
                [
                    'scalar'  => 123,
                    'unknown' => 123,
                ],
                [
                    ConfigMerger::Strict => false,
                    'scalar'             => 123,
                ],
                [
                    'unknown' => 123,
                ],
            ],
            'not strict: scalar => array'           => [
                [
                    'value' => [1, 2, 3],
                ],
                [
                    ConfigMerger::Strict => false,
                    'value'              => 123,
                ],
                [
                    'value' => [1, 2, 3],
                ],
            ],
            'not strict: array => scalar'           => [
                [
                    'value' => [321],
                ],
                [
                    ConfigMerger::Strict => false,
                    'value'              => [1, 2, 3],
                ],
                [
                    'value' => 123,
                ],
                [
                    'value' => [321],
                ],
            ],
            'not strict: array + array => ok'       => [
                [
                    'scalar'      => 123,
                    'unprotected' => [
                        'path-a' => [
                            'value' => [1, 2, 3],
                            'added' => 123,
                        ],
                        'path-b' => [
                            'value' => 123,
                            'added' => 123,
                        ],
                    ],
                ],
                [
                    'scalar'      => 123,
                    'unprotected' => [
                        'path-a' => [
                            ConfigMerger::Strict => false,
                            'value'              => 123,
                        ],
                        'path-b' => [
                            ConfigMerger::Strict => false,
                            'value'              => 123,
                        ],
                    ],
                ],
                [
                    'unprotected' => [
                        'path-a' => [
                            'value' => [1, 2, 3],
                            'added' => 123,
                        ],
                        'path-b' => [
                            'added' => 123,
                        ],
                    ],
                ],
            ],
            'partial not strict = error'            => [
                new InvalidArgumentException('Unknown key `unprotected.path-b.added`.'),
                [
                    'scalar'      => 123,
                    'unprotected' => [
                        'path-a' => [
                            ConfigMerger::Strict => false,
                            'value'              => 123,
                        ],
                        'path-b' => [
                            'value' => 123,
                        ],
                    ],
                ],
                [
                    'unprotected' => [
                        'path-a' => [
                            'value' => [1, 2, 3],
                            'added' => 123,
                        ],
                        'path-b' => [
                            'added' => 123,
                        ],
                    ],
                ],
            ],
            'empty target array + non empty array'  => [
                [
                    'scalar' => 123,
                    'array'  => [
                        'path-a' => [
                            'value' => [1, 2, 3],
                            'added' => 123,
                        ],
                    ],
                ],
                [
                    'scalar' => 123,
                    'array'  => [
                        'path-a' => [],
                    ],
                ],
                [
                    'array' => [
                        'path-a' => [
                            'value' => [1, 2, 3],
                            'added' => 123,
                        ],
                    ],
                ],
            ],
            'strict cannot be overwritten in child' => [
                [
                    'unknown' => 1,
                    'array'   => [
                        'unknown' => 1,
                    ],
                ],
                [
                    ConfigMerger::Strict => false,
                    'array'              => [
                        ConfigMerger::Strict => true,
                    ],
                ],
                [
                    'unknown' => 1,
                    'array'   => [
                        'unknown' => 1,
                    ],
                ],
            ],
            'strict cannot be overwritten in value' => [
                new InvalidArgumentException('Unknown key `unknown`.'),
                [
                    'value' => 1,
                ],
                [
                    ConfigMerger::Strict => false,
                    'unknown'            => 1,
                ],
            ],
            'replace'                               => [
                [
                    'array' => [
                        'unknown' => 1,
                    ],
                ],
                [
                    'array' => [
                        ConfigMerger::Replace => true,
                        'value'               => 123,
                    ],
                ],
                [
                    'array' => [
                        'unknown' => 1,
                    ],
                ],
            ],
        ];
    }
    // </editor-fold>
}
