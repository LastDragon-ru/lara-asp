<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Misc;

use LastDragon_ru\GraphQLPrinter\Contracts\Result;
use Override;

/**
 * @internal
 */
class ResultImpl implements Result {
    public function __construct(
        protected Collector $collector,
        protected string $content,
    ) {
        // empty
    }

    #[Override]
    public function __toString(): string {
        return $this->content;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getUsedTypes(): array {
        return $this->collector->getUsedTypes();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getUsedDirectives(): array {
        return $this->collector->getUsedDirectives();
    }
}
