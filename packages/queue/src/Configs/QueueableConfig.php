<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Configs;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;
use LastDragon_ru\LaraASP\Core\Utils\ConfigRecursiveMerger;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use ReflectionClass;

/**
 * Queue config.
 *
 * Configurations priority order (last win):
 * - own properties (`$this->connection`, `$this->queue`, etc)
 * - own config from `getQueueConfig()`
 * - app's config (`queue.queueables.<class>` from `config/queue.php` if present)
 * - `onConnection()`, `onQueue()`, etc calls
 */
class QueueableConfig {
    public const Debug = 'debug';

    protected Repository            $global;
    protected ConfigurableQueueable $queueable;
    protected ?array                $config = null;

    public function __construct(Repository $global, ConfigurableQueueable $queueable) {
        $this->global    = $global;
        $this->queueable = $queueable;
    }

    // <editor-fold desc="API">
    // =========================================================================
    public function all(): array {
        return $this->config();
    }

    public function get(string $key) {
        return Arr::get($this->config(), $key);
    }

    public function setting(string $key) {
        return $this->get("settings.{$key}");
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function config(): array {
        if (is_null($this->config)) {
            $global       = $this->global->get($this->getApplicationConfig());
            $config       = $this->queueable->getQueueConfig();
            $target       = $this->getDefaultConfig() + $config;
            $this->config = (new ConfigRecursiveMerger())->merge($target, $config, $global);
        }

        return $this->config;
    }

    public function getDefaultConfig(): array {
        return [
            // FIXME [!] Fill properties
        ];
    }

    protected function getApplicationConfig(): string {
        return "queue.queueables.{$this->getQueueClass()}";
    }

    protected function getQueueClass(): string {
        $class    = get_class($this->queueable);
        $instance = new ReflectionClass($this->queueable);

        while ($instance) {
            if (in_array(QueueableConfig::class, $instance->getInterfaceNames(), true)) {
                $class = $instance->getName();
                break;
            }

            $instance = $instance->getParentClass();
        }

        return $class;
    }
    // </editor-fold>
}
