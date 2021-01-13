<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers;

use SplFileInfo;
use XMLReader;

/**
 * @internal
 */
class XmlFileXsdSchemaMatcher extends XmlFileSchemaMatcher {
    protected function setSchema(SplFileInfo $schema, XMLReader $reader): bool {
        return $reader->setSchema($schema->getPathname());
    }
}
