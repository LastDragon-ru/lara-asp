<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;
use Stringable;
use function mb_strlen;
use function str_repeat;

/**
 * @internal
 */
abstract class Block implements Stringable {
    private ?string $content   = null;
    private ?int    $length    = null;
    private ?bool   $multiline = null;

    protected function __construct(
        protected Settings $settings,
        protected int $level = 0,
    ) {
        // empty
    }

    public function __toString(): string {
        return $this->getContent();
    }

    protected function getContent(): string {
        if ($this->content === null) {
            $this->content   = $this->serialize();
            $this->length    = mb_strlen($this->content);
            $this->multiline = $this->isLineTooLong($this->length)
                || $this->isStringMultiline($this->content);
        }

        return $this->content;
    }

    protected function getLength(): int {
        return $this->length ?? mb_strlen($this->getContent());
    }

    protected function isMultiline(): bool {
        return $this->getContent() && $this->multiline;
    }

    protected function reset(): void {
        $this->multiline = null;
        $this->content   = null;
        $this->length    = null;
    }

    abstract protected function serialize(): string;

    abstract protected function isNormalized(): bool;

    protected function eol(): string {
        return $this->settings->getLineEnd();
    }

    protected function space(): string {
        return $this->settings->getSpace();
    }

    protected function indent(): string {
        return str_repeat($this->settings->getIndent(), $this->level);
    }

    protected function isLineTooLong(int $length): bool {
        return $length > $this->settings->getLineLength();
    }

    protected function isStringMultiline(string $string): bool {
        return mb_strpos($string, "\n") !== false
            || mb_strpos($string, "\r") !== false;
    }
}
