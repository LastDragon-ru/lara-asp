<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Types;

use LastDragon_ru\GraphQLPrinter\Blocks\Values\StringValue;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use Override;

use function mb_rtrim;
use function mb_trim;
use function preg_replace;
use function str_replace;

/**
 * @internal
 */
class DescriptionBlock extends StringValue {
    public function __construct(
        Context $context,
        ?string $string,
    ) {
        parent::__construct($context, (string) $string);
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeDescription();
    }

    #[Override]
    protected function isBlock(): bool {
        return true;
    }

    #[Override]
    protected function getString(): string {
        // Normalize
        $string = parent::getString();

        if ($this->isNormalized()) {
            $eol    = $this->eol();
            $string = str_replace(["\r\n", "\n\r", "\n", "\r"], $eol, $string);
            $string = mb_rtrim(mb_trim($string, $eol));
            $string = (string) preg_replace('/\R{2,}/u', "{$eol}{$eol}", $string);
            $string = (string) preg_replace('/^(.*?)\h+$/mu', '$1', $string);
        }

        // Return
        return $string;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        $content = parent::content($collector, $level, $used);

        if ($content === '""""""') {
            $content = '';
        }

        return $content;
    }
}
