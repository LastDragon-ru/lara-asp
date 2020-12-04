<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use DOMDocument;
use LastDragon_ru\LaraASP\Testing\Constraints\Xml\DomDocumentMatchesSchemaRelaxNg;
use LastDragon_ru\LaraASP\Testing\Constraints\Xml\DomDocumentMatchesSchemaXsd;
use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlFileMatchesSchemaRelaxNg;
use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlFileMatchesSchemaXsd;
use SplFileInfo;
use function strtolower;

/**
 * @mixin \PHPUnit\Framework\Assert
 */
trait XmlAssertions {
    /**
     * @param \SplFileInfo|\DOMDocument|string $xml
     * @param \SplFileInfo                     $schema
     * @param string                           $message
     *
     * @return void
     */
    public static function assertXmlMatchesSchema($xml, SplFileInfo $schema, string $message = ''): void {
        $xml        = Args::getFile($xml) ?? Args::getDomDocument($xml) ?? Args::invalidXml();
        $schema     = Args::getFile($schema) ?? Args::invalidFile();
        $isRelaxNg  = strtolower($schema->getExtension()) === 'rng';
        $constraint = null;

        if ($xml instanceof DOMDocument) {
            $constraint = $isRelaxNg
                ? new DomDocumentMatchesSchemaRelaxNg($schema)
                : new DomDocumentMatchesSchemaXsd($schema);
        } else {
            $constraint = $isRelaxNg
                ? new XmlFileMatchesSchemaRelaxNg($schema)
                : new XmlFileMatchesSchemaXsd($schema);
        }

        static::assertThat($xml, $constraint, $message);
    }
}
