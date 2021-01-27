<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use Exception;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Core\Utils\ConfigRecursiveMerger
 */
class ConfigRecursiveMergerTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::merge
     *
     * @dataProvider dataProviderMerge
     *
     * @param array|\Exception $expected
     * @param bool             $strict
     * @param string[]         $unprotected
     * @param array            $target
     * @param array            ...$configs
     *
     * @return void
     */
    public function testMerge($expected, bool $strict, array $unprotected, array $target, array ...$configs): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $merger = new ConfigRecursiveMerger($strict, $unprotected);
        $actual = $merger->merge($target, ... $configs);

        $this->assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderMerge(): array {
        return [
            'strict + array = ok'               => [
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
                true,
                [],
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
            'strict + not scalar = error'       => [
                new InvalidArgumentException('Config may contain only scalar/null values and arrays of them.'),
                true,
                [],
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
            'strict + unknown key = error'      => [
                new InvalidArgumentException('Unknown key `unknown`.'),
                true,
                [],
                [
                    'scalar' => 123,
                ],
                [
                    'unknown' => 123,
                ],
            ],
            'strict + scalar => array = error'  => [
                new InvalidArgumentException('Scalar/null value cannot be replaced by array.'),
                true,
                [],
                [
                    'value' => 123,
                ],
                [
                    'value' => [1, 2, 3],
                ],
            ],
            'strict + array => scalar = error'  => [
                new InvalidArgumentException('Array cannot be replaced by scalar/null value.'),
                true,
                [],
                [
                    'value' => [1, 2, 3],
                ],
                [
                    'value' => 123,
                ],
            ],
            'unknown key'                       => [
                [
                    'scalar'  => 123,
                    'unknown' => 123,
                ],
                false,
                [],
                [
                    'scalar' => 123,
                ],
                [
                    'unknown' => 123,
                ],
            ],
            'scalar => array'                   => [
                [
                    'value' => [1, 2, 3],
                ],
                false,
                [],
                [
                    'value' => 123,
                ],
                [
                    'value' => [1, 2, 3],
                ],
            ],
            'array => scalar'                   => [
                [
                    'value' => [321],
                ],
                false,
                [],
                [
                    'value' => [1, 2, 3],
                ],
                [
                    'value' => 123,
                ],
                [
                    'value' => [321],
                ],
            ],
            'non-strict + unprotected => error' => [
                new LogicException('Setting the `$unprotected` paths has no effect in non-strict mode.'),
                false,
                ['path'],
                [],
                [],
                [],
            ],
            'strict + unprotected  => ok'       => [
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
                true,
                [
                    'unprotected.path-a',
                    'unprotected.path-b',
                ],
                [
                    'scalar'      => 123,
                    'unprotected' => [
                        'path-a' => [
                            'value' => 123,
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
            'strict + partial unprotected  => error'       => [
                new InvalidArgumentException('Unknown key `unprotected.path-b.added`.'),
                true,
                [
                    'unprotected.path-a',
                ],
                [
                    'scalar'      => 123,
                    'unprotected' => [
                        'path-a' => [
                            'value' => 123,
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
        ];
    }
    // </editor-fold>
}
