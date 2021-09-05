<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Configs;

class CronableConfig extends QueueableConfig {
    public const Cron     = 'cron';
    public const Enabled  = 'enabled';
    public const Timezone = 'timezone';

    /**
     * @return array<string,mixed>
     */
    public function getDefaultConfig(): array {
        return parent::getDefaultConfig() + [
                (string) static::Cron     => '* * * * *', // = every minute
                (string) static::Enabled  => true,
                (string) static::Timezone => null,
            ];
    }
}
