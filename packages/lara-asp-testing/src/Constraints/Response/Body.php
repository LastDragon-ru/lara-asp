<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use Override;
use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\ResponseInterface;

class Body extends Response {
    #[Override]
    protected function isConstraintMatches(
        ResponseInterface $other,
        Constraint $constraint,
        bool $return = false,
    ): ?bool {
        return $constraint->evaluate((string) $other->getBody(), '', $return);
    }
}
