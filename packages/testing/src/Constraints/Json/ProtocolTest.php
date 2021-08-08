<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use Composer\Util\Platform;
use Exception;
use LastDragon_ru\LaraASP\Testing\Utils\WithTempFile;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function array_map;
use function explode;
use function http_build_query;
use function implode;
use function json_decode;
use function ltrim;
use function rawurlencode;
use function str_replace;

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
        $uri    = Protocol::getUri($file, $parameters);
        $actual = (new Protocol())($uri);

        $this->assertEquals($expected, $actual);
        $this->assertNotNull(json_decode($actual));
    }

    /**
     * @covers ::getUri
     */
    public function testGetUri(): void {
        $file   = new SplFileInfo(__FILE__);
        $host   = Platform::isWindows() ? 'windows.path' : 'unix.path';
        $path   = str_replace('\\', '/', $file->getPathname());
        $path   = implode('/', array_map(static function (string $segment): string {
            return rawurlencode($segment);
        }, explode('/', '/'.ltrim($path, '/'))));
        $params = ['a' => 'a', 'b' => 'b'];
        $actual = Protocol::getUri($file, $params);

        $this->assertEquals(Protocol::Scheme, $actual->scheme());
        $this->assertEquals($host, $actual->host());
        $this->assertEquals(null, $actual->port());
        $this->assertEquals($host, $actual->authority());
        $this->assertEquals(null, $actual->user());
        $this->assertEquals(null, $actual->pass());
        $this->assertEquals($path, $actual->path());
        $this->assertEquals(http_build_query($params), $actual->query());
        $this->assertEquals(null, $actual->fragment());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string|Exception, string, array<string,string>}>
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
