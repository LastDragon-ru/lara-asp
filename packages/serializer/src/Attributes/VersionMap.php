<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Attributes;

use Attribute;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

#[Attribute(Attribute::TARGET_CLASS)]
class VersionMap extends DiscriminatorMap {
    /**
     * @param array<string, class-string> $mapping
     */
    public function __construct(array $mapping) {
        parent::__construct('$v', $mapping);
    }
}
