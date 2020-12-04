<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use DOMDocument;

class DomDocumentMatchesSchemaRelaxNg extends DomDocumentMatchesSchema {
    protected function isMatchesSchemaValidate(DOMDocument $document): bool {
        return $document->relaxNGValidate($this->schema->getPathname());
    }
}
