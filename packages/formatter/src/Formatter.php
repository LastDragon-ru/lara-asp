<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Illuminate\Support\Traits\Macroable;
use IntlTimeZone;
use LastDragon_ru\LaraASP\Core\Application\ApplicationResolver;
use LastDragon_ru\LaraASP\Core\Application\ConfigResolver;
use LastDragon_ru\LaraASP\Formatter\Contracts\Format;
use LastDragon_ru\LaraASP\Formatter\Exceptions\FormatterFailedToFormatValue;
use OutOfBoundsException;
use Stringable;

use function sprintf;

class Formatter {
    use Macroable;

    public const String     = 'string';
    public const Integer    = 'integer';
    public const Scientific = 'scientific';
    public const Spellout   = 'spellout';
    public const Ordinal    = 'ordinal';
    public const Decimal    = 'decimal';
    public const Percent    = 'percent';
    public const Time       = 'time';
    public const Date       = 'date';
    public const DateTime   = 'datetime';
    public const Filesize   = 'filesize';
    public const Disksize   = 'disksize';
    public const Secret     = 'secret';
    public const Currency   = 'currency';
    public const Duration   = 'duration';

    private ?string                               $locale   = null;
    private IntlTimeZone|DateTimeZone|string|null $timezone = null;

    public function __construct(
        protected readonly ApplicationResolver $application,
        protected readonly ConfigResolver $config,
        protected readonly PackageConfig $package,
    ) {
        // empty
    }

    // <editor-fold desc="Factory">
    // =========================================================================
    /**
     * Create a new formatter for the specified locale.
     */
    public function forLocale(?string $locale): static {
        $formatter = $this;

        if ($this->locale !== $locale) {
            $formatter         = clone $this;
            $formatter->locale = $locale;
        }

        return $formatter;
    }

    /**
     * Create a new formatter for the specified timezone.
     */
    public function forTimezone(IntlTimeZone|DateTimeZone|string|null $timezone): static {
        $formatter = $this;

        if ($this->timezone !== $timezone) {
            $formatter           = clone $this;
            $formatter->timezone = $timezone;
        }

        return $formatter;
    }
    // </editor-fold>

    // <editor-fold desc="Getters & Setters">
    // =========================================================================
    public function getLocale(): string {
        return $this->locale ?? $this->getDefaultLocale();
    }

    public function getTimezone(): IntlTimeZone|DateTimeZone|string|null {
        return $this->timezone ?? $this->getDefaultTimezone();
    }
    // </editor-fold>

    // <editor-fold desc="Format">
    // =========================================================================
    public function format(string $format, mixed $value): string {
        try {
            return ($this->getFormat($format))($value);
        } catch (Exception $exception) {
            throw new FormatterFailedToFormatValue($format, $value, $exception);
        }
    }

    /**
     * @return Format<*, mixed>
     */
    protected function getFormat(string $format): Format {
        // Known?
        $config   = $this->package->getInstance();
        $settings = $config->formats[$format] ?? null;

        if ($settings === null) {
            throw new OutOfBoundsException(sprintf('The `%s` format is unknown.', $format));
        }

        // Create
        $locale    = $this->getLocale();
        $formatter = $this->application->getInstance()->make($settings->class, [
            'formatter' => $this,
            'options'   => [
                $settings->locales[$locale] ?? null,
                $settings->default,
            ],
        ]);

        return $formatter;
    }
    // </editor-fold>

    // <editor-fold desc="Formats">
    // =========================================================================
    public function string(Stringable|string|null $value): string {
        return $this->format(self::String, $value);
    }

    public function integer(float|int|null $value): string {
        return $this->format(self::Integer, $value);
    }

    public function decimal(float|int|null $value): string {
        return $this->format(self::Decimal, $value);
    }

    public function currency(float|int|null $value): string {
        return $this->format(self::Currency, $value);
    }

    /**
     * @param float|int|null $value must be between 0-100
     */
    public function percent(float|int|null $value): string {
        return $this->format(self::Percent, $value !== null ? $value / 100 : $value);
    }

    public function scientific(float|int|null $value): string {
        return $this->format(self::Scientific, $value);
    }

    public function spellout(float|int|null $value): string {
        return $this->format(self::Spellout, $value);
    }

    public function ordinal(?int $value): string {
        return $this->format(self::Ordinal, $value);
    }

    public function duration(DateInterval|float|int|null $value): string {
        return $this->format(self::Duration, $value);
    }

    public function time(?DateTimeInterface $value): string {
        return $this->format(self::Time, $value);
    }

    public function date(?DateTimeInterface $value): string {
        return $this->format(self::Date, $value);
    }

    public function datetime(?DateTimeInterface $value): string {
        return $this->format(self::DateTime, $value);
    }

    /**
     * Formats number of bytes into units based on powers of 2 (kibibyte, mebibyte, etc).
     *
     * @param numeric-string|float|int|null $bytes
     */
    public function filesize(string|float|int|null $bytes): string {
        return $this->format(self::Filesize, $bytes);
    }

    /**
     * Formats number of bytes into units based on powers of 10 (kilobyte, megabyte, etc).
     *
     * @param numeric-string|float|int|null $bytes
     */
    public function disksize(string|float|int|null $bytes): string {
        return $this->format(self::Disksize, $bytes);
    }

    public function secret(?string $value): string {
        return $this->format(self::Secret, $value);
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function getDefaultLocale(): string {
        return $this->application->getInstance()->getLocale();
    }

    protected function getDefaultTimezone(): IntlTimeZone|DateTimeZone|string|null {
        return $this->config->getInstance()->get('app.timezone') ?? null;
    }
    // </editor-fold>
}
