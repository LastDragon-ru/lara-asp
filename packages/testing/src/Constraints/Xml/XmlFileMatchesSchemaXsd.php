<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use XMLReader;

class XmlFileMatchesSchemaXsd extends XmlFileMatchesSchema {
    protected function setSchema(XMLReader $reader): bool {
        return $reader->setSchema($this->schema->getPathname());
    }
}
