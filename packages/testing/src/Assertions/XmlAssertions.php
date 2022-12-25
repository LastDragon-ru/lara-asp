<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use DOMDocument;
use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchema;
use PHPUnit\Framework\Assert;
use SplFileInfo;

/**
 * @mixin Assert
 */
trait XmlAssertions {
    /**
     * Asserts that XML matches schema.
     *
     * @see XmlMatchesSchema
     */
    public static function assertXmlMatchesSchema(
        SplFileInfo $schema,
        SplFileInfo|DOMDocument|string $xml,
        string $message = '',
    ): void {
        static::assertThat($xml, new XmlMatchesSchema($schema), $message);
    }
}
