<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use function mb_strtolower;

class ContentType extends Constraint {
    private string $contentType;

    public function __construct(string $contentType) {
        $this->contentType = $contentType;
    }

    public function getContentType(): string {
        return $this->contentType;
    }

    /**
     * @param \Illuminate\Testing\TestResponse $other
     *
     * @return bool
     */
    protected function matches($other): bool {
        $actual   = mb_strtolower((string) $other->headers->get('Content-Type'));
        $expected = mb_strtolower($this->getContentType());

        return $actual === $expected;
    }

    public function toString(): string {
        return "Content-Type is {$this->getContentType()}";
    }
}
