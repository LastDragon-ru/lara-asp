<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use DOMDocument;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function is_string;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchema
 */
class XmlMatchesSchemaTest extends TestCase {
    use WithTestData;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderEvaluate
     */
    public function testEvaluate(bool|string $expected, SplFileInfo $schema, DOMDocument|SplFileInfo $xml): void {
        $constraint = new class($schema) extends XmlMatchesSchema {
            /**
             * @inheritDoc
             */
            public function additionalFailureDescription($other): string {
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
     * @return array<mixed>
     */
    public function dataProviderEvaluate(): array {
        return [
            'rng + dom = valid'    => [
                true,
                $this->getTestData()->file('.rng'),
                $this->getTestData()->dom('.xml'),
            ],
            'rng + dom = invalid'  => [
                'Error #38: Did not expect element a there',
                $this->getTestData()->file('.rng'),
                $this->getTestData()->dom('.invalid.xml'),
            ],
            'xsd + dom = valid'    => [
                true,
                $this->getTestData()->file('.xsd'),
                $this->getTestData()->dom('.xml'),
            ],
            'xsd + dom = invalid'  => [
                "Error #1871: Element 'a': This element is not expected. Expected is ( child )",
                $this->getTestData()->file('.xsd'),
                $this->getTestData()->dom('.invalid.xml'),
            ],
            'rng + file = valid'   => [
                true,
                $this->getTestData()->file('.rng'),
                $this->getTestData()->file('.xml'),
            ],
            'rng + file = invalid' => [
                'Error #38: Did not expect element a there',
                $this->getTestData()->file('.rng'),
                $this->getTestData()->file('.invalid.xml'),
            ],
            'xsd + file = valid'   => [
                true,
                $this->getTestData()->file('.xsd'),
                $this->getTestData()->file('.xml'),
            ],
            'xsd + file = invalid' => [
                "Error #1871: Element 'a': This element is not expected. Expected is ( child )",
                $this->getTestData()->file('.xsd'),
                $this->getTestData()->file('.invalid.xml'),
            ],
        ];
    }
    // </editor-fold>
}
