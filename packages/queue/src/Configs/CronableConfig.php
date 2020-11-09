<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Configs;

class CronableConfig extends QueueableConfig {
    public const Cron    = 'cron';
    public const Enabled = 'enabled';

    public function getDefaultConfig(): array {
        return parent::getDefaultConfig() + [
                static::Cron    => '* * * * *', // = every minute
                static::Enabled => true,
            ];
    }
}
