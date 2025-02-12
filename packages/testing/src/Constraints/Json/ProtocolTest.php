<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use Exception;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use SplFileInfo;

use function array_map;
use function explode;
use function http_build_query;
use function implode;
use function json_decode;
use function mb_ltrim;
use function rawurlencode;
use function str_replace;

use const PHP_OS_FAMILY;

/**
 * @internal
 */
#[CoversClass(Protocol::class)]
final class ProtocolTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array<string,string> $parameters
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(Exception|string $expected, string $content, array $parameters): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $file   = self::getTempFile($content);
        $uri    = Protocol::getUri($file, $parameters);
        $actual = (new Protocol())($uri);

        self::assertEquals($expected, $actual);
        self::assertNotNull($actual);
        self::assertNotNull(json_decode($actual));
    }

    public function testGetUri(): void {
        $file   = new SplFileInfo(__FILE__);
        $host   = PHP_OS_FAMILY === 'Windows' ? 'windows.path' : 'unix.path';
        $path   = str_replace('\\', '/', $file->getPathname());
        $path   = implode('/', array_map(rawurlencode(...), explode('/', '/'.mb_ltrim($path, '/'))));
        $params = ['a' => 'a', 'b' => 'b'];
        $actual = Protocol::getUri($file, $params);

        self::assertSame(Protocol::Scheme, $actual->scheme());
        self::assertSame($host, $actual->host());
        self::assertNull($actual->port());
        self::assertSame($host, $actual->authority());
        self::assertNull($actual->user());
        self::assertNull($actual->pass());
        self::assertSame($path, $actual->path());
        self::assertSame(http_build_query($params), $actual->query());
        self::assertNull($actual->fragment());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string|Exception, string, array<string,string>}>
     */
    public static function dataProviderInvoke(): array {
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
