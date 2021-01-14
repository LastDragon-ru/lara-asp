<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

class StatusCode extends Constraint {
    private int $statusCode;

    public function __construct(int $statusCode) {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    /**
     * @param \Illuminate\Testing\TestResponse $other
     *
     * @return bool
     */
    protected function matches($other): bool {
        return $this->getStatusCode() === $other->getStatusCode();
    }

    public function toString(): string {
        return "has Status Code is equal to {$this->getStatusCode()}";
    }
}
