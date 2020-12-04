<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use XMLReader;

class XmlFileMatchesSchemaRelaxNg extends XmlFileMatchesSchema {
    protected function setSchema(XMLReader $reader): bool {
        return $reader->setRelaxNGSchema($this->schema->getPathname());
    }
}
