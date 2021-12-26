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
        protected string $description,
    ) {
        parent::__construct($settings, $level, $used);
    }

    protected function isNormalized(): bool {
        return $this->settings->isNormalizeDescription();
    }

    protected function isBlock(): bool {
        return true;
    }

    protected function getString(): string {
        $string = $this->description;

        if ($this->isNormalized()) {
            $eol    = $this->eol();
            $string = str_replace(["\r\n", "\n\r", "\n", "\r"], $eol, $string);
            $string = rtrim(trim($string, $eol));
            $string = preg_replace('/\R{2,}/u', "{$eol}{$eol}", $string);
            $string = preg_replace('/^(.*?)\h+$/mu', '$1', $string);
        }

        return $string;
    }

    protected function serialize(): string {
        $content = parent::serialize();

        if ($content === '""""""') {
            $content = '';
        }

        return $content;
    }
}
