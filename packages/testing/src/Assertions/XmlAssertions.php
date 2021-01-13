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
     * @param \SplFileInfo|\DOMDocument|string $xml
     * @param \SplFileInfo                     $schema
     * @param string                           $message
     *
     * @return void
     */
    public static function assertXmlMatchesSchema($xml, SplFileInfo $schema, string $message = ''): void {
        static::assertThat($xml, XmlMatchesSchema::create($xml, $schema), $message);
    }
}
