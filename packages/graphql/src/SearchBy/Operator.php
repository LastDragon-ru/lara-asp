<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

interface Operator {
    public function getName(): string;

    public function getDefinition(string $type, bool $nullable): string;
}
