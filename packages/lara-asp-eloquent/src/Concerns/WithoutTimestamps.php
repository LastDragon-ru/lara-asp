<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @mixin Model
 */
trait WithoutTimestamps {
    /**
     * @noinspection PhpMissingReturnTypeInspection
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint
     */
    #[Override]
    public function usesTimestamps() {
        return false;
    }
}
