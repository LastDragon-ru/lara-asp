<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use RuntimeException;
use SplFileInfo;
use XMLReader;
use function sprintf;

abstract class XmlFileMatchesSchema extends XmlMatchesSchema {
    protected function isMatchesSchema($other): bool {
        // Can?
        if (!($other instanceof SplFileInfo)) {
            return false;
        }

        // Create Reader
        $reader = new XMLReader();

        if (!$reader->open($other->getPathname())) {
            throw new RuntimeException(sprintf("Failed to load XML from `%s`.", $other->getPathname()));
        }

        // Check
        $matches = true;

        try {
            if (!$this->setSchema($reader)) {
                throw new RuntimeException(sprintf("Failed to load XML schema from `%s`.", $this->schema->getPathname()));
            }

            while ($reader->read()) {
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

    protected abstract function setSchema(XMLReader $reader): bool;
}
