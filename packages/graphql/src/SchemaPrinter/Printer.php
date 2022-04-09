<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer\DefinitionList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\ExecutableTypeNodeConverter;
use Nuwave\Lighthouse\Schema\TypeRegistry;

abstract class Printer {
    protected Settings $settings;
    protected int      $level = 0;

    public function __construct(
        protected TypeRegistry $registry,
        protected DirectiveLocator $locator,
        protected ExecutableTypeNodeConverter $converter,
        Settings $settings = null,
    ) {
        $this->setSettings($settings);
    }

    public function getLevel(): int {
        return $this->level;
    }

    public function setLevel(int $level): static {
        $this->level = $level;

        return $this;
    }

    public function getSettings(): Settings {
        return $this->settings;
    }

    public function setSettings(?Settings $settings): static {
        $this->settings = $settings ?? new DefaultSettings();

        return $this;
    }

    /**
     * @param array<GraphQLDirective> $directives
     */
    protected function getPrinterSettings(array $directives = []): PrinterSettings {
        $resolver = new DirectiveResolver($this->registry, $this->locator, $this->converter, $directives);
        $settings = new PrinterSettings($resolver, $this->getSettings());

        return $settings;
    }

    protected function getDefinitionBlock(
        PrinterSettings $settings,
        Schema|Type|GraphQLDirective $definition,
    ): Block {
        return new DefinitionBlock($settings, $this->getLevel(), $definition);
    }

    /**
     * @return BlockList<Block>
     */
    protected function getDefinitionList(PrinterSettings $settings, bool $root = false): BlockList {
        return new DefinitionList($settings, $this->getLevel(), $root);
    }
}
