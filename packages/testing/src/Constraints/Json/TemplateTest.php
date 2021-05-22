<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use PHPUnit\Framework\TestCase;

use function is_null;
use function json_decode;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Constraints\Json\Template
 */
class TemplateTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::build
     *
     * @dataProvider dataProviderBuild
     *
     * @param array<string,string> $parameters
     */
    public function testBuild(string|null $expected, string $content, array $parameters): void {
        if (is_null($expected)) {
            $this->expectError();
            $this->expectErrorMessage('Undefined array key');
        }

        $actual = (new Template($content))->build($parameters);

        $this->assertEquals($expected, $actual);
        $this->assertNotNull(json_decode($actual));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array<string|null, string, array<string,string>>>
     */
    public function dataProviderBuild(): array {
        return [
            'template without parameters'    => [
                '{"a": "b"}',
                '{"a": "b"}',
                [],
            ],
            'template with parameters'       => [
                '{"\\"a\\"": ["\\"a\\"", "b"]}',
                '{"${a}": ["${a}", "${b}"]}',
                [
                    'a' => '"a"',
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
