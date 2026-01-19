<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\OutputCleanup;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Hook;
use Override;

/**
 * Removes all files in `output` directory. It will do nothing if `output` is
 * equal to `input`.
 */
class Task implements HookTask {
    #[Override]
    public static function hook(): Hook {
        return Hook::Before;
    }

    #[Override]
    public function __invoke(Resolver $resolver, File $file, Hook $hook): void {
        if ($resolver->input->equals($resolver->output)) {
            return;
        }

        $resolver->delete(
            $resolver->search($resolver->output, include: '*', hidden: true),
        );
    }
}
