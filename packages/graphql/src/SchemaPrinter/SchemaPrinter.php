<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedSchema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\SchemaPrinter as SchemaPrinterContract;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;

class SchemaPrinter extends Printer implements SchemaPrinterContract {
    public function print(Schema $schema): PrintedSchema {
        // todo(graphql): directives in description for schema
        //      https://github.com/webonyx/graphql-php/issues/1027

        // Print
        $schema    = clone $schema;
        $settings  = $this->getPrinterSettings($schema->getDirectives());
        $block     = $this->getSchemaDefinition($settings, $schema);
        $content   = $this->getDefinitionList($settings, true);
        $content[] = $block;

        if ($settings->isPrintUnusedDefinitions()) {
            $content[] = $this->getTypeDefinitions($settings, $schema);
            $content[] = $this->getDirectiveDefinitions($settings, $schema);
        } else {
            foreach ($this->getUsedDefinitions($settings, $schema, $block) as $definition) {
                $content[] = $definition;
            }
        }

        // Return
        return $this->getPrintedSchema($settings, $schema, $content);
    }

    protected function getPrintedSchema(PrinterSettings $settings, Schema $schema, Block $content): PrintedSchema {
        return new SchemaPrinted($settings->getResolver(), $schema, $content);
    }

    protected function getSchemaDefinition(PrinterSettings $settings, Schema $schema): Block {
        return $this->getDefinitionBlock($settings, $schema);
    }

    /**
     * Returns all types defined in the schema.
     *
     * @return BlockList<Block>
     */
    protected function getTypeDefinitions(PrinterSettings $settings, Schema $schema): BlockList {
        $blocks = $this->getDefinitionList($settings);

        foreach ($schema->getTypeMap() as $type) {
            if ($settings->isTypeDefinitionAllowed($type)) {
                $blocks[] = $this->getDefinitionBlock($settings, $type);
            }
        }

        return $blocks;
    }

    /**
     * Returns all directives defined in the schema.
     *
     * @return BlockList<Block>
     */
    protected function getDirectiveDefinitions(PrinterSettings $settings, Schema $schema): BlockList {
        // Included?
        $blocks = $this->getDefinitionList($settings);

        if ($settings->isPrintDirectiveDefinitions()) {
            $directives = $settings->getResolver()->getDefinitions();

            foreach ($directives as $directive) {
                if ($settings->isDirectiveDefinitionAllowed($directive->name)) {
                    $blocks[] = $this->getDefinitionBlock($settings, $directive);
                }
            }
        }

        // Return
        return $blocks;
    }
}
