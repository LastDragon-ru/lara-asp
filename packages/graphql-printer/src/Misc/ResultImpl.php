<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Misc;

use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Result;

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

    public function __toString(): string {
        return $this->content;
    }

    /**
     * @inheritDoc
     */
    public function getUsedTypes(): array {
        return $this->collector->getUsedTypes();
    }

    /**
     * @inheritDoc
     */
    public function getUsedDirectives(): array {
        return $this->collector->getUsedDirectives();
    }
}
