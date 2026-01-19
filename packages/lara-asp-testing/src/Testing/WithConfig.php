<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Testing;

use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\Core\Application\Configuration\ConfigurationResolver;
use LastDragon_ru\PhpUnit\Utils\TempFile;

use function is_array;
use function is_callable;
use function var_export;

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
 *
 * @phpstan-require-extends TestCase
 */
trait WithConfig {
    /**
     * @param SettingsFactory $settings
     */
    public function setConfig(callable|array|null $settings): void {
        $repository = $this->app()->make(Repository::class);
        $settings   = is_callable($settings) && !is_array($settings) ? $settings($this, $repository) : $settings;

        if ($settings !== null) {
            $repository->set($settings);
        }
    }

    /**
     * @template T of Configuration
     *
     * @param class-string<ConfigurationResolver<T>> $resolver
     * @param callable(T): void|null                 $callback
     */
    public function setConfiguration(string $resolver, ?callable $callback): void {
        if ($callback !== null) {
            $callback($this->app()->make($resolver)->getInstance());
        }
    }

    /**
     * @template T of Configuration
     *
     * @param class-string<ConfigurationResolver<T>> $resolver
     */
    public function assertConfigurationExportable(string $resolver): void {
        $config   = $resolver::getDefaultConfig();
        $exported = var_export($config, true);
        $exported = "<?php declare(strict_types = 1);\n\nreturn {$exported};";
        $imported = new TempFile($exported);
        $imported = require $imported->path->path;

        self::assertEquals($config, $imported);
    }
}
