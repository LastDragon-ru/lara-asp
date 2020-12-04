<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use DOMDocument;

class DomDocumentMatchesSchemaXsd extends DomDocumentMatchesSchema {
    protected function isMatchesSchemaValidate(DOMDocument $document): bool {
        return $document->schemaValidate($this->schema->getPathname());
    }
}
