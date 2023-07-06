<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use Closure;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Statistics;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function is_object;
use function mb_strlen;
use function mb_strpos;
use function str_repeat;

/**
 * @internal
 */
abstract class Block {
    private int         $level      = 0;
    private int         $used       = 0;
    private ?string     $content    = null;
    private ?int        $length     = null;
    private ?bool       $multiline  = null;
    private ?Statistics $statistics = null;

    public function __construct(
        private Context $context,
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
    //</editor-fold>

    // <editor-fold desc="API">
    // =========================================================================
    public function isEmpty(int $level, int $used): bool {
        return $this->getLength($level, $used) <= 0;
    }

    public function getLength(int $level, int $used): int {
        return $this->resolve($level, $used, fn (): int => (int) $this->length);
    }

    public function isMultiline(int $level, int $used): bool {
        return $this->resolve($level, $used, fn (): bool => (bool) $this->multiline);
    }

    public function serialize(Collector $collector, int $level, int $used): string {
        return $this->getContent($collector, $level, $used);
    }

    /**
     * @template T
     *
     * @param Closure(string $content): T $callback
     *
     * @return T
     */
    protected function resolve(int $level, int $used, Closure $callback): mixed {
        $content = $this->getContent(new Collector(), $level, $used);
        $result  = $callback($content);

        return $result;
    }
    //</editor-fold>

    // <editor-fold desc="Cache">
    // =========================================================================
    protected function getContent(Collector $collector, int $level, int $used): string {
        if ($this->content === null || $this->level !== $level || $this->used !== $used) {
            $this->statistics = $collector;
            $this->level      = $level;
            $this->used       = $used;
            $this->content    = $this->content($this->statistics, $level, $used);
            $this->length     = mb_strlen($this->content);
            $this->multiline  = $this->isStringMultiline($this->content);
        } elseif ($this->statistics) {
            $collector->addUsed($this->statistics);
        } else {
            // empty
        }

        return $this->content;
    }

    protected function reset(): void {
        $this->statistics = null;
        $this->multiline  = null;
        $this->content    = null;
        $this->length     = null;
        $this->level      = 0;
        $this->used       = 0;
    }

    abstract protected function content(Collector $collector, int $level, int $used): string;
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function eol(): string {
        return $this->getSettings()->getLineEnd();
    }

    protected function space(): string {
        return $this->getSettings()->getSpace();
    }

    protected function indent(int $level): string {
        return str_repeat($this->getSettings()->getIndent(), $level);
    }

    protected function isLineTooLong(int $length): bool {
        return $length > $this->getSettings()->getLineLength();
    }

    protected function isStringMultiline(string $string): bool {
        return mb_strpos($string, "\n") !== false
            || mb_strpos($string, "\r") !== false;
    }
    // </editor-fold>

    // <editor-fold desc="Types">
    // =========================================================================
    /**
     * @param (TypeDefinitionNode&Node)|(TypeNode&Node)|Type|string|null $type
     */
    public function isTypeAllowed(TypeDefinitionNode|TypeNode|Type|string|null $type): bool {
        return $type === null || $this->getContext()->isTypeAllowed($this->getTypeName($type));
    }

    /**
     * @param (TypeDefinitionNode&Node)|(TypeNode&Node)|Type|string|null $type
     */
    public function isTypeDefinitionAllowed(TypeDefinitionNode|TypeNode|Type|string|null $type): bool {
        return $type === null || $this->getContext()->isTypeDefinitionAllowed($this->getTypeName($type));
    }

    /**
     * @param (TypeDefinitionNode&Node)|(TypeNode&Node)|Type|string $type
     */
    protected function getTypeName(TypeDefinitionNode|TypeNode|Type|string $type): string {
        return is_object($type)
            ? $this->getContext()->getTypeName($type)
            : $type;
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

    // <editor-fold desc="Fields">
    // =================================================================================================================
    /**
     * @param (TypeNode&Node)|Type|null $object
     */
    public function getField(TypeNode|Type|null $object, string $field): InputObjectField|FieldDefinition|null {
        return $object
            ? $this->getContext()->getField($object, $field)
            : null;
    }
    //</editor-fold>
}
