<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Package;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;

use function is_callable;

/**
 * Allows to replace settings for Laravel.
 *
 * Required to avoid dependency from `Illuminate\Foundation\*` (`config()`).
 *
 * @internal
 *
 * @phpstan-type Settings         array<string,mixed>
 * @phpstan-type SettingsCallback callable(static, Repository): Settings
 * @phpstan-type SettingsFactory  SettingsCallback|Settings|null
 */
trait WithConfig {
    /**
     * @param SettingsFactory $settings
     */
    public function setConfig(callable|array|null $settings): void {
        $repository = Container::getInstance()->make(Repository::class);
        $settings   = is_callable($settings) ? $settings($this, $repository) : $settings;

        if ($settings !== null) {
            $repository->set($settings);
        }
    }
}
