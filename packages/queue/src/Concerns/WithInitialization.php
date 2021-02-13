<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use Closure;
use LastDragon_ru\LaraASP\Queue\Contracts\Initializable;
use RuntimeException;

use function get_class;
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

    protected function ifInitialized(Closure $closure) {
        if ($this instanceof Initializable && !$this->isInitialized()) {
            throw new RuntimeException(sprintf('The `%s` is not initialized.', get_class($this)));
        }

        return $closure();
    }
}
