<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Constraints\Xml\DomDocumentMatchesSchemaRelaxNg
 */
class DomDocumentMatchesSchemaRelaxNgTest extends TestCase {
    use WithTestData;

    /**
     * @covers ::evaluate
     */
    public function testEvaluateValid(): void {
        $schema = $this->getTestData(XmlMatchesSchema::class)->file('.rng');
        $dom    = $this->getTestData(XmlMatchesSchema::class)->dom('.xml');
        $c      = new DomDocumentMatchesSchemaRelaxNg($schema);

        $this->assertTrue($c->evaluate($dom, '', true));
    }

    /**
     * @covers ::evaluate
     */
    public function testEvaluateInValid(): void {
        $schema = $this->getTestData(XmlMatchesSchema::class)->file('.rng');
        $dom    = $this->getTestData(XmlMatchesSchema::class)->dom('.invalid.xml');
        $c      = new class($schema) extends DomDocumentMatchesSchemaRelaxNg {
            public function additionalFailureDescription($other): string {
                return parent::additionalFailureDescription($other);
            }
        };

        $this->assertFalse($c->evaluate($dom, '', true));
        $this->assertStringContainsString('Error #38: Did not expect element a there', $c->additionalFailureDescription($dom));
    }

    /**
     * @covers ::evaluate
     */
    public function testEvaluateNotDocument(): void {
        $schema = $this->getTestData(XmlMatchesSchema::class)->file('.rng');
        $c      = new DomDocumentMatchesSchemaRelaxNg($schema);

        $this->assertFalse($c->evaluate(1, '', true));
        $this->assertFalse($c->evaluate(new stdClass(), '', true));
    }
}
