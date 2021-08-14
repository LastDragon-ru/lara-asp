<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\ResponseInterface;

class Body extends Response {
    protected function isConstraintMatches(ResponseInterface $other, Constraint $constraint): bool {
        return (bool) $constraint->evaluate((string) $other->getBody(), '', true);
    }
}
