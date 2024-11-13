<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Config;

use Exception;
use IntlDateFormatter;
use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\DateTimeFormat;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\DurationFormatPattern;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\FilesizeFormat;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\NumberFormat;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\SecretFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\String\StringFormat;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use NumberFormatter;
use Override;

class Config extends Configuration {
    public function __construct(
        /**
         * @var array<string, Format<Configuration, mixed>|Format<null, mixed>>
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

        $this->formats[Formatter::String] = new Format(StringFormat::class);

        $this->global->number->attributes += [
            NumberFormatter::ROUNDING_MODE => NumberFormatter::ROUND_HALFUP,
        ];
        $this->global->number->formats    += [
            Formatter::Integer    => new NumberFormat(
                style     : NumberFormatter::DECIMAL,
                attributes: [
                    NumberFormatter::FRACTION_DIGITS => 0,
                ],
            ),
            Formatter::Decimal    => new NumberFormat(
                style     : NumberFormatter::DECIMAL,
                attributes: [
                    NumberFormatter::FRACTION_DIGITS => 2,
                ],
            ),
            Formatter::Scientific => new NumberFormat(
                style: NumberFormatter::SCIENTIFIC,
            ),
            Formatter::Spellout   => new NumberFormat(
                style: NumberFormatter::SPELLOUT,
            ),
            Formatter::Ordinal    => new NumberFormat(
                style: NumberFormatter::ORDINAL,
            ),
            Formatter::Percent    => new NumberFormat(
                style     : NumberFormatter::PERCENT,
                attributes: [
                    NumberFormatter::FRACTION_DIGITS => 0,
                ],
            ),
        ];
        $this->global->datetime->formats  += [
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
        $this->global->duration->formats  += [
            Formatter::Default => new DurationFormatPattern('HH:mm:ss.SSS'),
        ];
        $this->global->secret->formats    += [
            Formatter::Default => new SecretFormat(5),
        ];
        $this->global->filesize->formats  += [
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
