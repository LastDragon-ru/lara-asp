<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchemaTest;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use LastDragon_ru\LaraASP\Testing\Utils\TestData;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @deprecated %{VERSION}
 * @internal
 */
#[CoversClass(XmlAssertions::class)]
final class XmlAssertionsTest extends TestCase {
    public function testAssertXmlMatchesSchema(): void {
        $data      = new TestData(XmlMatchesSchemaTest::class);
        $assertion = new class() extends Assert {
            use XmlAssertions;
        };

        $assertion::assertXmlMatchesSchema($data->file('.rng'), $data->dom('.xml'));
        $assertion::assertXmlMatchesSchema($data->file('.xsd'), $data->dom('.xml'));
        $assertion::assertXmlMatchesSchema($data->file('.rng'), $data->file('.xml'));
        $assertion::assertXmlMatchesSchema($data->file('.xsd'), $data->file('.xml'));
    }
}
