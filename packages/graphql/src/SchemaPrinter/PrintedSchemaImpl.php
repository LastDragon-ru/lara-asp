<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedSchema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;

use function array_diff_key;

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

    /**
     * @inheritDoc
     */
    public function getUnusedDirectives(): array {
        return array_diff_key($this->getDirectives(), $this->getUsedDirectives());
    }

    /**
     * @return array<string, string>
     */
    protected function getDirectives(): array {
        $directives = [];

        foreach ($this->resolver->getDefinitions() as $directive) {
            $directive              = "@{$directive->name}";
            $directives[$directive] = $directive;
        }

        return $directives;
    }
}
