<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use DOMDocument;
use SplFileInfo;

/**
 * @internal
 */
interface XmlSchemaMatcher {
    public function isMatchesSchema(SplFileInfo $schema, SplFileInfo|DOMDocument $xml): bool;
}
