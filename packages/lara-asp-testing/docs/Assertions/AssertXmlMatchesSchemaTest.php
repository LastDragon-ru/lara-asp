<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Docs\Assertions;

use LastDragon_ru\LaraASP\Testing\Assertions\XmlAssertions;
use LastDragon_ru\PhpUnit\Utils\TempFile;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

/**
 * @internal
 */
#[CoversNothing]
final class AssertXmlMatchesSchemaTest extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use XmlAssertions;

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        $file = new TempFile(
            <<<'XML'
            <?xml version="1.0" encoding="UTF-8" ?>
            <xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
                <xs:simpleType name="UUID">
                    <xs:restriction base="xs:string">
                        <xs:pattern value="[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}"/>
                    </xs:restriction>
                </xs:simpleType>
                <xs:element name="product">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name="id" type="UUID"/>
                            <xs:element name="title" type="xs:string" minOccurs="0"/>
                            <xs:element name="price" type="xs:decimal" minOccurs="0"/>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
            </xs:schema>
            XML,
        );

        self::assertXmlMatchesSchema(
            new SplFileInfo($file->path->path),
            <<<'XML'
            <?xml version="1.0" encoding="UTF-8" ?>
            <product>
                <id>3894f9ef-bde8-45b7-bb16-43d1e29f9115</id>
                <title>Test product</title>
            </product>
            XML,
        );
    }
}
