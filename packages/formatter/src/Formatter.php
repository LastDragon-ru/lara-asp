<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use Closure;
use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use IntlDateFormatter;
use IntlTimeZone;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Formatter\Exceptions\FailedToCreateDateFormatter;
use LastDragon_ru\LaraASP\Formatter\Exceptions\FailedToCreateNumberFormatter;
use LastDragon_ru\LaraASP\Formatter\Exceptions\FailedToFormatValue;
use LastDragon_ru\LaraASP\Formatter\Utils\DurationFormatter;
use Locale;
use NumberFormatter;
use OutOfBoundsException;

use function abs;
use function bccomp;
use function bcdiv;
use function config;
use function is_float;
use function is_int;
use function is_null;
use function is_string;
use function json_encode;
use function mb_str_pad;
use function mb_strlen;
use function mb_substr;
use function sprintf;
use function str_replace;
use function trim;

use const JSON_THROW_ON_ERROR;

class Formatter {
    use Macroable;

    /**
     * @see NumberFormatter::setSymbol()
     */
    final public const IntlSymbols = 'intl_symbols';

    /**
     * @see NumberFormatter::setAttribute()
     */
    final public const IntlAttributes = 'intl_attributes';

    /**
     * @see NumberFormatter::setTextAttribute()
     */
    final public const IntlTextAttributes = 'intl_text_attributes';

    /**
     * Options:
     * - none
     *
     * Locales options:
     * - {@link Formatter::IntlTextAttributes}
     * - {@link Formatter::IntlAttributes} (except {@link NumberFormatter::FRACTION_DIGITS})
     * - {@link Formatter::IntlSymbols}
     */
    public const Integer = 'integer';

    /**
     * Options:
     * - none
     *
     * Locales options:
     * - {@link Formatter::IntlTextAttributes}
     * - {@link Formatter::IntlAttributes}
     * - {@link Formatter::IntlSymbols}
     */
    public const Scientific = 'scientific';

    /**
     * Options:
     * - none
     *
     * Locales options:
     * - {@link Formatter::IntlTextAttributes}
     * - {@link Formatter::IntlAttributes}
     * - {@link Formatter::IntlSymbols}
     */
    public const Spellout = 'spellout';

    /**
     * Options:
     * - none
     *
     * Locales options:
     * - {@link Formatter::IntlTextAttributes}
     * - {@link Formatter::IntlAttributes}
     * - {@link Formatter::IntlSymbols}
     */
    public const Ordinal = 'ordinal';

    /**
     * Options (one of):
     *  - `int`: {@link NumberFormatter::DURATION}
     *  - `string`: the name of custom format
     *
     * Locales options:
     * - {@link Formatter::IntlTextAttributes}
     * - {@link Formatter::IntlAttributes}
     * - {@link Formatter::IntlSymbols}
     * - `string`: available only for custom formats: locale specific pattern
     *       (key: `locales.<locale>.duration.<format>` and/or default pattern
     *       (key: `all.duration.<format>`)
     *       {@link DurationFormatter}
     */
    public const Duration = 'duration';

    /**
     * Options:
     * - `int`: fraction digits
     *
     * Locales options:
     * - {@link Formatter::IntlTextAttributes}
     * - {@link Formatter::IntlAttributes} (except {@link NumberFormatter::FRACTION_DIGITS})
     * - {@link Formatter::IntlSymbols}
     */
    public const Decimal = 'decimal';

    /**
     * Options:
     * - `string`: default currency
     *
     * Locales options:
     * - {@link Formatter::IntlTextAttributes}
     * - {@link Formatter::IntlAttributes} (except {@link NumberFormatter::FRACTION_DIGITS})
     * - {@link Formatter::IntlSymbols}
     */
    public const Currency = 'currency';

    /**
     * Options:
     * - `int`: fraction digits
     *
     * Locales options:
     * - {@link Formatter::IntlTextAttributes}
     * - {@link Formatter::IntlAttributes} (except {@link NumberFormatter::FRACTION_DIGITS})
     * - {@link Formatter::IntlSymbols}
     */
    public const Percent = 'percent';

    /**
     * Options (one of):
     * - `int`: {@link \IntlDateFormatter::SHORT}, {@link \IntlDateFormatter::FULL},
     *      {@link \IntlDateFormatter::LONG} or {@link \IntlDateFormatter::MEDIUM}
     * - `string`: the name of custom format
     *
     * Locales options:
     * - `string`: available only for custom formats: locale specific pattern
     *      (key: `locales.<locale>.time.<format>` and/or default pattern
     *      (key: `all.time.<format>`)
     */
    public const Time = 'time';

    /**
     * @see Formatter::Time
     */
    public const Date = 'date';

    /**
     * @see Formatter::Time
     */
    public const DateTime = 'datetime';

    /**
     * Options:
     * - `int`: fraction digits
     *
     * Locales options:
     * - none
     */
    public const Filesize = 'filesize';

    /**
     * Options:
     * - `int`: fraction digits
     *
     * Locales options:
     * - none
     */
    public const Disksize = 'disksize';

    /**
     * Options:
     * - `int`: how many characters should be shown
     *
     * Locales options:
     * - none
     */
    public const Secret = 'secret';

    private ?string                               $locale   = null;
    private IntlTimeZone|DateTimeZone|string|null $timezone = null;

    /**
     * @var array<string, IntlDateFormatter>
     */
    private array $dateFormatters = [];

    /**
     * @var array<string, NumberFormatter>
     */
    private array $numbersFormatters = [];

    public function __construct(
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
        return $this->locale ?: $this->getDefaultLocale();
    }

    public function getTimezone(): IntlTimeZone|DateTimeZone|string|null {
        return $this->timezone ?: $this->getDefaultTimezone();
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

    public function integer(int|float|null $value): string {
        return $this->formatValue(static::Integer, $value);
    }

    public function decimal(float|int|null $value, int $decimals = null): string {
        return $this->formatValue(static::Decimal, $value, $decimals, function () use ($decimals): int {
            return $decimals ?: Cast::toInt($this->getOptions(static::Decimal, 2));
        });
    }

    public function currency(?float $value, string $currency = null): string {
        $type      = static::Currency;
        $value     = (float) $value;
        $currency  = $currency ?: Cast::toString($this->getOptions($type, 'USD'));
        $formatter = $this->getIntlNumberFormatter($type);
        $formatted = $formatter->formatCurrency($value, $currency);

        if ($formatted === false) {
            throw new FailedToFormatValue($type, $formatter->getErrorCode(), $formatter->getErrorMessage());
        }

        return $formatted;
    }

    /**
     * @param float|null $value must be between 0-100
     */
    public function percent(?float $value, int $decimals = null): string {
        return $this->formatValue(static::Percent, (float) $value / 100, $decimals, function () use ($decimals): int {
            return $decimals ?: Cast::toInt($this->getOptions(static::Percent, 0));
        });
    }

    public function scientific(?float $value): string {
        return $this->formatValue(static::Scientific, $value);
    }

    public function spellout(?float $value): string {
        return $this->formatValue(static::Spellout, $value);
    }

    public function ordinal(?int $value): string {
        return $this->formatValue(static::Ordinal, $value);
    }

    public function duration(DateInterval|float|int|null $value, string $format = null): string {
        $type     = static::Duration;
        $format   = $format ?: $this->getOptions($type);
        $format   = is_string($format)
            ? Cast::toString($this->getLocaleOptions($type, $format))
            : $format;
        $format ??= 'HH:mm:ss.SSS';
        $value  ??= 0;
        $value    = $value instanceof DateInterval
            ? DurationFormatter::getTimestamp($value)
            : $value;
        $value    = is_string($format)
            ? (new DurationFormatter($format))->format($value)
            : $this->formatValue($type, $value);

        return $value;
    }

    public function time(
        ?DateTimeInterface $value,
        string|int $format = null,
        IntlTimeZone|DateTimeZone|string $timezone = null,
    ): string {
        return $this->formatDateTime(self::Time, $value, $format, $timezone);
    }

    public function date(
        ?DateTimeInterface $value,
        string|int $format = null,
        IntlTimeZone|DateTimeZone|string $timezone = null,
    ): string {
        return $this->formatDateTime(self::Date, $value, $format, $timezone);
    }

    public function datetime(
        ?DateTimeInterface $value,
        string|int $format = null,
        IntlTimeZone|DateTimeZone|string $timezone = null,
    ): string {
        return $this->formatDateTime(self::DateTime, $value, $format, $timezone);
    }

    /**
     * Formats number of bytes into units based on powers of 2 (kibibyte, mebibyte, etc).
     *
     * @param numeric-string|float|int<0, max>|null $bytes
     */
    public function filesize(string|float|int|null $bytes, int $decimals = null): string {
        return $this->formatFilesize(
            $bytes,
            $decimals ?: Cast::toInt($this->getOptions(static::Filesize, 2)),
            1024,
            [
                $this->getTranslation(['filesize.B', 'B']),
                $this->getTranslation(['filesize.KiB', 'KiB']),
                $this->getTranslation(['filesize.MiB', 'MiB']),
                $this->getTranslation(['filesize.GiB', 'GiB']),
                $this->getTranslation(['filesize.TiB', 'TiB']),
                $this->getTranslation(['filesize.PiB', 'PiB']),
                $this->getTranslation(['filesize.EiB', 'EiB']),
                $this->getTranslation(['filesize.ZiB', 'ZiB']),
                $this->getTranslation(['filesize.YiB', 'YiB']),
                $this->getTranslation(['filesize.RiB', 'RiB']),
                $this->getTranslation(['filesize.QiB', 'QiB']),
            ],
        );
    }

    /**
     * Formats number of bytes into units based on powers of 10 (kilobyte, megabyte, etc).
     *
     * @param numeric-string|float|int<0, max>|null $bytes
     */
    public function disksize(string|float|int|null $bytes, int $decimals = null): string {
        return $this->formatFilesize(
            $bytes,
            $decimals ?: Cast::toInt($this->getOptions(static::Disksize, 2)),
            1000,
            [
                $this->getTranslation(['disksize.B', 'B']),
                $this->getTranslation(['disksize.kB', 'kB']),
                $this->getTranslation(['disksize.MB', 'MB']),
                $this->getTranslation(['disksize.GB', 'GB']),
                $this->getTranslation(['disksize.TB', 'TB']),
                $this->getTranslation(['disksize.PB', 'PB']),
                $this->getTranslation(['disksize.EB', 'EB']),
                $this->getTranslation(['disksize.ZB', 'ZB']),
                $this->getTranslation(['disksize.YB', 'YB']),
                $this->getTranslation(['disksize.RB', 'RB']),
                $this->getTranslation(['disksize.QB', 'QB']),
            ],
        );
    }

    public function secret(?string $value, int $show = null): string {
        if (is_null($value)) {
            return '';
        }

        $show   = $show ?: Cast::toInt($this->getOptions(static::Secret, 5));
        $length = mb_strlen($value);
        $hidden = $length - $show;

        if ($length <= $show) {
            $value = mb_str_pad('*', $length, '*');
        } elseif ($hidden < $show) {
            $value = str_replace(
                mb_substr($value, 0, $show),
                mb_str_pad('*', $show, '*'),
                $value,
            );
        } else {
            $value = str_replace(
                mb_substr($value, 0, $hidden),
                mb_str_pad('*', $hidden, '*'),
                $value,
            );
        }

        return $value;
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function getDefaultLocale(): string {
        return Container::getInstance()->make(Application::class)->getLocale()
            ?: Locale::getDefault();
    }

    protected function getDefaultTimezone(): IntlTimeZone|DateTimeZone|string|null {
        return config('app.timezone') ?: null;
    }

    protected function getOptions(string $type, mixed $default = null): mixed {
        $package = Package::Name;
        $key     = "{$package}.options.{$type}";

        return config($key, $default);
    }

    protected function getLocaleOptions(string $type, string $option): mixed {
        $package = Package::Name;
        $locale  = $this->getLocale();
        $pattern = config("{$package}.locales.{$locale}.{$type}.{$option}")
            ?: config("{$package}.all.{$type}.{$option}");

        return $pattern;
    }

    /**
     * @param list<string>|string  $key
     * @param array<string, mixed> $replace
     */
    protected function getTranslation(array|string $key, array $replace = []): string {
        return $this->getTranslator()->get($key, $replace, $this->getLocale());
    }

    /**
     * @param Closure(string, ?int):(int|null)|null $closure
     */
    protected function formatValue(
        string $type,
        float|int|null $value,
        int $decimals = null,
        Closure $closure = null,
    ): string {
        $value     = (float) $value;
        $formatter = $this->getIntlNumberFormatter($type, $decimals, $closure);
        $formatted = $formatter->format($value);

        if ($formatted === false) {
            throw new FailedToFormatValue($type, $formatter->getErrorCode(), $formatter->getErrorMessage());
        }

        return $formatted;
    }

    protected function formatDateTime(
        string $type,
        ?DateTimeInterface $value,
        string|int $format = null,
        IntlTimeZone|DateTimeZone|string $timezone = null,
    ): string {
        if (is_null($value)) {
            return '';
        }

        $formatter = ($timezone ? $this->forTimezone($timezone) : $this)->getIntlDateFormatter($type, $format);
        $value     = $formatter->format($value);

        if ($value === false) {
            throw new FailedToFormatValue($type, $formatter->getErrorCode(), $formatter->getErrorMessage());
        }

        return $value;
    }

    /**
     * @param numeric-string|float|int<0, max>|null $bytes
     * @param array<int<0, max>, string>            $units
     */
    protected function formatFilesize(string|float|int|null $bytes, int $decimals, int $base, array $units): string {
        $unit  = 0;
        $base  = (string) $base;
        $scale = 2 * $decimals;
        $bytes = match (true) {
            is_float($bytes) => sprintf('%0.0f', $bytes),
            default => (string) $bytes,
        };
        $length = static function (string $bytes): int {
            return mb_strlen(Str::before($bytes, '.'));
        };

        while ((bccomp($bytes, $base, $scale) >= 0 || $length($bytes) > 2) && isset($units[$unit + 1])) {
            $bytes = bcdiv($bytes, $base, $scale);
            $unit++;
        }

        // Format
        return $unit === 0
            ? $this->integer((int) $bytes)." {$units[$unit]}"
            : $this->decimal((float) $bytes, $decimals)." {$units[$unit]}";
    }
    // </editor-fold>

    // <editor-fold desc="Internal">
    // =========================================================================
    private function getIntlDateFormatter(
        string $type,
        string|int $format = null,
    ): IntlDateFormatter {
        $key       = json_encode([$type, $format], JSON_THROW_ON_ERROR);
        $formatter = $this->dateFormatters[$key] ?? $this->createIntlDateFormatter($type, $format);

        if ($formatter) {
            $this->dateFormatters[$key] = $formatter;
        } else {
            throw new FailedToCreateDateFormatter($type, $format);
        }

        return $formatter;
    }

    private function createIntlDateFormatter(
        string $type,
        string|int $format = null,
    ): ?IntlDateFormatter {
        $formatter = null;
        $pattern   = '';
        $default   = IntlDateFormatter::SHORT;
        $format    = $format ?: $this->getOptions($type, $default);
        $tz        = $this->getTimezone();

        if (is_string($format)) {
            $pattern = Cast::toString($this->getLocaleOptions($type, $format));
            $format  = IntlDateFormatter::FULL;
        }

        if (!is_int($format)) {
            $format = $default;
        }

        switch ($type) {
            case self::Time:
                $formatter = new IntlDateFormatter(
                    $this->getLocale(),
                    IntlDateFormatter::NONE,
                    $format,
                    $tz,
                    null,
                    $pattern,
                );
                break;
            case self::Date:
                $formatter = new IntlDateFormatter(
                    $this->getLocale(),
                    $format,
                    IntlDateFormatter::NONE,
                    $tz,
                    null,
                    $pattern,
                );
                break;
            case self::DateTime:
                $formatter = new IntlDateFormatter(
                    $this->getLocale(),
                    $format,
                    $format,
                    $tz,
                    null,
                    $pattern,
                );
                break;
            default:
                // empty
                break;
        }

        return $formatter;
    }

    /**
     * @param Closure(string, ?int):(int|null)|null $closure
     */
    private function getIntlNumberFormatter(
        string $type,
        int $decimals = null,
        Closure $closure = null,
    ): NumberFormatter {
        $key       = json_encode([$type, $decimals], JSON_THROW_ON_ERROR);
        $formatter = $this->numbersFormatters[$key] ?? $this->createIntlNumberFormatter($type, $decimals, $closure);

        if ($formatter) {
            $this->numbersFormatters[$key] = $formatter;
        } else {
            throw new FailedToCreateNumberFormatter($type);
        }

        return $formatter;
    }

    /**
     * @param Closure(string, ?int):(int|null)|null $closure
     */
    private function createIntlNumberFormatter(
        string $type,
        int $decimals = null,
        Closure $closure = null,
    ): ?NumberFormatter {
        $formatter  = null;
        $attributes = [];
        $symbols    = [];
        $texts      = [];

        if ($closure) {
            $decimals = $closure($type, $decimals);
        }

        switch ($type) {
            case static::Integer:
                $formatter  = new NumberFormatter($this->getLocale(), NumberFormatter::DECIMAL);
                $attributes = $attributes + [
                        NumberFormatter::FRACTION_DIGITS => 0,
                    ];
                break;
            case static::Decimal:
                $formatter  = new NumberFormatter($this->getLocale(), NumberFormatter::DECIMAL);
                $attributes = $attributes + [
                        NumberFormatter::FRACTION_DIGITS => abs((int) $decimals),
                    ];
                break;
            case static::Currency:
                $formatter = new NumberFormatter($this->getLocale(), NumberFormatter::CURRENCY);
                break;
            case static::Percent:
                $formatter  = new NumberFormatter($this->getLocale(), NumberFormatter::PERCENT);
                $attributes = $attributes + [
                        NumberFormatter::FRACTION_DIGITS => abs((int) $decimals),
                    ];
                break;
            case static::Scientific:
                $formatter = new NumberFormatter($this->getLocale(), NumberFormatter::SCIENTIFIC);
                break;
            case static::Spellout:
                $formatter = new NumberFormatter($this->getLocale(), NumberFormatter::SPELLOUT);
                break;
            case static::Ordinal:
                $formatter = new NumberFormatter($this->getLocale(), NumberFormatter::ORDINAL);
                break;
            case static::Duration:
                $formatter = new NumberFormatter($this->getLocale(), NumberFormatter::DURATION);
                break;
            default:
                // null
                break;
        }

        if ($formatter) {
            $attributes = $attributes
                + (array) $this->getLocaleOptions($type, self::IntlAttributes)
                + (array) $this->getOptions(self::IntlAttributes);
            $symbols    = $symbols
                + (array) $this->getLocaleOptions($type, self::IntlSymbols)
                + (array) $this->getOptions(self::IntlSymbols);
            $texts      = $texts
                + (array) $this->getLocaleOptions($type, self::IntlTextAttributes)
                + (array) $this->getOptions(self::IntlTextAttributes);

            foreach ($attributes as $attribute => $value) {
                if (!is_int($attribute) || !$formatter->setAttribute($attribute, Cast::toNumber($value))) {
                    throw new OutOfBoundsException(
                        sprintf(
                            '%s::setAttribute() failed: `%s` is unknown/invalid.',
                            NumberFormatter::class,
                            $attribute,
                        ),
                    );
                }
            }

            foreach ($symbols as $symbol => $value) {
                if (!is_int($symbol) || !$formatter->setSymbol($symbol, Cast::toString($value))) {
                    throw new OutOfBoundsException(
                        sprintf(
                            '%s::setSymbol() failed: `%s` is unknown/invalid.',
                            NumberFormatter::class,
                            $symbol,
                        ),
                    );
                }
            }

            foreach ($texts as $text => $value) {
                if (!is_int($text) || !$formatter->setTextAttribute($text, Cast::toString($value))) {
                    throw new OutOfBoundsException(
                        sprintf(
                            '%s::setTextAttribute() failed: `%s` is unknown/invalid.',
                            NumberFormatter::class,
                            $text,
                        ),
                    );
                }
            }
        }

        return $formatter;
    }

    public function __clone(): void {
        $this->dateFormatters    = [];
        $this->numbersFormatters = [];
    }
    //</editor-fold>
}
