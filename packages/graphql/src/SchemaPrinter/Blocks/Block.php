<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks;

use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use Stringable;

use function mb_strlen;
use function mb_strpos;
use function str_repeat;

/**
 * @internal
 */
abstract class Block implements Stringable {
    private ?string $content   = null;
    private ?int    $length    = null;
    private ?bool   $multiline = null;

    public function __construct(
        private Dispatcher $dispatcher,
        private Settings $settings,
        private int $level = 0,
        private int $used = 0,
    ) {
        // empty
    }

    // <editor-fold desc="Getters/Setters">
    // =========================================================================
    protected function getDispatcher(): Dispatcher {
        return $this->dispatcher;
    }

    protected function getSettings(): Settings {
        return $this->settings;
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
    public function getLength(): int {
        return $this->length ?? mb_strlen($this->getContent());
    }

    public function isMultiline(): bool {
        return $this->getContent() && $this->multiline;
    }

    public function __toString(): string {
        return $this->getContent();
    }
    //</editor-fold>

    // <editor-fold desc="Cache">
    // =========================================================================
    protected function getContent(): string {
        if ($this->content === null) {
            $this->content   = $this->content();
            $this->length    = mb_strlen($this->content);
            $this->multiline = $this->isLineTooLong($this->length + $this->getUsed())
                || $this->isStringMultiline($this->content);
        }

        return $this->content;
    }

    protected function reset(): void {
        $this->multiline = null;
        $this->content   = null;
        $this->length    = null;
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
}
