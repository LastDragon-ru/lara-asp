<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use Closure;
use LastDragon_ru\LaraASP\Queue\Contracts\Initializable;
use RuntimeException;

use function sprintf;

trait WithInitialization {
    private bool $isInitialized = false;

    public function isInitialized(): bool {
        return $this->isInitialized;
    }

    protected function initialized(): self {
        $this->isInitialized = true;

        return $this;
    }

    /**
     * @template T
     *
     * @param Closure(): T $closure
     *
     * @return T
     */
    protected function ifInitialized(Closure $closure): mixed {
        if ($this instanceof Initializable && !$this->isInitialized()) {
            throw new RuntimeException(sprintf('The `%s` is not initialized.', static::class));
        }

        return $closure();
    }
}
