<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use PHPUnit\Framework\Constraint\Constraint as PHPUnitConstraint;
use PHPUnit\Framework\Constraint\IsEqual;
use Psr\Http\Message\ResponseInterface;

class StatusCode extends Response {
    public function __construct(int $statusCode) {
        parent::__construct(new IsEqual($statusCode));
    }

    public function toString(): string {
        return 'has Status Code '.parent::toString();
    }

    protected function isConstraintMatches(ResponseInterface $other, PHPUnitConstraint $constraint): bool {
        return $constraint->evaluate($other->getStatusCode(), '', true);
    }
}
