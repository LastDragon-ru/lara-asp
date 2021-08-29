<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Configs;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;
use LastDragon_ru\LaraASP\Queue\Concerns\WithConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use ReflectionClass;

use function get_class;
use function in_array;
use function is_null;

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
    /**
     * @var array<string, mixed>|null
     */
    protected ?array $config = null;

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        protected Container $container,
        protected Repository $global,
        protected ConfigurableQueueable $queueable,
        protected array $properties,
    ) {
        // empty
    }

    // <editor-fold desc="API">
    // =========================================================================
    /**
     * @return array<string,mixed>
     */
    public function all(): array {
        return $this->config();
    }

    public function get(string $key, mixed $default = null): mixed {
        return Arr::get($this->config(), $key, $default);
    }

    public function setting(string $key): mixed {
        return $this->get("settings.{$key}");
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    /**
     * @return array<string,mixed>
     */
    protected function config(): array {
        if (is_null($this->config)) {
            $global       = (array) $this->global->get($this->getApplicationConfig());
            $config       = $this->queueable->getQueueConfig();
            $target       = $this->getDefaultConfig() + $config;
            $this->config = (new ConfigMerger())->merge($target, $config, $global);
        }

        return $this->config;
    }

    /**
     * @return array<string,mixed>
     */
    public function getDefaultConfig(): array {
        return $this->properties;
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
