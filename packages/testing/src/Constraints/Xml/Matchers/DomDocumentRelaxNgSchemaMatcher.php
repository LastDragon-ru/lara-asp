<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers;

use DOMDocument;
use SplFileInfo;

/**
 * @internal
 */
class DomDocumentRelaxNgSchemaMatcher extends DomDocumentSchemaMatcher {
    protected function isMatchesSchemaValidate(SplFileInfo $schema, DOMDocument $document): bool {
        return @$document->relaxNGValidate($schema->getPathname());
    }
}
