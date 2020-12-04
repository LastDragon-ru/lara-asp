<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Constraints\Xml\DomDocumentMatchesSchemaXsd
 */
class DomDocumentMatchesSchemaXsdTest extends TestCase {
    use WithTestData;

    /**
     * @covers ::evaluate
     */
    public function testEvaluateValid(): void {
        $schema = $this->getTestData(XmlMatchesSchema::class)->file('.xsd');
        $dom    = $this->getTestData(XmlMatchesSchema::class)->dom('.xml');
        $c      = new DomDocumentMatchesSchemaXsd($schema);

        $this->assertTrue($c->evaluate($dom, '', true));
    }

    /**
     * @covers ::evaluate
     */
    public function testEvaluateInValid(): void {
        $schema = $this->getTestData(XmlMatchesSchema::class)->file('.xsd');
        $dom    = $this->getTestData(XmlMatchesSchema::class)->dom('.invalid.xml');
        $c      = new class($schema) extends DomDocumentMatchesSchemaXsd {
            public function additionalFailureDescription($other): string {
                return parent::additionalFailureDescription($other);
            }
        };

        $this->assertFalse($c->evaluate($dom, '', true));
        $this->assertStringContainsString(
            "Error #1871: Element 'a': This element is not expected. Expected is ( child )",
            $c->additionalFailureDescription($dom)
        );
    }

    /**
     * @covers ::evaluate
     */
    public function testEvaluateNotDocument(): void {
        $schema = $this->getTestData(XmlMatchesSchema::class)->file('.rng');
        $c      = new DomDocumentMatchesSchemaXsd($schema);

        $this->assertFalse($c->evaluate(1, '', true));
        $this->assertFalse($c->evaluate(new stdClass(), '', true));
    }
}
