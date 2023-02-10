<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class GraphQLDefinition {
    /**
     * @param class-string $class
     */
    public function __construct(
        protected string $class,
    ) {
        // empty
    }

    /**
     * @return class-string
     */
    public function getClass(): string {
        return $this->class;
    }
}
