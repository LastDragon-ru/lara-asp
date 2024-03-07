# `assertXmlMatchesSchema`

Asserts that XML matches schema [XSD](https://en.wikipedia.org/wiki/XML_Schema_(W3C)) or [Relax NG](https://en.wikipedia.org/wiki/RELAX_NG). Validation based on the standard methods of [`DOMDocument`](https://www.php.net/manual/en/class.domdocument.php) class.

[include:example]: ./AssertXmlMatchesSchema.php
[//]: # (start: 7c444c9c4ee0ea7f25c8f70fcefd4825f6873579ba5c8ac884f69fcc93ba5024)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Docs\Assertions;

use LastDragon_ru\LaraASP\Testing\Assertions\XmlAssertions;
use LastDragon_ru\LaraASP\Testing\Utils\WithTempFile;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
final class AssertXmlMatchesSchema extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use XmlAssertions;
    use WithTempFile;

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        self::assertXmlMatchesSchema(
            self::getTempFile(
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
            ),
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
```

Example output:

```plain
OK (1 test, 1 assertion)
```

[//]: # (end: 7c444c9c4ee0ea7f25c8f70fcefd4825f6873579ba5c8ac884f69fcc93ba5024)
