<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers;

use DOMDocument;
use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlSchemaMatcher;
use RuntimeException;
use SplFileInfo;
use XMLReader;

use function sprintf;

/**
 * @internal
 */
abstract class XmlFileSchemaMatcher implements XmlSchemaMatcher {
    public function isMatchesSchema(SplFileInfo $schema, SplFileInfo|DOMDocument $xml): bool {
        // Can?
        if (!($xml instanceof SplFileInfo)) {
            return false;
        }

        // Create Reader
        $reader = new XMLReader();

        if (!$reader->open($xml->getPathname())) {
            throw new RuntimeException(sprintf("Failed to load XML from `%s`.", $xml->getPathname()));
        }

        // Check
        $matches = true;

        try {
            if (!$this->setSchema($schema, $reader)) {
                throw new RuntimeException(sprintf("Failed to load XML schema from `%s`.", $schema->getPathname()));
            }

            while (@$reader->read()) {
                if (!$reader->isValid()) {
                    $matches = false;
                    break;
                }
            }
        } finally {
            $reader->close();
        }

        // Return
        return $matches;
    }

    protected abstract function setSchema(SplFileInfo $schema, XMLReader $reader): bool;
}
