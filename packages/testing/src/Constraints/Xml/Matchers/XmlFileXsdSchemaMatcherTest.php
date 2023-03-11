<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers;

use DOMDocument;
use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchemaTest;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers\XmlFileXsdSchemaMatcher
 */
class XmlFileXsdSchemaMatcherTest extends TestCase {
    use WithTestData;

    public function testEvaluateValid(): void {
        $schema = self::getTestData(XmlMatchesSchemaTest::class)->file('.xsd');
        $xml    = self::getTestData(XmlMatchesSchemaTest::class)->file('.xml');
        $c      = new XmlFileXsdSchemaMatcher();

        self::assertTrue($c->isMatchesSchema($schema, $xml));
    }

    public function testEvaluateInvalid(): void {
        $schema = self::getTestData(XmlMatchesSchemaTest::class)->file('.xsd');
        $xml    = self::getTestData(XmlMatchesSchemaTest::class)->file('.invalid.xml');
        $c      = new XmlFileXsdSchemaMatcher();

        self::assertFalse($c->isMatchesSchema($schema, $xml));
    }

    public function testEvaluateNotDocument(): void {
        $schema = self::getTestData(XmlMatchesSchemaTest::class)->file('.rng');
        $c      = new XmlFileXsdSchemaMatcher();

        self::assertFalse($c->isMatchesSchema($schema, new DOMDocument()));
    }
}
