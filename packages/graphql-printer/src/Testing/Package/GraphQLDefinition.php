<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Testing\Package;

use Attribute;

/**
 * @internal
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class GraphQLDefinition extends GraphQLMarker {
    // empty
}
