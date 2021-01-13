<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

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
     *
     * @param \SplFileInfo                     $schema
     * @param \SplFileInfo|\DOMDocument|string $xml
     * @param string                           $message
     *
     * @return void
     */
    public static function assertXmlMatchesSchema(SplFileInfo $schema, $xml, string $message = ''): void {
        static::assertThat($xml, new XmlMatchesSchema($schema), $message);
    }
}
