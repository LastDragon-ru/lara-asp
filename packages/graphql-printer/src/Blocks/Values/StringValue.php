<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Values;

use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use Override;

use function json_encode;
use function mb_strlen;
use function preg_match;
use function preg_replace;
use function str_ends_with;
use function str_replace;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
class StringValue extends Block {
    public function __construct(
        Context $context,
        protected string $string,
    ) {
        parent::__construct($context);
    }

    protected function getString(): string {
        return $this->string;
    }

    protected function isBlock(): bool {
        return false;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        // Begin
        $eol     = $this->eol();
        $indent  = $this->indent($level);
        $wrapper = '"""';
        $content = $this->getString();

        // Whitespace only? (it cannot be rendered as BlockString)
        if (preg_match('/^\h+$/u', $content) > 0) {
            return json_encode($content, JSON_THROW_ON_ERROR);
        }

        // Multiline?
        $length      = $used + mb_strlen($indent) + 2 * mb_strlen($wrapper) + mb_strlen($content);
        $isOneliner  = !$this->isStringMultiline($content);
        $isMultiline = $this->isBlock()
            || !$isOneliner
            || $this->isLineTooLong($length)
            || str_ends_with($content, '"')
            || str_ends_with($content, '\\\\');

        if ($isOneliner && (bool) preg_match('/^\h+/u', $content)) {
            $isMultiline = false;
        }

        if ($isMultiline && $content !== '') {
            $content = $eol.preg_replace('/(.+)/mu', "{$indent}\$1", $content).$eol.$indent;
        }

        // Wrap && Escape
        $content = str_replace($wrapper, "\\{$wrapper}", $content);
        $content = "{$wrapper}{$content}{$wrapper}";

        // Return
        return $content;
    }
}
