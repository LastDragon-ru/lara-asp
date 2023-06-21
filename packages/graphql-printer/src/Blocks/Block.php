<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use Closure;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Statistics;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use Stringable;

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
    /**
     * @param (TypeDefinitionNode&Node)|(TypeNode&Node)|Type|string|null $type
     */
    public function isTypeAllowed(TypeDefinitionNode|TypeNode|Type|string|null $type): bool {
        return $type === null || $this->getContext()->isTypeAllowed($type);
    }

    /**
     * @param (TypeDefinitionNode&Node)|(TypeNode&Node)|Type|string|null $type
     */
    public function isTypeDefinitionAllowed(TypeDefinitionNode|TypeNode|Type|string|null $type): bool {
        return $type === null || $this->getContext()->isTypeDefinitionAllowed($type);
    }

    /**
     * @param (TypeDefinitionNode&Node)|(TypeNode&Node)|Type $type
     */
    protected function getTypeName(TypeDefinitionNode|TypeNode|Type $type): string {
        return $this->getContext()->getTypeName($type);
    }

    /**
     * @param (TypeNode&Node)|Type|null $object
     */
    public function getFieldType(TypeNode|Type|null $object, string $field): ?Type {
        return $object
            ? $this->getContext()->getFieldType($object, $field)
            : null;
    }
    // </editor-fold>

    // <editor-fold desc="Directives">
    // =========================================================================
    public function isDirectiveAllowed(string $directive): bool {
        return $this->getContext()->isDirectiveAllowed($directive);
    }

    public function isDirectiveDefinitionAllowed(string $directive): bool {
        return $this->getContext()->isDirectiveDefinitionAllowed($directive);
    }
    // </editor-fold>
}
