<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use function explode;
use function implode;
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
     * @param \Psr\Http\Message\ResponseInterface $other
     *
     * @return bool
     */
    protected function matches($other): bool {
        $actual   = mb_strtolower(explode(';', $other->getHeaderLine('Content-Type'))[0]);
        $expected = mb_strtolower($this->getContentType());

        return $actual === $expected;
    }

    public function toString(): string {
        return "has Content-Type is equal to {$this->getContentType()}";
    }
}
