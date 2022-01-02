<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;

use function preg_replace;
use function rtrim;
use function str_replace;
use function trim;

/**
 * @internal
 */
class Description extends StringBlock {
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        string $string,
    ) {
        parent::__construct($settings, $level, $used, $string, true);
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeDescription();
    }

    protected function getString(): string {
        $string = parent::getString();

        if ($this->isNormalized()) {
            $eol    = $this->eol();
            $string = str_replace(["\r\n", "\n\r", "\n", "\r"], $eol, $string);
            $string = rtrim(trim($string, $eol));
            $string = preg_replace('/\R{2,}/u', "{$eol}{$eol}", $string);
            $string = preg_replace('/^(.*?)\h+$/mu', '$1', $string);
        }

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
