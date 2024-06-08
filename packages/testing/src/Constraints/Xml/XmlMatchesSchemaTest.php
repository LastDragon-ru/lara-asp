<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use DOMDocument;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use SplFileInfo;

use function is_string;

/**
 * @internal
 */
#[CoversClass(XmlMatchesSchema::class)]
final class XmlMatchesSchemaTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderEvaluate')]
    public function testEvaluate(bool|string $expected, SplFileInfo $schema, DOMDocument|SplFileInfo $xml): void {
        $constraint = new class($schema) extends XmlMatchesSchema {
            #[Override]
            public function additionalFailureDescription(mixed $other): string {
                return parent::additionalFailureDescription($other);
            }
        };
        $result     = $constraint->evaluate($xml, '', true);

        if (is_string($expected)) {
            self::assertFalse($result);
            self::assertStringContainsString($expected, $constraint->additionalFailureDescription($xml));
        } else {
            self::assertEquals($expected, $result);
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderEvaluate(): array {
        return [
            'rng + dom = valid'    => [
                true,
                self::getTestData()->file('.rng'),
                self::getTestData()->dom('.xml'),
            ],
            'rng + dom = invalid'  => [
                'Error #38: Did not expect element a there',
                self::getTestData()->file('.rng'),
                self::getTestData()->dom('.invalid.xml'),
            ],
            'xsd + dom = valid'    => [
                true,
                self::getTestData()->file('.xsd'),
                self::getTestData()->dom('.xml'),
            ],
            'xsd + dom = invalid'  => [
                "Error #1871: Element 'a': This element is not expected. Expected is ( child )",
                self::getTestData()->file('.xsd'),
                self::getTestData()->dom('.invalid.xml'),
            ],
            'rng + file = valid'   => [
                true,
                self::getTestData()->file('.rng'),
                self::getTestData()->file('.xml'),
            ],
            'rng + file = invalid' => [
                'Error #38: Did not expect element a there',
                self::getTestData()->file('.rng'),
                self::getTestData()->file('.invalid.xml'),
            ],
            'xsd + file = valid'   => [
                true,
                self::getTestData()->file('.xsd'),
                self::getTestData()->file('.xml'),
            ],
            'xsd + file = invalid' => [
                "Error #1871: Element 'a': This element is not expected. Expected is ( child )",
                self::getTestData()->file('.xsd'),
                self::getTestData()->file('.invalid.xml'),
            ],
        ];
    }
    // </editor-fold>
}
