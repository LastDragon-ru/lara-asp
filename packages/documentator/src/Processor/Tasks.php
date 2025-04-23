<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Hook;
use LastDragon_ru\LaraASP\Documentator\Utils\Instances;
use Override;

use function array_map;

/**
 * @internal
 * @extends Instances<Task>
 */
class Tasks extends Instances {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getInstanceKeys(object|string $instance): array {
        $extensions = $instance::getExtensions();
        $extensions = array_map(
            static function (Hook|string $extension): string {
                return $extension instanceof Hook ? $extension->value : $extension;
            },
            $extensions,
        );

        return $extensions;
    }
}
