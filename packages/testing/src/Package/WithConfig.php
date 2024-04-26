<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Package;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;

use function is_callable;

/**
 * Allows replacing settings for Laravel.
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
    abstract protected function app(): Application;

    /**
     * @param SettingsFactory $settings
     */
    public function setConfig(callable|array|null $settings): void {
        $repository = $this->app()->make(Repository::class);
        $settings   = is_callable($settings) ? $settings($this, $repository) : $settings;

        if ($settings !== null) {
            $repository->set($settings);
        }
    }
}
