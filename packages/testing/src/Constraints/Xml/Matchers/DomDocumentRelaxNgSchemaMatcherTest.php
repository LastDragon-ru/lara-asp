<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers;

use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchemaTest;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SplFileInfo;

/**
 * @internal
 */
#[CoversClass(DomDocumentRelaxNgSchemaMatcher::class)]
final class DomDocumentRelaxNgSchemaMatcherTest extends TestCase {
    public function testEvaluateValid(): void {
        $schema = self::getTestData(XmlMatchesSchemaTest::class)->file('.rng');
        $dom    = self::getTestData(XmlMatchesSchemaTest::class)->dom('.xml');
        $c      = new DomDocumentRelaxNgSchemaMatcher();

        self::assertTrue($c->isMatchesSchema($schema, $dom));
    }

    public function testEvaluateInvalid(): void {
        $schema = self::getTestData(XmlMatchesSchemaTest::class)->file('.rng');
        $dom    = self::getTestData(XmlMatchesSchemaTest::class)->dom('.invalid.xml');
        $c      = new DomDocumentRelaxNgSchemaMatcher();

        self::assertFalse($c->isMatchesSchema($schema, $dom));
    }

    public function testEvaluateNotDocument(): void {
        $schema = self::getTestData(XmlMatchesSchemaTest::class)->file('.rng');
        $c      = new DomDocumentRelaxNgSchemaMatcher();

        self::assertFalse($c->isMatchesSchema($schema, new SplFileInfo('tmp')));
    }
}
