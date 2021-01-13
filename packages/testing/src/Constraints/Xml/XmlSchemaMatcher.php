<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use SplFileInfo;

/**
 * @internal
 */
interface XmlSchemaMatcher {
    /**
     * @param \SplFileInfo              $schema
     * @param \SplFileInfo|\DOMDocument $xml
     *
     * @return bool
     */
    public function isMatchesSchema(SplFileInfo $schema, $xml): bool;
}
