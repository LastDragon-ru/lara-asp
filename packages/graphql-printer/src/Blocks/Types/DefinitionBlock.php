<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Ast\DirectiveNodeList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\Description;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

use function mb_strlen;

/**
 * @internal
 *
 * @template TType of Type|FieldDefinition|EnumValueDefinition|FieldArgument|Directive|InputObjectField|Schema
 */
abstract class DefinitionBlock extends Block implements NamedBlock {
    /**
     * @param TType $definition
     */
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        private Type|FieldDefinition|EnumValueDefinition|FieldArgument|Directive|InputObjectField|Schema $definition,
    ) {
        parent::__construct($settings, $level, $used);
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
        $body        = (string) $this->addUsed($this->body($used));
        $fields      = (string) $this->addUsed($this->fields($used + mb_strlen($body)));
        $description = (string) $this->addUsed($this->description());
        $directives  = $this->getSettings()->isPrintDirectives()
            ? (string) $this->addUsed($this->directives())
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

    protected function directives(int $level = null, int $used = null): DirectiveNodeList {
        $definition = $this->getDefinition();
        $directives = new DirectiveNodeList(
            $this->getSettings(),
            $level ?? $this->getLevel(),
            $used ?? $this->getUsed(),
            $this->getDefinitionDirectives(),
            $definition->deprecationReason ?? null,
        );

        return $directives;
    }

    protected function description(): ?Description {
        // Description
        $definition  = $this->getDefinition();
        $description = null;

        if ($definition instanceof Schema) {
            // It is part of October2021 spec but not yet supported
            // https://github.com/webonyx/graphql-php/issues/1027
        } else {
            $description = $definition->description;
        }

        // Return
        return new Description(
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $description,
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
