<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\Traits;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use Override;

use function mb_strrpos;
use function mb_substr;

/**
 * @phpstan-require-implements Link
 */
trait ClassTitle {
    #[Override]
    public function getTitle(): ?string {
        $title    = (string) $this;
        $position = mb_strrpos($title, '\\');

        if ($position !== false) {
            $title = mb_substr($title, $position + 1);
        }

        return $title ?: null;
    }
}
