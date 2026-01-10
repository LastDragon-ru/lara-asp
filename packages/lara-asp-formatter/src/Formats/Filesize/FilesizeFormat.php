<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\Filesize;

use Illuminate\Support\Str;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Formatter\Contracts\Format;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Formatter\PackageTranslator;
use Override;

use function bccomp;
use function bcdiv;
use function is_float;
use function mb_strlen;
use function sprintf;

/**
 * @implements Format<FilesizeOptions, numeric-string|float|int|null>
 */
readonly class FilesizeFormat implements Format {
    protected int $base;
    /**
     * @var non-empty-list<non-empty-list<string>>
     */
    protected array $units;

    /**
     * @param list<FilesizeOptions|null> $options
     */
    public function __construct(
        protected PackageTranslator $translator,
        protected Formatter $formatter,
        array $options = [],
    ) {
        // Collect options
        $base  = null;
        $units = null;

        foreach ($options as $option) {
            if ($option === null) {
                continue;
            }

            $base  ??= $option->base;
            $units ??= $option->units;
        }

        // Possible?
        if ($base === null) {
            throw new InvalidArgumentException('The `$base` in unknown.');
        }

        if ($units === null) {
            throw new InvalidArgumentException('The `$units` in unknown.');
        }

        // Save
        $this->base  = $base;
        $this->units = $units;
    }

    #[Override]
    public function __invoke(mixed $value): string {
        $unit  = 0;
        $base  = (string) $this->base;
        $scale = mb_strlen($base) + 1;
        $value = match (true) {
            is_float($value) => sprintf('%0.0f', $value),
            $value === null  => '0',
            default          => (string) $value,
        };
        $length = static function (string $bytes): int {
            return mb_strlen(Str::before($bytes, '.'));
        };

        while ((bccomp($value, $base, $scale) >= 0 || $length($value) > 2) && isset($this->units[$unit + 1])) {
            $value = bcdiv($value, $base, $scale);
            $unit++;
        }

        // Format
        $isInt     = $unit === 0;
        $value     = $isInt ? (int) $value : (float) $value;
        $format    = $isInt ? Formatter::Integer : Formatter::Decimal;
        $suffix    = $this->translator->get($this->units[$unit], [], $this->formatter->getLocale());
        $formatted = "{$this->formatter->format($format, $value)} {$suffix}";

        return $formatted;
    }
}
