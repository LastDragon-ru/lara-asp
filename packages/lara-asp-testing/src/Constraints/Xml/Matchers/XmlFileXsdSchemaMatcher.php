<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers;

use Override;
use SplFileInfo;
use XMLReader;

/**
 * @internal
 */
class XmlFileXsdSchemaMatcher extends XmlFileSchemaMatcher {
    #[Override]
    protected function setSchema(SplFileInfo $schema, XMLReader $reader): bool {
        return $reader->setSchema($schema->getPathname());
    }
}
