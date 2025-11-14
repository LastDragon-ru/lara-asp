<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\DependencyResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Hook;

interface HookTask extends Task {
    /**
     * @return non-empty-list<Hook>
     */
    public static function hooks(): array;

    public function __invoke(DependencyResolver $resolver, File $file, Hook $hook): void;
}
