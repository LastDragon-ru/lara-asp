<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\ResponseInterface;

use function count;
use function reset;

class Header extends Response {
    /**
     * @param array<array-key, Constraint> $constraints
     */
    public function __construct(
        protected string $name,
        array $constraints = [],
    ) {
        parent::__construct(...$constraints);
    }

    public function getName(): string {
        return $this->name;
    }

    protected function matches(mixed $other): bool {
        return parent::matches($other)
            && $other instanceof ResponseInterface
            && $other->hasHeader($this->getName());
    }

    public function toString(): string {
        return "has {$this->getName()} header".(
            $this->getConstraints() ? ' that '.parent::toString() : ''
            );
    }

    protected function isConstraintMatches(
        ResponseInterface $other,
        Constraint $constraint,
        bool $return = false,
    ): ?bool {
        $header  = $other->getHeader($this->getName());
        $header  = count($header) === 1 ? reset($header) : $header;
        $matches = $constraint->evaluate($header, '', $return);

        return $matches;
    }
}
