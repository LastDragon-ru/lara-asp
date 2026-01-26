<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Xml;

use DOMDocument;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\PhpUnit\Xml\Constraints\XmlMatchesSchema;
use PHPUnit\Framework\Assert;

/**
 * @mixin Assert
 */
trait Assertions {
    /**
     * Asserts that XML matches schema.
     *
     * @see XmlMatchesSchema
     */
    public static function assertXmlMatchesSchema(
        FilePath $schema,
        FilePath|DOMDocument $xml,
        string $message = '',
    ): void {
        static::assertFileExists($schema->path);

        if ($xml instanceof FilePath) {
            static::assertFileExists($xml->path);
        }

        static::assertThat($xml, new XmlMatchesSchema($schema), $message);
    }
}
