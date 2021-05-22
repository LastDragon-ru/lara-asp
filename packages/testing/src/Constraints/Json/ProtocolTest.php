<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use Exception;
use LastDragon_ru\LaraASP\Testing\Utils\WithTempFile;
use Opis\JsonSchema\Uri;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function http_build_query;
use function json_decode;

use const PHP_QUERY_RFC3986;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Constraints\Json\Protocol
 */
class ProtocolTest extends TestCase {
    use WithTempFile;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__invoke
     *
     * @dataProvider dataProviderInvoke
     *
     * @param array<string,string> $parameters
     */
    public function testInvoke(Exception|string $expected, string $content, array $parameters): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $file   = $this->getTempFile($content);
        $query  = http_build_query($parameters, encoding_type: PHP_QUERY_RFC3986);
        $uri    = Uri::create("https://example.com/{$file->getPathname()}?{$query}");
        $actual = (new Protocol())($uri);

        $this->assertEquals($expected, $actual);
        $this->assertNotNull(json_decode($actual));
    }

    /**
     * @covers ::getUri
     */
    public function testGetUri(): void {
        $file   = new SplFileInfo(__FILE__);
        $params = ['a' => 'a', 'b' => 'b'];
        $actual = Protocol::getUri($file, $params);

        $this->assertEquals(Protocol::Scheme, $actual->scheme());
        $this->assertEquals($file->getPathname(), $actual->path());
        $this->assertEquals(http_build_query($params), $actual->query());
        $this->assertEquals('', $actual->host());
        $this->assertEquals(null, $actual->port());
        $this->assertEquals('', $actual->authority());
        $this->assertEquals(null, $actual->fragment());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array<string|null, string, array<string,string>>>
     */
    public function dataProviderInvoke(): array {
        return [
            'template without parameters'    => [
                '{"a": "b"}',
                '{"a": "b"}',
                [],
            ],
            'template with parameters'       => [
                '{"a a": ["a a", "b"]}',
                '{"${a.a}": ["${a.a}", "${b}"]}',
                [
                    'a.a' => 'a a',
                    'b'   => 'b',
                ],
            ],
            'template with missed parameter' => [
                new OutOfBoundsException('Required parameter `b` is missed.'),
                '{"${a}": ["${a}", "${b}"]}',
                [
                    'a' => 'a',
                ],
            ],
        ];
    }
    // </editor-fold>
}
