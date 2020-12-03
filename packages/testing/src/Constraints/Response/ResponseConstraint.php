<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use LastDragon_ru\LaraASP\Testing\Providers\CompositeExpectedImpl;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeExpectedInterface;
use PHPUnit\Framework\Constraint\LogicalAnd;

class ResponseConstraint extends Constraint implements CompositeExpectedInterface {
    use CompositeExpectedImpl;

    protected LogicalAnd $constraint;

    public function __construct(Constraint ...$constraints) {
        $this->constraint = LogicalAnd::fromConstraints($constraints);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $other
     *
     * @return bool
     */
    protected function matches($other): bool {
        return $this->constraint->evaluate($other);
    }

    public function toString(): string {
        return $this->constraint->toString();
    }
}
