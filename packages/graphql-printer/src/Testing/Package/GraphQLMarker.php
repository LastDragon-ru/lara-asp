<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Testing\Package;

/**
 * @internal
 */
abstract class GraphQLMarker {
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
