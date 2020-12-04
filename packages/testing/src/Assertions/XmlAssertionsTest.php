<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Utils\TestData;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Assertions\XmlAssertions
 */
class XmlAssertionsTest extends TestCase {
    /**
     * @covers ::assertXmlMatchesSchema
     */
    public function testAssertXmlMatchesSchema(): void {
        $data      = new TestData(XmlMatchesSchema::class);
        $assertion = new class() extends Assert {
            use XmlAssertions;
        };

        $assertion->assertXmlMatchesSchema($data->dom('.xml'), $data->file('.rng'));
        $assertion->assertXmlMatchesSchema($data->dom('.xml'), $data->file('.xsd'));
        $assertion->assertXmlMatchesSchema($data->file('.xml'), $data->file('.rng'));
        $assertion->assertXmlMatchesSchema($data->file('.xml'), $data->file('.xsd'));
    }
}
