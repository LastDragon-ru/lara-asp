<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;
use function mb_strlen;
use function preg_match;
use function rtrim;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function trim;

/**
 * @internal
 */
class Description extends Block {
    public function __construct(
        Settings $settings,
        int $level,
        protected string $description,
    ) {
        parent::__construct($settings, $level);
    }

    protected function isMultiline(): bool {
        // Needed to convert any item with description into multiline.
        return true;
    }

    protected function isNormalized(): bool {
        return $this->settings->isNormalizeDescription();
    }

    protected function serialize(): string {
        // Begin
        $eol         = $this->eol();
        $indent      = $this->indent();
        $wrapper     = '"""';
        $description = $this->description;

        // Standardize line endings
        $description = str_replace(["\r\n", "\n\r", "\n", "\r"], $eol, $description);

        // Normalize?
        if ($this->isNormalized()) {
            $description = rtrim(trim($description, $eol));

            if (!$description) {
                return $description;
            }
        }

        // Whitespace only?
        if (preg_match('/^\p{Zs}+$/u', $description)) {
            return "\"{$description}\"";
        }

        // Multiline? (markdown)
        $length      = mb_strlen($indent) + 2 * mb_strlen($wrapper) + mb_strlen($description);
        $isMultiline = $this->isLineTooLong($length)
            || $this->isStringMultiline($description)
            || str_starts_with($description, ' ')
            || str_starts_with($description, "\t")
            || str_ends_with($description, '"')
            || str_ends_with($description, '\\\\');

        if ($isMultiline) {
            $description = $eol.$indent.str_replace($eol, "{$eol}{$indent}", $description).$eol.$indent;
        }

        // Wrap && Escape
        $description = str_replace($wrapper, "\\{$wrapper}", $description);
        $description = "{$indent}{$wrapper}{$description}{$wrapper}";

        // Return
        return $description;
    }
}
