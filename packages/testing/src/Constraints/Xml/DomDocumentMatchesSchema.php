<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use DOMDocument;

abstract class DomDocumentMatchesSchema extends XmlMatchesSchema {
    protected function isMatchesSchema($reader): bool {
        return $reader instanceof DOMDocument
            && $this->isMatchesSchemaValidate($reader);
    }

    protected abstract function isMatchesSchemaValidate(DOMDocument $document): bool;
}
