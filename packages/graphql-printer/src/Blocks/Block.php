<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use Closure;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Statistics;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use Stringable;

use function array_key_exists;
use function assert;
use function mb_strlen;
use function mb_strpos;
use function str_repeat;

/**
 * @internal
 */
abstract class Block implements Statistics, Stringable {
    private ?string $content   = null;
    private ?int    $length    = null;
    private ?bool   $multiline = null;

    /**
     * @var array<string, string>
     */
    private array $usedTypes = [];

    /**
     * @var array<string, string>
     */
    private array $usedDirectives = [];

    public function __construct(
        private Context $context,
        private int $level = 0,
        private int $used = 0,
    ) {
        // empty
    }

    // <editor-fold desc="Getters/Setters">
    // =========================================================================
    protected function getContext(): Context {
        return $this->context;
    }

    protected function getSettings(): Settings {
        return $this->getContext()->getSettings();
    }

    protected function getLevel(): int {
        return $this->level;
    }

    protected function getUsed(): int {
        return $this->used;
    }
    //</editor-fold>

    // <editor-fold desc="API">
    // =========================================================================
    public function isEmpty(): bool {
        return $this->getLength() <= 0;
    }

    public function getLength(): int {
        return $this->resolve(fn (): int => (int) $this->length);
    }

    public function isMultiline(): bool {
        return $this->resolve(fn (): bool => (bool) $this->multiline);
    }

    public function __toString(): string {
        return $this->getContent();
    }

    /**
     * @template T
     *
     * @param Closure(string $content): T $callback
     *
     * @return T
     */
    protected function resolve(Closure $callback): mixed {
        $content = $this->getContent();
        $result  = $callback($content);

        return $result;
    }
    //</editor-fold>

    // <editor-fold desc="Cache">
    // =========================================================================
    protected function getContent(): string {
        if ($this->content === null) {
            $this->content   = $this->content();
            $this->length    = mb_strlen($this->content);
            $this->multiline = $this->isStringMultiline($this->content);
        }

        return $this->content;
    }

    protected function reset(): void {
        $this->usedDirectives = [];
        $this->usedTypes      = [];
        $this->multiline      = null;
        $this->content        = null;
        $this->length         = null;
    }

    abstract protected function content(): string;
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function eol(): string {
        return $this->getSettings()->getLineEnd();
    }

    protected function space(): string {
        return $this->getSettings()->getSpace();
    }

    protected function indent(int $level = null): string {
        return str_repeat($this->getSettings()->getIndent(), $level ?? $this->getLevel());
    }

    protected function isLineTooLong(int $length): bool {
        return $length > $this->getSettings()->getLineLength();
    }

    protected function isStringMultiline(string $string): bool {
        return mb_strpos($string, "\n") !== false
            || mb_strpos($string, "\r") !== false;
    }
    // </editor-fold>

    // <editor-fold desc="Statistics">
    // =========================================================================
    /**
     * @return array<string,string>
     */
    public function getUsedTypes(): array {
        return $this->resolve(fn (): array => $this->usedTypes);
    }

    /**
     * @return array<string,string>
     */
    public function getUsedDirectives(): array {
        return $this->resolve(fn (): array => $this->usedDirectives);
    }

    /**
     * @template T
     *
     * @param T $block
     *
     * @return T
     */
    protected function addUsed(mixed $block): mixed {
        if ($block instanceof Statistics) {
            $this->usedTypes      += $block->getUsedTypes();
            $this->usedDirectives += $block->getUsedDirectives();
        }

        return $block;
    }

    protected function addUsedType(string $type): static {
        $this->usedTypes[$type] = $type;

        return $this;
    }

    protected function addUsedDirective(string $directive): static {
        $this->usedDirectives[$directive] = $directive;

        return $this;
    }
    // </editor-fold>

    // <editor-fold desc="Types">
    // =========================================================================
    public function isTypeAllowed(string $type): bool {
        // Filter?
        $filter = $this->getSettings()->getTypeFilter();

        if ($filter === null) {
            return true;
        }

        // Allowed?
        $isBuiltIn = $this->isTypeBuiltIn($type);
        $isAllowed = $filter->isAllowedType($type, $isBuiltIn);

        // Return
        return $isAllowed;
    }

    public function isTypeDefinitionAllowed(string $type): bool {
        // Allowed?
        if (!$this->isTypeAllowed($type)) {
            return false;
        }

        // Allowed?
        $filter    = $this->getSettings()->getTypeDefinitionFilter();
        $isBuiltIn = $this->isTypeBuiltIn($type);
        $isAllowed = $isBuiltIn
            ? ($filter !== null && $filter->isAllowedType($type, $isBuiltIn))
            : ($filter === null || $filter->isAllowedType($type, $isBuiltIn));

        // Return
        return $isAllowed;
    }

    /**
     * @param (TypeDefinitionNode&Node)|(TypeNode&Node)|Type $type
     */
    protected function getTypeName(TypeDefinitionNode|TypeNode|Type $type): string {
        $name = null;

        if ($type instanceof WrappingType) {
            $type = $type->getInnermostType();
        }

        if ($type instanceof NamedType) {
            $name = $type->name();
        } elseif ($type instanceof TypeDefinitionNode) {
            $name = $type->getName()->value;
        } elseif ($type instanceof Node) {
            $name = match (true) {
                $type instanceof ListTypeNode,
                $type instanceof NonNullTypeNode
                    => $this->getTypeName($type->type),
                $type instanceof NamedTypeNode
                    => $this->getTypeName($type->name),
                $type instanceof NameNode
                    => $type->value,
                default
                    => null,
            };
        } else {
            // empty
        }

        assert($name !== null);

        return $name;
    }

    protected function isTypeBuiltIn(string $type): bool {
        return array_key_exists($type, Type::builtInTypes());
    }
    // </editor-fold>

    // <editor-fold desc="Directives">
    // =========================================================================
    public function isDirectiveAllowed(string $directive): bool {
        // Filter?
        $filter = $this->getSettings()->getDirectiveFilter();

        if ($filter === null) {
            return true;
        }

        // Allowed?
        $isBuiltIn = $this->isDirectiveBuiltIn($directive);
        $isAllowed = $filter->isAllowedDirective($directive, $isBuiltIn);

        // Return
        return $isAllowed;
    }

    public function isDirectiveDefinitionAllowed(string $directive): bool {
        // Allowed?
        if (!$this->getSettings()->isPrintDirectiveDefinitions() || !$this->isDirectiveAllowed($directive)) {
            return false;
        }

        // Definition?
        $filter    = $this->getSettings()->getDirectiveDefinitionFilter();
        $isBuiltIn = $this->isDirectiveBuiltIn($directive);
        $isAllowed = $isBuiltIn
            ? ($filter !== null && $filter->isAllowedDirective($directive, $isBuiltIn))
            : ($filter === null || $filter->isAllowedDirective($directive, $isBuiltIn));

        // Return
        return $isAllowed;
    }

    private function isDirectiveBuiltIn(string $directive): bool {
        return isset(GraphQLDirective::getInternalDirectives()[$directive]);
    }
    // </editor-fold>
}
