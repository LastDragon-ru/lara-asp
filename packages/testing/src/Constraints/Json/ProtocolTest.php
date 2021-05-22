<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use LastDragon_ru\LaraASP\Testing\Utils\WithTempFile;
use Opis\Uri\Uri;
use PHPUnit\Framework\TestCase;

use function http_build_query;
use function is_null;
use function json_decode;

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
    public function testInvoke(string|null $expected, string $content, array $parameters): void {
        if (is_null($expected)) {
            $this->expectError();
            $this->expectErrorMessage('Undefined array key');
        }

        $file   = $this->getTempFile($content);
        $query  = http_build_query($parameters);
        $uri    = Uri::create("https://example.com/{$file->getPathname()}?{$query}");
        $actual = (new Protocol())($uri);

        $this->assertEquals($expected, $actual);
        $this->assertNotNull(json_decode($actual));
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
                '{"a": ["a", "b"]}',
                '{"${a}": ["${a}", "${b}"]}',
                [
                    'a' => 'a',
                    'b' => 'b',
                ],
            ],
            'template with missed parameter' => [
                null,
                '{"${a}": ["${a}", "${b}"]}',
                [
                    'a' => 'a',
                ],
            ],
        ];
    }
    // </editor-fold>
}
