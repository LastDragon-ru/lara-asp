<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use Override;
use Symfony\Component\Console\Formatter\NullOutputFormatterStyle;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

/**
 * @internal
 */
class RawOutputFormatter implements OutputFormatterInterface {
    private NullOutputFormatterStyle $style;

    public function __construct() {
        $this->style = new NullOutputFormatterStyle();
    }

    #[Override]
    public function format(?string $message): ?string {
        return $message;
    }

    #[Override]
    public function getStyle(string $name): OutputFormatterStyleInterface {
        return $this->style;
    }

    #[Override]
    public function hasStyle(string $name): bool {
        return false;
    }

    #[Override]
    public function isDecorated(): bool {
        return false;
    }

    #[Override]
    public function setDecorated(bool $decorated): void {
        // empty
    }

    #[Override]
    public function setStyle(string $name, OutputFormatterStyleInterface $style): void {
        // empty
    }
}
