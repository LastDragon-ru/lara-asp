<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions;

use GraphQL\Error\ClientAware;
use LastDragon_ru\LaraASP\GraphQL\Package;

abstract class SortLogicException extends SortByException implements ClientAware {
    public function isClientSafe(): bool {
        return true;
    }

    public function getCategory(): string {
        return Package::Name;
    }
}
