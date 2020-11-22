<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Configs;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;
use LastDragon_ru\LaraASP\Core\Utils\ConfigRecursiveMerger;
use LastDragon_ru\LaraASP\Queue\Concerns\WithConfig;
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
    protected array                 $properties;
    protected ?array                $config = null;

    public function __construct(Repository $global, ConfigurableQueueable $queueable, array $properties) {
        $this->global     = $global;
        $this->queueable  = $queueable;
        $this->properties = $properties;
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
            $global       = (array) $this->global->get($this->getApplicationConfig());
            $config       = (array) $this->queueable->getQueueConfig();
            $target       = $this->getDefaultConfig() + $config;
            $this->config = (new ConfigRecursiveMerger())->merge($target, $config, $global);
        }

        return $this->config;
    }

    public function getDefaultConfig(): array {
        return $this->properties + [
                static::Debug => false, // Not used directly, but you may use it for debug the job
            ];
    }

    protected function getApplicationConfig(): string {
        return "queue.queueables.{$this->getQueueClass()}";
    }

    protected function getQueueClass(): string {
        $class    = get_class($this->queueable);
        $instance = new ReflectionClass($this->queueable);

        while ($instance) {
            if (!$instance->isAbstract() && in_array(WithConfig::class, $instance->getTraitNames(), true)) {
                $class = $instance->getName();
                break;
            }

            $instance = $instance->getParentClass();
        }

        return $class;
    }
    // </editor-fold>
}
