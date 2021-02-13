<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use DOMDocument;
use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchema;
use SplFileInfo;

/**
 * @mixin \PHPUnit\Framework\Assert
 */
trait XmlAssertions {
    /**
     * Asserts that XML matches schema.
     *
     * @see \LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchema
     */
    public static function assertXmlMatchesSchema(
        SplFileInfo $schema,
        SplFileInfo|DOMDocument|string $xml,
        string $message = '',
    ): void {
        static::assertThat($xml, new XmlMatchesSchema($schema), $message);
    }
}
