<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints;

use Closure;
use PHPUnit\Framework\Constraint\Constraint;

class ClosureConstraint extends Constraint {
    private Closure $closure;

    public function __construct(Closure $closure) {
        $this->closure = $closure;
    }

    protected function getClosure(): Closure {
        return $this->closure;
    }

    /**
     * @inheritdoc
     */
    protected function matches($other): bool {
        return $this->getClosure()($other);
    }

    public function toString(): string {
        return 'matches closure';
    }
}
