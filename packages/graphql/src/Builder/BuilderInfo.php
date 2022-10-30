<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

class BuilderInfo {
    public function __construct(
        protected string $name,
        protected object $builder,
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getBuilder(): object {
        return $this->builder;
    }
}
