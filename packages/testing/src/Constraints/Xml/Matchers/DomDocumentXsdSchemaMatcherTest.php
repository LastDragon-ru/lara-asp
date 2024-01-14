<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers;

use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchemaTest;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SplFileInfo;

/**
 * @internal
 */
#[CoversClass(DomDocumentXsdSchemaMatcher::class)]
final class DomDocumentXsdSchemaMatcherTest extends TestCase {
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
