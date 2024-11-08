<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use IntlDateFormatter;
use IntlException;
use IntlTimeZone;
use LastDragon_ru\LaraASP\Core\Application\ApplicationResolver;
use LastDragon_ru\LaraASP\Core\Application\ConfigResolver;
use LastDragon_ru\LaraASP\Formatter\Config\Config;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\DurationFormatIntl;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\DurationFormatPattern;
use LastDragon_ru\LaraASP\Formatter\Exceptions\FormatterFailedToCreateFormatter;
use LastDragon_ru\LaraASP\Formatter\Exceptions\FormatterFailedToFormatValue;
use LastDragon_ru\LaraASP\Formatter\Formatters\Number\Formatter as NumberFormatter;
use LastDragon_ru\LaraASP\Formatter\Formatters\Number\Options;
use LastDragon_ru\LaraASP\Formatter\Utils\DurationFormatter;
use NumberFormatter as IntlNumberFormatter;

use function bccomp;
use function bcdiv;
use function is_float;
use function is_null;
use function mb_str_pad;
use function mb_strlen;
use function mb_substr;
use function sprintf;
use function str_replace;
use function trim;

class Formatter {
    use Macroable;

    public const Default    = 'default';
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

    private const FormatterNumber   = 'Number';
    private const FormatterSecret   = 'Secret';
    private const FormatterDateTime = 'DateTime';
    private const FormatterDuration = 'Duration';
    private const FormatterCurrency = 'Currency';
    private const FormatterFilesize = 'Filesize';

    private ?string                               $locale   = null;
    private IntlTimeZone|DateTimeZone|string|null $timezone = null;

    public function __construct(
        protected readonly ApplicationResolver $application,
        protected readonly ConfigResolver $config,
        protected readonly PackageConfig $configuration,
        private PackageTranslator $translator,
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
            $formatter         = $this->create();
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
            $formatter           = $this->create();
            $formatter->timezone = $timezone;
        }

        return $formatter;
    }

    protected function create(): static {
        return clone $this;
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

    protected function getTranslator(): PackageTranslator {
        return $this->translator;
    }
    // </editor-fold>

    // <editor-fold desc="Formats">
    // =========================================================================
    public function string(?string $value): string {
        return trim((string) $value);
    }

    public function integer(float|int|null $value): string {
        return $this->formatNumber(self::Integer, $value);
    }

    public function decimal(float|int|null $value): string {
        return $this->formatNumber(self::Decimal, $value);
    }

    public function currency(float|int|null $value): string {
        return $this->formatCurrency(self::Default, $value);
    }

    /**
     * @param float|int|null $value must be between 0-100
     */
    public function percent(float|int|null $value): string {
        return $this->formatNumber(self::Percent, $value !== null ? $value / 100 : $value);
    }

    public function scientific(float|int|null $value): string {
        return $this->formatNumber(self::Scientific, $value);
    }

    public function spellout(float|int|null $value): string {
        return $this->formatNumber(self::Spellout, $value);
    }

    public function ordinal(?int $value): string {
        return $this->formatNumber(self::Ordinal, $value);
    }

    public function duration(DateInterval|float|int|null $value): string {
        return $this->formatDuration(self::Default, $value);
    }

    public function time(?DateTimeInterface $value): string {
        return $this->formatDateTime(self::Time, $value);
    }

    public function date(?DateTimeInterface $value): string {
        return $this->formatDateTime(self::Date, $value);
    }

    public function datetime(?DateTimeInterface $value): string {
        return $this->formatDateTime(self::DateTime, $value);
    }

    /**
     * Formats number of bytes into units based on powers of 2 (kibibyte, mebibyte, etc).
     *
     * @param numeric-string|float|int|null $bytes
     */
    public function filesize(string|float|int|null $bytes): string {
        return $this->formatFilesize(self::Filesize, $bytes);
    }

    /**
     * Formats number of bytes into units based on powers of 10 (kilobyte, megabyte, etc).
     *
     * @param numeric-string|float|int|null $bytes
     */
    public function disksize(string|float|int|null $bytes): string {
        return $this->formatFilesize(self::Disksize, $bytes);
    }

    public function secret(?string $value): string {
        return $this->formatSecret(self::Default, $value);
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

    /**
     * @param list<string>|string  $key
     * @param array<string, mixed> $replace
     */
    protected function getTranslation(array|string $key, array $replace = []): string {
        return $this->getTranslator()->get($key, $replace, $this->getLocale());
    }

    protected function formatNumber(string $format, float|int|null $value): string {
        // Create
        try {
            $config    = $this->configuration->getInstance();
            $locale    = $this->getLocale();
            $formatter = new NumberFormatter(
                $locale,
                $config->locales[$locale]->number->formats[$format] ?? null,
                $config->locales[$locale]->number ?? null,
                $config->global->number->formats[$format] ?? null,
                $config->global->number,
            );
            $formatted = $formatter->formatNumber($value ?? 0);
        } catch (Exception $exception) {
            throw new FormatterFailedToFormatValue(self::FormatterNumber, $format, $value, $exception);
        }

        return $formatted;
    }

    /**
     * @param non-empty-string|null $currency
     */
    protected function formatCurrency(string $format, float|int|null $value, ?string $currency = null): string {
        // Create
        try {
            $config    = $this->configuration->getInstance();
            $locale    = $this->getLocale();
            $formatter = new NumberFormatter(
                $locale,
                new Options(IntlNumberFormatter::CURRENCY),
                $config->locales[$locale]->currency->formats[$format] ?? null,
                $config->locales[$locale]->currency ?? null,
                $config->global->currency->formats[$format] ?? null,
                $config->global->currency,
            );
            $formatted = $formatter->formatCurrency($value ?? 0, $currency);
        } catch (Exception $exception) {
            throw new FormatterFailedToFormatValue(
                self::FormatterCurrency,
                "{$format}@".($currency ?? 'NULL'),
                $value,
                $exception,
            );
        }

        // Return
        return $formatted;
    }

    protected function formatDateTime(string $format, ?DateTimeInterface $value): string {
        // Null?
        if (is_null($value)) {
            return '';
        }

        // Prepare
        $config   = $this->configuration->getInstance();
        $locale   = $this->getLocale();
        $pattern  = $config->global->datetime->formats[$format]->pattern
            ?? $config->locales[$locale]->datetime->formats[$format]->pattern
            ?? null;
        $dateType = $config->global->datetime->formats[$format]->dateType
            ?? $config->locales[$locale]->datetime->formats[$format]->dateType
            ?? null;
        $timeType = $config->global->datetime->formats[$format]->timeType
            ?? $config->locales[$locale]->datetime->formats[$format]->timeType
            ?? null;

        if ($dateType === null || $timeType === null) {
            throw new FormatterFailedToCreateFormatter(self::FormatterDateTime, $format);
        }

        // Create
        try {
            $formatter = $this->getDateTimeFormatter($dateType, $timeType, $pattern);
        } catch (Exception $exception) {
            throw new FormatterFailedToCreateFormatter(self::FormatterDateTime, $format, $exception);
        }

        // Format
        $formatted = $formatter->format($value);

        if ($formatted === false) {
            throw new FormatterFailedToFormatValue(
                self::FormatterDateTime,
                $format,
                $value,
                throw new IntlException(
                    $formatter->getErrorMessage(),
                    $formatter->getErrorCode(),
                ),
            );
        }

        // Return
        return $formatted;
    }

    protected function formatSecret(string $format, ?string $value): string {
        // Null?
        if (is_null($value)) {
            return '';
        }

        // Prepare
        $config  = $this->configuration->getInstance();
        $locale  = $this->getLocale();
        $visible = $config->global->secret->formats[$format]->visible
            ?? $config->locales[$locale]->secret->formats[$format]->visible
            ?? null;

        if ($visible === null) {
            throw new FormatterFailedToCreateFormatter(self::FormatterSecret, $format);
        }

        // Format
        $length    = mb_strlen($value);
        $hidden    = $length - $visible;
        $formatted = match (true) {
            $length <= $visible => mb_str_pad('*', $length, '*'),
            $hidden < $visible  => str_replace(mb_substr($value, 0, $visible), mb_str_pad('*', $visible, '*'), $value),
            default             => str_replace(mb_substr($value, 0, $hidden), mb_str_pad('*', $hidden, '*'), $value),
        };

        // Return
        return $formatted;
    }

    protected function formatDuration(string $format, DateInterval|float|int|null $value): string {
        $config    = $this->configuration->getInstance();
        $locale    = $this->getLocale();
        $type      = $config->locales[$locale]->duration->formats[$format]
            ?? $config->global->duration->formats[$format]
            ?? null;
        $value     = ($value instanceof DateInterval ? DurationFormatter::getTimestamp($value) : $value) ?? 0;
        $formatted = match (true) {
            $type instanceof DurationFormatPattern => $this->formatDurationPattern($type, $value),
            $type instanceof DurationFormatIntl    => $this->formatDurationIntl($config, $locale, $format, $value),
            default                                => throw new FormatterFailedToCreateFormatter(
                self::FormatterDuration,
                $format,
            ),
        };

        return $formatted;
    }

    private function formatDurationPattern(DurationFormatPattern $config, float|int $value): string {
        return (new DurationFormatter($config->pattern))->format($value);
    }

    private function formatDurationIntl(Config $config, string $locale, string $format, float|int $value): string {
        try {
            $formatIntl = $config->locales[$locale]->duration->formats[$format] ?? null;
            $globalIntl = $config->global->duration->formats[$format] ?? null;
            $formatter  = new NumberFormatter(
                $locale,
                new Options(IntlNumberFormatter::DURATION),
                $formatIntl instanceof Options ? $formatIntl : null,
                $globalIntl instanceof Options ? $globalIntl : null,
            );
            $formatted  = $formatter->formatNumber($value);
        } catch (Exception $exception) {
            throw new FormatterFailedToFormatValue(self::FormatterDuration, $format, $value, $exception);
        }

        return $formatted;
    }

    /**
     * @param numeric-string|float|int|null $bytes
     */
    protected function formatFilesize(string $format, string|float|int|null $bytes): string {
        // Prepare
        $config        = $this->configuration->getInstance();
        $locale        = $this->getLocale();
        $base          = $config->locales[$locale]->filesize->formats[$format]->base
            ?? $config->global->filesize->formats[$format]->base
            ?? null;
        $units         = $config->locales[$locale]->filesize->formats[$format]->units
            ?? $config->global->filesize->formats[$format]->units
            ?? null;
        $integerFormat = $config->locales[$locale]->filesize->formats[$format]->integerFormat
            ?? $config->global->filesize->formats[$format]->integerFormat
            ?? self::Integer;
        $decimalFormat = $config->locales[$locale]->filesize->formats[$format]->decimalFormat
            ?? $config->global->filesize->formats[$format]->decimalFormat
            ?? self::Decimal;

        if ($base === null || $units === null) {
            throw new FormatterFailedToCreateFormatter(self::FormatterFilesize, $format);
        }

        $unit  = 0;
        $base  = (string) $base;
        $scale = mb_strlen($base) + 1;
        $bytes = match (true) {
            is_float($bytes) => sprintf('%0.0f', $bytes),
            $bytes === null  => '0',
            default          => (string) $bytes,
        };
        $length = static function (string $bytes): int {
            return mb_strlen(Str::before($bytes, '.'));
        };

        while ((bccomp($bytes, $base, $scale) >= 0 || $length($bytes) > 2) && isset($units[$unit + 1])) {
            $bytes = bcdiv($bytes, $base, $scale);
            $unit++;
        }

        // Format
        $isInt     = $unit === 0;
        $bytes     = $isInt ? (int) $bytes : (float) $bytes;
        $format    = $isInt ? $integerFormat : $decimalFormat;
        $suffix    = $this->getTranslation($units[$unit]);
        $formatted = "{$this->formatNumber($format, $bytes)} {$suffix}";

        return $formatted;
    }
    // </editor-fold>

    // <editor-fold desc="Internal">
    // =========================================================================
    private function getDateTimeFormatter(int $dateType, int $timeType, ?string $pattern): IntlDateFormatter {
        $locale    = $this->getLocale();
        $timezone  = $this->getTimezone();
        $formatter = new IntlDateFormatter($locale, $dateType, $timeType, $timezone, null, $pattern);

        return $formatter;
    }
    //</editor-fold>
}
