<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints;

use Closure;
use Override;
use PHPUnit\Framework\Constraint\Constraint;

/**
 * @phpstan-type TClosure Closure(mixed): bool
 */
class ClosureConstraint extends Constraint {
    /**
     * @var TClosure
     */
    private Closure $closure;

    /**
     * @param TClosure $closure
     */
    public function __construct(Closure $closure) {
        $this->closure = $closure;
    }

    /**
     * @return TClosure
     */
    protected function getClosure(): Closure {
        return $this->closure;
    }

    #[Override]
    protected function matches(mixed $other): bool {
        return $this->getClosure()($other);
    }

    #[Override]
    public function toString(): string {
        return 'matches closure';
    }
}
