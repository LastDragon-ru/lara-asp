<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Misc;

use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Statistics;

/**
 * @internal
 */
class Collector implements Statistics {
    /**
     * @var array<string, string>
     */
    private array $usedTypes = [];

    /**
     * @var array<string, string>
     */
    private array $usedDirectives = [];

    public function __construct() {
        // empty
    }

    /**
     * @return array<string,string>
     */
    public function getUsedTypes(): array {
        return $this->usedTypes;
    }

    /**
     * @return array<string,string>
     */
    public function getUsedDirectives(): array {
        return $this->usedDirectives;
    }

    /**
     * @template T
     *
     * @param T $block
     *
     * @return T
     */
    public function addUsed(mixed $block): mixed {
        if ($block instanceof Statistics) {
            $this->usedTypes      += $block->getUsedTypes();
            $this->usedDirectives += $block->getUsedDirectives();
        }

        return $block;
    }

    public function addUsedType(string $type): static {
        $this->usedTypes[$type] = $type;

        return $this;
    }

    public function addUsedDirective(string $directive): static {
        $this->usedDirectives[$directive] = $directive;

        return $this;
    }
}
