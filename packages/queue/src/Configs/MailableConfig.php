<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Configs;

use Override;

class MailableConfig extends QueueableConfig {
    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function getDefaultConfig(): array {
        /** SEE {@link \Illuminate\Mail\SendQueuedMailable} */
        $config = parent::getDefaultConfig();

        unset($config['retryUntil']);
        unset($config['maxExceptions']);
        unset($config['deleteWhenMissingModels']);

        return $config;
    }
}
