<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\ResponseInterface;

use function count;
use function reset;

class Header extends Response {
    protected string $name;

    /**
     * @param array<Constraint> $constraints
     */
    public function __construct(string $name, array $constraints = []) {
        parent::__construct(...$constraints);

        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    protected function matches($other): bool {
        return $other->hasHeader($this->getName())
            && parent::matches($other);
    }

    public function toString(): string {
        return "has {$this->getName()} header".(
            $this->getConstraints() ? ' that '.parent::toString() : ''
            );
    }

    protected function isConstraintMatches(ResponseInterface $other, Constraint $constraint): bool {
        $header  = $other->getHeader($this->getName());
        $header  = count($header) === 1 ? reset($header) : $header;
        $matches = (bool) $constraint->evaluate($header, '', true);

        return $matches;
    }
}
