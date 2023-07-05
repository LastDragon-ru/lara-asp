<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 */
class RootOperationTypeDefinition extends Type {
    public function __construct(
        Context $context,
        private string $operation,
        NamedTypeNode|ObjectType $type,
    ) {
        parent::__construct($context, $type);
    }

    public function getOperation(): string {
        return $this->operation;
    }

    protected function content(int $level, int $used): string {
        $content = '';

        if ($this->isTypeAllowed($this->getDefinition())) {
            $content = parent::content($level, $used);
            $content = "{$this->getOperation()}:{$this->space()}{$content}";
        }

        return $content;
    }
}
