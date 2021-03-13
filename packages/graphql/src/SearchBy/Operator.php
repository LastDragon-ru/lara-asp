<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

interface Operator {
    public const PrecedenceStructural = 10;
    public const PrecedenceLogical    = 20;
    public const PrecedenceNormal     = 50;

    public function getName(): string;

    public function getPrecedence(): int;

    /**
     * @param array<string, string> $map
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string;
}
