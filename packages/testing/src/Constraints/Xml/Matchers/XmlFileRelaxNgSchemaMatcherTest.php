<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers;

use DOMDocument;
use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchemaTest;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers\XmlFileRelaxNgSchemaMatcher
 */
class XmlFileRelaxNgSchemaMatcherTest extends TestCase {
    use WithTestData;

    /**
     * @covers ::evaluate
     */
    public function testEvaluateValid(): void {
        $schema = $this->getTestData(XmlMatchesSchemaTest::class)->file('.rng');
        $xml    = $this->getTestData(XmlMatchesSchemaTest::class)->file('.xml');
        $c      = new XmlFileRelaxNgSchemaMatcher();

        $this->assertTrue($c->isMatchesSchema($schema, $xml));
    }

    /**
     * @covers ::evaluate
     */
    public function testEvaluateInvalid(): void {
        $schema = $this->getTestData(XmlMatchesSchemaTest::class)->file('.rng');
        $xml    = $this->getTestData(XmlMatchesSchemaTest::class)->file('.invalid.xml');
        $c      = new XmlFileRelaxNgSchemaMatcher();

        $this->assertFalse($c->isMatchesSchema($schema, $xml));
    }

    /**
     * @covers ::evaluate
     */
    public function testEvaluateNotDocument(): void {
        $schema = $this->getTestData(XmlMatchesSchemaTest::class)->file('.rng');
        $c      = new XmlFileRelaxNgSchemaMatcher();

        $this->assertFalse($c->isMatchesSchema($schema, new DOMDocument()));
    }
}
