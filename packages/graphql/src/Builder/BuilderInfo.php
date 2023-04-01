<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

class BuilderInfo {
    /**
     * @param class-string $builder
     */
    public function __construct(
        protected string $name,
        protected string $builder,
    ) {
        // empty
    }

    public function getName(): string {
        return $this->name;
    }

    /**
     * @return class-string
     */
    public function getBuilder(): string {
        return $this->builder;
    }
}
