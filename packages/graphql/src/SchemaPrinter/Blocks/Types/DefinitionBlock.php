<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast\DirectiveNodeList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Named;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use function mb_strlen;

/**
 * @internal
 *
 * @template TType of Type|FieldDefinition|EnumValueDefinition|FieldArgument
 */
abstract class DefinitionBlock extends Block implements Named {
    /**
     * @param TType $definition
     */
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        private Type|FieldDefinition|EnumValueDefinition|FieldArgument $definition,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);
    }

    public function getName(): string {
        return $this->getDefinition()->name;
    }

    protected function isBlock(): bool {
        return $this->getDefinition() instanceof Type;
    }

    /**
     * @return TType
     */
    protected function getDefinition(): Type|FieldDefinition|EnumValueDefinition|FieldArgument {
        return $this->definition;
    }

    protected function content(): string {
        $type        = $this->getDefinition();
        $directives  = new DirectiveNodeList(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $type->astNode->directives ?? null,
            $type->deprecationReason ?? null,
        );
        $description = new Description(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $type->description,
            $directives,
        );

        $eol     = $this->eol();
        $indent  = $this->indent();
        $content = '';

        if ($this->isBlock()) {
            $content .= $indent;
        }

        $body = $this->body($this->getUsed() + mb_strlen($content));

        if ($description->getLength()) {
            $body = "{$description}{$eol}{$indent}{$body}";
        }

        if ($directives->getLength() && $this->getSettings()->isIncludeDirectives()) {
            $body = "{$body}{$eol}{$directives}";
        }

        return "{$content}{$body}";
    }

    abstract protected function body(int $used): string;
}
