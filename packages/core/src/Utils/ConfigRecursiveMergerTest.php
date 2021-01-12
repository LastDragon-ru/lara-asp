<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use Exception;
use InvalidArgumentException;
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
     * @param array            $config
     * @param array            ...$configs
     *
     * @return void
     */
    public function testMerge($expected, bool $strict, array $config, array ...$configs): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $merger = new ConfigRecursiveMerger($strict);
        $actual = $merger->merge($config, ... $configs);

        $this->assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderMerge(): array {
        return [
            'strict + array = ok'              => [
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
            'strict + not scalar = error'      => [
                new InvalidArgumentException('Config may contain only scalar/null values and arrays of them.'),
                true,
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
            'strict + unknown key = error'     => [
                new InvalidArgumentException('Unknown key.'),
                true,
                [
                    'scalar' => 123,
                ],
                [
                    'unknown' => 123,
                ],
            ],
            'strict + scalar => array = error' => [
                new InvalidArgumentException('Scalar/null value cannot be replaced by array.'),
                true,
                [
                    'value' => 123,
                ],
                [
                    'value' => [1, 2, 3],
                ],
            ],
            'strict + array => scalar = error' => [
                new InvalidArgumentException('Array cannot be replaced by scalar/null value.'),
                true,
                [
                    'value' => [1, 2, 3],
                ],
                [
                    'value' => 123,
                ],
            ],
            'unknown key'                      => [
                [
                    'scalar'  => 123,
                    'unknown' => 123,
                ],
                false,
                [
                    'scalar' => 123,
                ],
                [
                    'unknown' => 123,
                ],
            ],
            'scalar => array'                  => [
                [
                    'value' => [1, 2, 3],
                ],
                false,
                [
                    'value' => 123,
                ],
                [
                    'value' => [1, 2, 3],
                ],
            ],
            'array => scalar'                  => [
                [
                    'value' => [321],
                ],
                false,
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
        ];
    }
    // </editor-fold>
}
