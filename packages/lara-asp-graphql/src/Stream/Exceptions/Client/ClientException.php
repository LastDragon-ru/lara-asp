<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\Client;

use GraphQL\Error\ClientAware;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\StreamException;
use Override;

abstract class ClientException extends StreamException implements ClientAware {
    #[Override]
    public function isClientSafe(): bool {
        return true;
    }
}
