<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers;

use DOMDocument;
use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlSchemaMatcher;
use SplFileInfo;

/**
 * @internal
 */
abstract class DomDocumentSchemaMatcher implements XmlSchemaMatcher {
    public function isMatchesSchema(SplFileInfo $schema, SplFileInfo|DOMDocument $xml): bool {
        return $xml instanceof DOMDocument
            && $this->isMatchesSchemaValidate($schema, $xml);
    }

    protected abstract function isMatchesSchemaValidate(SplFileInfo $schema, DOMDocument $document): bool;
}
