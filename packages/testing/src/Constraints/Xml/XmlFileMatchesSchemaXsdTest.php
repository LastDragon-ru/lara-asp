<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlFileMatchesSchemaXsd
 */
class XmlFileMatchesSchemaXsdTest extends TestCase {
    use WithTestData;

    /**
     * @covers ::evaluate
     */
    public function testEvaluateValid(): void {
        $schema = $this->getTestData(XmlMatchesSchema::class)->file('.xsd');
        $xml    = $this->getTestData(XmlMatchesSchema::class)->file('.xml');
        $c      = new XmlFileMatchesSchemaXsd($schema);

        $this->assertTrue($c->evaluate($xml, '', true));
    }

    /**
     * @covers ::evaluate
     */
    public function testEvaluateInValid(): void {
        $schema = $this->getTestData(XmlMatchesSchema::class)->file('.xsd');
        $xml    = $this->getTestData(XmlMatchesSchema::class)->file('.invalid.xml');
        $c      = new class($schema) extends XmlFileMatchesSchemaXsd {
            public function additionalFailureDescription($other): string {
                return parent::additionalFailureDescription($other);
            }
        };

        $this->assertFalse($c->evaluate($xml, '', true));
        $this->assertStringContainsString(
            "Error #1871: Element 'a': This element is not expected. Expected is ( child )",
            $c->additionalFailureDescription($xml)
        );
    }

    /**
     * @covers ::evaluate
     */
    public function testEvaluateNotDocument(): void {
        $schema = $this->getTestData(XmlMatchesSchema::class)->file('.rng');
        $c      = new XmlFileMatchesSchemaXsd($schema);

        $this->assertFalse($c->evaluate(1, '', true));
        $this->assertFalse($c->evaluate(new stdClass(), '', true));
    }
}
