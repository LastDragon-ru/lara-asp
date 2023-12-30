<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\BuilderUnknown;

// @phpcs:disable Generic.Files.LineLength.TooLong

/**
 * Can be used with a directive to define the builder type in case when
 * auto-detection doesn't work.
 *
 * @see BuilderUnknown
 */
interface BuilderInfoProvider {
    public function getBuilderInfo(TypeSource $source): ?BuilderInfo;
}
