<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedSchema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;

/**
 * @internal
 */
class PrintedSchemaImpl extends Printed implements PrintedSchema {
    public function __construct(
        protected DirectiveResolver $resolver,
        protected Schema $schema,
        Block $block,
    ) {
        parent::__construct($block);
    }
}
