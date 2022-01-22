<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast\DirectiveNodeList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Named;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use function mb_strlen;

/**
 * @internal
 *
 * @template TType of Type|FieldDefinition|EnumValueDefinition|FieldArgument|Directive|InputObjectField|Schema
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
        private Type|FieldDefinition|EnumValueDefinition|FieldArgument|Directive|InputObjectField|Schema $definition,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);
    }

    public function getName(): string {
        $name = $this->name();
        $type = $this->type();

        if ($type && $name) {
            $space = $this->space();
            $name  = "{$type}{$space}{$name}";
        } elseif ($type) {
            $name = $type;
        } else {
            // empty
        }

        return $name;
    }

    /**
     * @return TType
     */
    protected function getDefinition(
        // empty
    ): Type|FieldDefinition|EnumValueDefinition|FieldArgument|Directive|InputObjectField|Schema {
        return $this->definition;
    }

    protected function content(): string {
        $eol         = $this->eol();
        $space       = $this->space();
        $indent      = $this->indent();
        $name        = $this->getName();
        $used        = $this->getUsed() + mb_strlen($name) + mb_strlen($space);
        $body        = (string) $this->body($used);
        $fields      = (string) $this->fields($used + mb_strlen($body));
        $directives  = $this->directives();
        $description = (string) $this->description($directives);
        $directives  = $this->getSettings()->isPrintDirectives()
            ? (string) $directives
            : '';
        $content     = '';

        if ($description) {
            $content .= "{$description}{$eol}{$indent}";
        }

        $content .= "{$name}{$body}";

        if ($directives) {
            $content .= "{$eol}{$indent}{$directives}";
        }

        if ($fields) {
            if ((bool) $directives || $this->isStringMultiline($body)) {
                $content .= "{$eol}{$indent}{$fields}";
            } else {
                $content .= "{$space}{$fields}";
            }
        }

        return $content;
    }

    protected function name(): string {
        $definition = $this->getDefinition();
        $name       = !($definition instanceof Schema)
            ? $definition->name
            : '';

        return $name;
    }

    abstract protected function type(): string|null;

    abstract protected function body(int $used): Block|string|null;

    abstract protected function fields(int $used): Block|string|null;

    protected function directives(): DirectiveNodeList {
        $definition = $this->getDefinition();
        $directives = new DirectiveNodeList(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $this->getDefinitionDirectives(),
            $definition->deprecationReason ?? null,
        );

        return $directives;
    }

    protected function description(DirectiveNodeList $directives): ?Description {
        // Supported?
        $definition  = $this->getDefinition();
        $description = null;

        if ($definition instanceof Schema) {
            // It is part of October2021 spec but not yet supported
            // https://github.com/webonyx/graphql-php/issues/1027
        } else {
            $description = $definition->description;
        }

        return new Description(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $description,
            $directives,
        );
    }

    /**
     * @return NodeList<DirectiveNode>
     */
    protected function getDefinitionDirectives(): NodeList {
        $definition = $this->getDefinition();
        $astNode    = $definition instanceof Schema
            ? $definition->getAstNode()
            : $definition->astNode;
        $directives = $astNode?->directives ?? null;

        if ($directives === null) {
            /** @var NodeList<DirectiveNode> $empty */
            $empty      = new NodeList([]);
            $directives = $empty;
        }

        return $directives;
    }
}
