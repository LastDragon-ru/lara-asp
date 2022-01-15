<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast\DirectiveNodeList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use function preg_replace;
use function rtrim;
use function str_replace;
use function trim;

/**
 * @internal
 */
class Description extends StringBlock {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        ?string $string,
        private ?DirectiveNodeList $directives = null,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used, (string) $string);
    }

    protected function getDirectives(): ?DirectiveNodeList {
        return $this->directives;
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

        // Directives
        if ($this->getSettings()->isIncludeDirectivesInDescription()) {
            $directives = (string) $this->getDirectives();

            if ($directives) {
                $eol    = $this->eol();
                $string = "{$string}{$eol}{$eol}{$directives}";
            }
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
