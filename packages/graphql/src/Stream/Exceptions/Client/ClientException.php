<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\Client;

use GraphQL\Error\ClientAware;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\StreamException;

abstract class ClientException extends StreamException implements ClientAware {
    public function isClientSafe(): bool {
        return true;
    }
}
