<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config;

use Exception;
use IntlDateFormatter;
use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\DateTimeFormat;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\DurationFormatPattern;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\FilesizeFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlCurrencyFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlNumberFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlOptions;
use LastDragon_ru\LaraASP\Formatter\Formats\Secret\SecretFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\Secret\SecretOptions;
use LastDragon_ru\LaraASP\Formatter\Formats\String\StringFormat;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use NumberFormatter;
use Override;

class Config extends Configuration {
    public function __construct(
        /**
         * @var array<string, Format<*, mixed>>
         */
        public array $formats = [],
        /**
         * Options and patterns/formats for all locales.
         */
        public Locale $global = new Locale(),
        /**
         * Options and patterns/formats for concrete locale.
         *
         * @var array<non-empty-string, Locale>
         */
        public array $locales = [],
    ) {
        parent::__construct();

        $this->formats[Formatter::String]     = new Format(StringFormat::class);
        $this->formats[Formatter::Secret]     = new Format(SecretFormat::class, new SecretOptions(5));
        $this->formats[Formatter::Integer]    = new Format(
            IntlNumberFormat::class,
            new IntlOptions(
                style     : NumberFormatter::DECIMAL,
                attributes: [
                    NumberFormatter::ROUNDING_MODE   => NumberFormatter::ROUND_HALFUP,
                    NumberFormatter::FRACTION_DIGITS => 0,
                ],
            ),
        );
        $this->formats[Formatter::Decimal]    = new Format(
            IntlNumberFormat::class,
            new IntlOptions(
                style     : NumberFormatter::DECIMAL,
                attributes: [
                    NumberFormatter::ROUNDING_MODE   => NumberFormatter::ROUND_HALFUP,
                    NumberFormatter::FRACTION_DIGITS => 2,
                ],
            ),
        );
        $this->formats[Formatter::Scientific] = new Format(
            IntlNumberFormat::class,
            new IntlOptions(
                style     : NumberFormatter::SCIENTIFIC,
                attributes: [
                    NumberFormatter::ROUNDING_MODE => NumberFormatter::ROUND_HALFUP,
                ],
            ),
        );
        $this->formats[Formatter::Spellout]   = new Format(
            IntlNumberFormat::class,
            new IntlOptions(
                style     : NumberFormatter::SPELLOUT,
                attributes: [
                    NumberFormatter::ROUNDING_MODE => NumberFormatter::ROUND_HALFUP,
                ],
            ),
        );
        $this->formats[Formatter::Ordinal]    = new Format(
            IntlNumberFormat::class,
            new IntlOptions(
                style     : NumberFormatter::ORDINAL,
                attributes: [
                    NumberFormatter::ROUNDING_MODE => NumberFormatter::ROUND_HALFUP,
                ],
            ),
        );
        $this->formats[Formatter::Percent]    = new Format(
            IntlNumberFormat::class,
            new IntlOptions(
                style     : NumberFormatter::PERCENT,
                attributes: [
                    NumberFormatter::ROUNDING_MODE   => NumberFormatter::ROUND_HALFUP,
                    NumberFormatter::FRACTION_DIGITS => 0,
                ],
            ),
        );
        $this->formats[Formatter::Currency]   = new Format(
            IntlCurrencyFormat::class,
            new IntlOptions(
                attributes: [
                    NumberFormatter::ROUNDING_MODE => NumberFormatter::ROUND_HALFUP,
                ],
            ),
        );

        $this->global->datetime->formats += [
            Formatter::Time     => new DateTimeFormat(
                dateType: IntlDateFormatter::NONE,
                timeType: IntlDateFormatter::SHORT,
            ),
            Formatter::Date     => new DateTimeFormat(
                dateType: IntlDateFormatter::SHORT,
                timeType: IntlDateFormatter::NONE,
            ),
            Formatter::DateTime => new DateTimeFormat(
                dateType: IntlDateFormatter::SHORT,
                timeType: IntlDateFormatter::SHORT,
            ),
        ];
        $this->global->duration->formats += [
            Formatter::Default => new DurationFormatPattern('HH:mm:ss.SSS'),
        ];
        $this->global->filesize->formats += [
            Formatter::Disksize => new FileSizeFormat(
                base : 1000,
                units: [
                    ['disksize.B', 'B'],
                    ['disksize.kB', 'kB'],
                    ['disksize.MB', 'MB'],
                    ['disksize.GB', 'GB'],
                    ['disksize.TB', 'TB'],
                    ['disksize.PB', 'PB'],
                    ['disksize.EB', 'EB'],
                    ['disksize.ZB', 'ZB'],
                    ['disksize.YB', 'YB'],
                    ['disksize.RB', 'RB'],
                    ['disksize.QB', 'QB'],
                ],
            ),
            Formatter::Filesize => new FileSizeFormat(
                base : 1024,
                units: [
                    ['filesize.B', 'B'],
                    ['filesize.KiB', 'KiB'],
                    ['filesize.MiB', 'MiB'],
                    ['filesize.GiB', 'GiB'],
                    ['filesize.TiB', 'TiB'],
                    ['filesize.PiB', 'PiB'],
                    ['filesize.EiB', 'EiB'],
                    ['filesize.ZiB', 'ZiB'],
                    ['filesize.YiB', 'YiB'],
                    ['filesize.RiB', 'RiB'],
                    ['filesize.QiB', 'QiB'],
                ],
            ),
        ];
    }

    /**
     * @deprecated %{VERSION} Array-based config is deprecated. Please migrate to object-based config.
     * @inheritDoc
     */
    #[Override]
    public static function fromArray(array $array): static {
        throw new Exception('Array-based config is not supported anymore. Please migrate to object-based config.');
    }
}
