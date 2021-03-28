<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use GraphQL\Error\ClientAware;
use LastDragon_ru\LaraASP\GraphQL\Package;

class SearchLogicException extends SearchByException implements ClientAware {
    public function isClientSafe(): bool {
        return true;
    }

    public function getCategory(): string {
        return Package::Name;
    }
}
