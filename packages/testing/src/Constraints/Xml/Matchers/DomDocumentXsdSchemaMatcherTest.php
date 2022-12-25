<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers;

use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchemaTest;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers\DomDocumentXsdSchemaMatcher
 */
class DomDocumentXsdSchemaMatcherTest extends TestCase {
    use WithTestData;

    /**
     * @covers ::isMatchesSchema
     */
    public function testEvaluateValid(): void {
        $schema = $this->getTestData(XmlMatchesSchemaTest::class)->file('.xsd');
        $dom    = $this->getTestData(XmlMatchesSchemaTest::class)->dom('.xml');
        $c      = new DomDocumentXsdSchemaMatcher();

        self::assertTrue($c->isMatchesSchema($schema, $dom));
    }

    /**
     * @covers ::isMatchesSchema
     */
    public function testEvaluateInvalid(): void {
        $schema = $this->getTestData(XmlMatchesSchemaTest::class)->file('.xsd');
        $dom    = $this->getTestData(XmlMatchesSchemaTest::class)->dom('.invalid.xml');
        $c      = new DomDocumentXsdSchemaMatcher();

        self::assertFalse($c->isMatchesSchema($schema, $dom));
    }

    /**
     * @covers ::isMatchesSchema
     */
    public function testEvaluateNotDocument(): void {
        $schema = $this->getTestData(XmlMatchesSchemaTest::class)->file('.rng');
        $c      = new DomDocumentXsdSchemaMatcher();

        self::assertFalse($c->isMatchesSchema($schema, new SplFileInfo('tmp')));
    }
}
