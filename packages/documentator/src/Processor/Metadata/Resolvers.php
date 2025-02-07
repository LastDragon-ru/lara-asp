<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Utils\Instances;
use Override;

/**
 * @internal
 * @extends Instances<MetadataResolver<object>>
 */
class Resolvers extends Instances {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getInstanceKeys(object|string $instance): array {
        return $instance::getExtensions();
    }

    #[Override]
    protected function isHighPriorityFirst(): bool {
        return true;
    }
}
