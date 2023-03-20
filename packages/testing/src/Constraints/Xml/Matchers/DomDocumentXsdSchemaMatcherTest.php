<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers;

use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchemaTest;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers\DomDocumentXsdSchemaMatcher
 */
class DomDocumentXsdSchemaMatcherTest extends TestCase {
    use WithTestData;

    public function testEvaluateValid(): void {
        $schema = self::getTestData(XmlMatchesSchemaTest::class)->file('.xsd');
        $dom    = self::getTestData(XmlMatchesSchemaTest::class)->dom('.xml');
        $c      = new DomDocumentXsdSchemaMatcher();

        self::assertTrue($c->isMatchesSchema($schema, $dom));
    }

    public function testEvaluateInvalid(): void {
        $schema = self::getTestData(XmlMatchesSchemaTest::class)->file('.xsd');
        $dom    = self::getTestData(XmlMatchesSchemaTest::class)->dom('.invalid.xml');
        $c      = new DomDocumentXsdSchemaMatcher();

        self::assertFalse($c->isMatchesSchema($schema, $dom));
    }

    public function testEvaluateNotDocument(): void {
        $schema = self::getTestData(XmlMatchesSchemaTest::class)->file('.rng');
        $c      = new DomDocumentXsdSchemaMatcher();

        self::assertFalse($c->isMatchesSchema($schema, new SplFileInfo('tmp')));
    }
}
