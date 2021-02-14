<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Angular;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Spa\Angular\Url
 */
class UrlTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__construct()
     * @covers ::extract
     *
     * @dataProvider dataProviderConstruct
     */
    public function testConstruct(array $expected, string $template): void {
        $url = new Url($template);

        $this->assertEquals($template, $url->getTemplate());
        $this->assertEquals($expected, $url->getParameters());
    }

    /**
     * @covers ::build
     *
     * @dataProvider dataProviderBuild
     */
    public function testBuild(string|Exception $expected, string $template, array $parameters): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $this->assertEquals($expected, (new Url($template))->build($parameters));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderConstruct(): array {
        return [
            'url without params'  => [[], 'http://example.com/path?:id=1'],
            'url with params'     => [['to', 'id'], 'http://example.com/path/:to/item/:id'],
            'path without params' => [[], 'path/to/item/id'],
            'path with params'    => [['to', 'id'], 'path/:to/item/:id'],
        ];
    }

    public function dataProviderBuild(): array {
        return [
            'url without params without data'       => ['http://example.com/path', 'http://example.com/path', []],
            'url without params with data'          => [
                'http://example.com/path?int=123&null=&true=1&false=0&float=1.23&array=1&array=2&array=3&string=value',
                'http://example.com/path',
                [
                    'int'    => 123,
                    'null'   => null,
                    'true'   => true,
                    'false'  => false,
                    'float'  => 1.23,
                    'array'  => [1, 2, 3],
                    'string' => 'value',
                ],
            ],
            'url with params without data'          => [
                new InvalidArgumentException('Url requires the following parameters: to, id.'),
                'http://example.com/path/:to/item/:id',
                [],
            ],
            'url with params with data'             => [
                'http://example.com/path/1/item/2?int=123',
                'http://example.com/path/:to/item/:id',
                [
                    'to'  => 1,
                    'id'  => 2,
                    'int' => 123,
                ],
            ],
            'url with params with data with query'  => [
                'http://example.com/path/1/item/2?value=123&int=123',
                'http://example.com/path/:to/item/:id?value=123',
                [
                    'to'  => 1,
                    'id'  => 2,
                    'int' => 123,
                ],
            ],
            'path without params without data'      => [
                'path/to/item/id?int=123&null=&true=1&false=0&float=1.23&array=1&array=2&array=3&string=value',
                'path/to/item/id',
                [
                    'int'    => 123,
                    'null'   => '',
                    'true'   => true,
                    'false'  => false,
                    'float'  => 1.23,
                    'array'  => [1, 2, 3],
                    'string' => 'value',
                ],
            ],
            'path with params with data'            => [
                '/path/1/item/2?int=123',
                '/path/:to/item/:id',
                [
                    'to'  => 1,
                    'id'  => 2,
                    'int' => 123,
                ],
            ],
            'path with params with data with query' => [
                '/path/1/item/2?value=123&int=123',
                '/path/:to/item/:id?value=123',
                [
                    'to'  => 1,
                    'id'  => 2,
                    'int' => 123,
                ],
            ],
            'path with params without data'         => [
                new InvalidArgumentException('Url requires the following parameters: to, id.'),
                'path/:to/item/:id',
                [],
            ],
        ];
    }
    // </editor-fold>
}
