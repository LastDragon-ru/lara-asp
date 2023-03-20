<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

use function mb_strlen;
use function preg_match;
use function preg_replace;
use function str_ends_with;
use function str_replace;

/**
 * @internal
 */
class StringBlock extends Block {
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        protected string $string,
    ) {
        parent::__construct($settings, $level, $used);
    }

    protected function getString(): string {
        return $this->string;
    }

    protected function isBlock(): bool {
        return false;
    }

    protected function content(): string {
        // Begin
        $eol     = $this->eol();
        $indent  = $this->indent();
        $wrapper = '"""';
        $content = $this->getString();

        // Whitespace only? (it cannot be rendered as BlockString)
        if (preg_match('/^\h+$/u', $content)) {
            return Printer::doPrint(
                new StringValueNode([
                    'value' => $content,
                    'block' => false,
                ]),
            );
        }

        // Multiline?
        $length      = $this->getUsed() + mb_strlen($indent) + 2 * mb_strlen($wrapper) + mb_strlen($content);
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
