<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Values\StringValue;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function preg_replace;
use function rtrim;
use function str_replace;
use function trim;

/**
 * @internal
 */
class DescriptionBlock extends StringValue {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        ?string $string,
    ) {
        parent::__construct($context, $level, $used, (string) $string);
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeDescription();
    }

    protected function isBlock(): bool {
        return true;
    }

    protected function getString(): string {
        // Normalize
        $string = parent::getString();

        if ($this->isNormalized()) {
            $eol    = $this->eol();
            $string = str_replace(["\r\n", "\n\r", "\n", "\r"], $eol, $string);
            $string = rtrim(trim($string, $eol));
            $string = (string) preg_replace('/\R{2,}/u', "{$eol}{$eol}", $string);
            $string = (string) preg_replace('/^(.*?)\h+$/mu', '$1', $string);
        }

        // Return
        return $string;
    }

    protected function content(): string {
        $content = parent::content();

        if ($content === '""""""') {
            $content = '';
        }

        return $content;
    }
}
