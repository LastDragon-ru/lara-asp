<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use DOMDocument;

abstract class DomDocumentMatchesSchema extends XmlMatchesSchema {
    protected function isMatchesSchema($other): bool {
        return $other instanceof DOMDocument
            && $this->isMatchesSchemaValidate($other);
    }

    protected abstract function isMatchesSchemaValidate(DOMDocument $document): bool;
}
