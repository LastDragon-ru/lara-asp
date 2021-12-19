<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;
use function mb_strlen;
use function preg_match;
use function str_replace;

/**
 * @internal
 */
class BlockString extends Block {
    public function __construct(
        Settings $settings,
        int $level,
        protected int $reserved,
        protected string $string,
    ) {
        parent::__construct($settings, $level);
    }

    protected function serialize(): string {
        // Begin
        $eol     = $this->eol();
        $indent  = $this->indent();
        $wrapper = '"""';
        $content = $this->string;

        // Whitespace only? (seems it is not possible, so just for the case)
        if (preg_match('/^\p{Zs}+$/u', $content)) {
            return "\"{$content}\"";
        }

        // Multiline? (markdown)
        $length      = $this->reserved + mb_strlen($indent) + 2 * mb_strlen($wrapper) + mb_strlen($content);
        $isMultiline = $this->isLineTooLong($length)
            || $this->isStringMultiline($content)
            || str_ends_with($content, '"')
            || str_ends_with($content, '\\\\');

        if ($isMultiline) {
            $content = $eol.$indent.preg_replace('/(\R)/u', "\$1{$indent}", $content).$eol.$indent;
        }

        // Wrap && Escape
        $content = str_replace($wrapper, "\\{$wrapper}", $content);
        $content = "{$indent}{$wrapper}{$content}{$wrapper}";

        // Return
        return $content;
    }
}
