<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use DateTime;
use IntlDateFormatter;
use LastDragon_ru\LaraASP\Formatter\Config\Config;
use LastDragon_ru\LaraASP\Formatter\Config\Format;
use LastDragon_ru\LaraASP\Formatter\Formats\Duration\DurationFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\Duration\DurationOptions;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlDateTime\IntlDateTimeFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlDateTime\IntlDateTimeOptions;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlDurationFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlNumberFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlNumberOptions;
use LastDragon_ru\LaraASP\Formatter\Formats\Secret\SecretFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\Secret\SecretOptions;
use LastDragon_ru\LaraASP\Formatter\Package\TestCase;
use NumberFormatter;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function mb_rtrim;
use function pow;
use function str_replace;

use const PHP_INT_MAX;

/**
 * @internal
 */
#[CoversClass(Formatter::class)]
final class FormatterTest extends TestCase {
    protected Formatter $formatter;

    // <editor-fold desc="Setup">
    // =========================================================================
    #[Override]
    public function setUp(): void {
        parent::setUp();

        $this->formatter = $this->app()->make(Formatter::class);
    }

    #[Override]
    public function tearDown(): void {
        parent::tearDown();

        unset($this->formatter);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    public function testForLocale(): void {
        $locale    = 'ru_RU';
        $formatter = $this->formatter->forLocale($locale);

        self::assertNotSame($this->formatter, $formatter);
        self::assertNotSame($this->formatter->getLocale(), $formatter->getLocale());
        self::assertSame($formatter, $formatter->forLocale($locale));
        self::assertSame($locale, $formatter->forTimezone('Europe/Moscow')->getLocale());
    }

    public function testForTimezone(): void {
        $timezone  = 'Europe/Moscow';
        $formatter = $this->formatter->forTimezone($timezone);

        self::assertNotSame($this->formatter, $formatter);
        self::assertNotEquals($this->formatter->getTimezone(), $formatter->getTimezone());
        self::assertSame($formatter, $formatter->forTimezone($timezone));
        self::assertSame($timezone, $formatter->forLocale('ru_RU')->getTimezone());
    }

    public function testInteger(): void {
        self::assertSame('1', $this->formatter->integer(1.45));
        self::assertSame('2', $this->formatter->integer(1.5));
        self::assertSame('1,000', $this->formatter->integer(1000));
        self::assertSame('1,001', $this->formatter->integer(1000.99));
        self::assertSame("1\u{00A0}000", $this->formatter->forLocale('ru_RU')->integer(1000));
    }

    public function testDecimal(): void {
        self::assertSame('1,000.00', $this->formatter->decimal(1000));
        self::assertSame('1,000.99', $this->formatter->decimal(1000.99));
        self::assertSame("1\u{00A0}000,99", $this->formatter->forLocale('ru_RU')->decimal(1000.99));
    }

    public function testDecimalConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::Decimal] = new Format(
                IntlNumberFormat::class,
                new IntlNumberOptions(
                    style     : NumberFormatter::DECIMAL,
                    attributes: [
                        NumberFormatter::FRACTION_DIGITS => 4,
                    ],
                ),
                [
                    'ru_RU' => new IntlNumberOptions(
                        attributes: [
                            NumberFormatter::FRACTION_DIGITS => 2,
                            NumberFormatter::ROUNDING_MODE   => NumberFormatter::ROUND_FLOOR,
                        ],
                    ),
                ],
            );
        });

        self::assertSame('1,000.0000', $this->formatter->decimal(1000));
        self::assertSame('1,000.0001', $this->formatter->decimal(1000.000099));
        self::assertSame("1\u{00A0}000,00", $this->formatter->forLocale('ru_RU')->decimal(1000.0099));
    }

    public function testOrdinal(): void {
        self::assertSame('1st', $this->formatter->ordinal(1));
        self::assertSame('10', mb_rtrim($this->formatter->forLocale('ru_RU')->ordinal(10), '.'));
    }

    public function testString(): void {
        self::assertSame('string', $this->formatter->string('   string   '));
    }

    public function testSpellout(): void {
        self::assertSame(
            'ninety-nine',
            $this->formatter->spellout(99),
        );
        self::assertSame(
            'one thousand three hundred twenty-four point two five',
            $this->formatter->spellout(1324.25),
        );
        self::assertSame(
            'девяносто пять',
            $this->formatter->forLocale('ru_RU')->spellout(95),
        );
        self::assertSame(
            'двадцать пять целых пять десятых',
            $this->formatter->forLocale('ru_RU')->spellout(25.5),
        );
    }

    public function testPercent(): void {
        self::assertSame('10%', $this->formatter->percent(10));
        self::assertSame('25%', $this->formatter->percent(24.59));
        self::assertSame("56\u{00A0}%", $this->formatter->forLocale('ru_RU')->percent(56.09));
    }

    public function testPercentConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::Percent] = new Format(
                IntlNumberFormat::class,
                new IntlNumberOptions(
                    style     : NumberFormatter::PERCENT,
                    attributes: [
                        NumberFormatter::FRACTION_DIGITS => 2,
                    ],
                ),
            );
        });

        self::assertSame('10.99%', $this->formatter->percent(10.99));
    }

    public function testDuration(): void {
        self::assertSame('03:25:45.120', $this->formatter->duration(12_345.12));
        self::assertSame('03:25:45.001', $this->formatter->forLocale('ru_RU')->duration(12_345.0005));
    }

    public function testDurationConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::Duration] = new Format(
                IntlDurationFormat::class,
            );
        });

        self::assertSame('3:25:45', $this->formatter->duration(12_345));
        self::assertSame("12\u{00A0}345", $this->formatter->forLocale('ru_RU')->duration(12_345));
    }

    public function testDurationCustomFormat(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::Duration] = new Format(
                DurationFormat::class,
                new DurationOptions(
                    'mm:ss',
                ),
            );
        });

        self::assertSame('02:03', $this->formatter->duration(123.456));
    }

    public function testTime(): void {
        $time = DateTime::createFromFormat('H:i:s', '23:24:59');

        self::assertIsObject($time);
        self::assertSame('11:24 PM', str_replace("\u{202F}", ' ', $this->formatter->time($time)));
    }

    public function testTimeConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::Time] = new Format(
                IntlDateTimeFormat::class,
                new IntlDateTimeOptions(
                    dateType: IntlDateFormatter::NONE,
                    timeType: IntlDateFormatter::MEDIUM,
                ),
            );
        });

        $time = DateTime::createFromFormat('H:i:s', '23:24:59');

        self::assertIsObject($time);
        self::assertSame('11:24:59 PM', str_replace("\u{202F}", ' ', $this->formatter->time($time)));
    }

    public function testTimeCustomFormat(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::Time] = new Format(
                IntlDateTimeFormat::class,
                new IntlDateTimeOptions(
                    dateType: IntlDateFormatter::NONE,
                    timeType: IntlDateFormatter::SHORT,
                    pattern : 'HH:mm:ss.SSS',
                ),
            );
        });

        $time = DateTime::createFromFormat('H:i:s', '23:24:59');

        self::assertIsObject($time);
        self::assertSame('23:24:59.000', $this->formatter->time($time));
    }

    public function testDate(): void {
        $date = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00');

        self::assertIsObject($date);
        self::assertSame('5/12/05', $this->formatter->date($date));
    }

    public function testDateConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::Date] = new Format(
                IntlDateTimeFormat::class,
                new IntlDateTimeOptions(
                    dateType: IntlDateFormatter::MEDIUM,
                    timeType: IntlDateFormatter::NONE,
                ),
            );
        });

        $date = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00');

        self::assertIsObject($date);
        self::assertSame('May 12, 2005', $this->formatter->date($date));
    }

    public function testDateCustomFormat(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::Date] = new Format(
                IntlDateTimeFormat::class,
                new IntlDateTimeOptions(
                    dateType: IntlDateFormatter::SHORT,
                    timeType: IntlDateFormatter::NONE,
                    pattern : 'd MMM YYYY',
                ),
            );
        });

        $date = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00');

        self::assertIsObject($date);
        self::assertSame('12 May 2005', $this->formatter->date($date));
    }

    public function testDatetime(): void {
        $datetime = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00');

        self::assertIsObject($datetime);
        self::assertSame('5/12/05, 11:00 PM', str_replace("\u{202F}", ' ', $this->formatter->datetime($datetime)));
    }

    public function testDatetimeConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::DateTime] = new Format(
                IntlDateTimeFormat::class,
                new IntlDateTimeOptions(
                    dateType: IntlDateFormatter::MEDIUM,
                    timeType: IntlDateFormatter::MEDIUM,
                ),
            );
        });

        $datetime = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00');

        self::assertIsObject($datetime);
        self::assertSame(
            'May 12, 2005, 11:00:00 PM',
            str_replace("\u{202F}", ' ', $this->formatter->datetime($datetime)),
        );
    }

    public function testDatetimeCustomFormat(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::DateTime] = new Format(
                IntlDateTimeFormat::class,
                new IntlDateTimeOptions(
                    dateType: IntlDateFormatter::MEDIUM,
                    timeType: IntlDateFormatter::MEDIUM,
                    pattern : 'd MMM YYYY || HH:mm:ss',
                ),
            );
        });

        $datetime = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00');

        self::assertIsObject($datetime);
        self::assertSame('12 May 2005 || 23:00:00', $this->formatter->datetime($datetime));
    }

    public function testScientific(): void {
        self::assertSame('1E1', $this->formatter->scientific(10));
        self::assertSame('1.00324234E1', $this->formatter->scientific(10.0324234));
        self::assertSame('1.00324234E8', $this->formatter->scientific(100_324_234));
        self::assertSame('-1,00324234E8', $this->formatter->forLocale('ru_RU')->scientific(-100_324_234));
    }

    public function testSecret(): void {
        self::assertSame('', $this->formatter->secret(null));
        self::assertSame('*', $this->formatter->secret('1'));
        self::assertSame('**', $this->formatter->secret('12'));
        self::assertSame('***', $this->formatter->secret('123'));
        self::assertSame('****', $this->formatter->secret('1234'));
        self::assertSame('*****', $this->formatter->secret('12345'));
        self::assertSame('*****6', $this->formatter->secret('123456'));
        self::assertSame('*****67', $this->formatter->secret('1234567'));
        self::assertSame('*****678', $this->formatter->secret('12345678'));
        self::assertSame('*****6789', $this->formatter->secret('123456789'));
        self::assertSame('*****67890', $this->formatter->secret('1234567890'));
        self::assertSame('******78901', $this->formatter->secret('12345678901'));
    }

    public function testSecretConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::Secret] = new Format(
                class  : SecretFormat::class,
                default: new SecretOptions(3),
                locales: [
                    'ru_RU' => new SecretOptions(2),
                ],
            );
        });

        self::assertSame('', $this->formatter->secret(null));
        self::assertSame('*', $this->formatter->secret('1'));
        self::assertSame('**', $this->formatter->secret('12'));
        self::assertSame('***', $this->formatter->secret('123'));
        self::assertSame('***4', $this->formatter->secret('1234'));
        self::assertSame('***45', $this->formatter->secret('12345'));
        self::assertSame('***456', $this->formatter->secret('123456'));
        self::assertSame('****567', $this->formatter->secret('1234567'));
        self::assertSame('*****678', $this->formatter->secret('12345678'));
        self::assertSame('******78', $this->formatter->forLocale('ru_RU')->secret('12345678'));
    }

    public function testFilesize(): void {
        self::assertSame('0 B', $this->formatter->filesize(null));
        self::assertSame('0 B', $this->formatter->filesize(0));
        self::assertSame('10 B', $this->formatter->filesize(10));
        self::assertSame('1.00 MiB', $this->formatter->filesize(1023 * 1024));
        self::assertSame('10.33 MiB', $this->formatter->filesize(10 * 1024 * 1024 + 1024 * 334));
        self::assertSame('10.00 GiB', $this->formatter->filesize(10 * 1024 * 1024 * 1024));
        self::assertSame('0.87 EiB', $this->formatter->filesize(999_999_999_999_999_999));
        self::assertSame('8.00 EiB', $this->formatter->filesize(PHP_INT_MAX));
        self::assertSame('10.00 QiB', $this->formatter->filesize(10 * pow(2, 100)));
        self::assertSame('10.00 QiB', $this->formatter->filesize('12676506002282294014967032053760'));
        self::assertSame('100.00 QiB', $this->formatter->filesize('126765060022822940149670320537699'));
        self::assertSame(
            '10,000.00 QiB',
            $this->formatter->filesize(10 * 1000 * pow(2, 100)),
        );
    }

    public function testDisksize(): void {
        self::assertSame('0 B', $this->formatter->disksize(null));
        self::assertSame('0 B', $this->formatter->disksize(0));
        self::assertSame('10 B', $this->formatter->disksize(10));
        self::assertSame('1.00 MB', $this->formatter->disksize(999 * 1000));
        self::assertSame('10.83 MB', $this->formatter->disksize(10 * 1024 * 1024 + 1024 * 334));
        self::assertSame('10.00 GB', $this->formatter->disksize(10 * 1000 * 1000 * 1000));
        self::assertSame('9.22 EB', $this->formatter->disksize(PHP_INT_MAX));
        self::assertSame('10.00 QB', $this->formatter->disksize(10_000_000_000_000_000_000_000_000_000_000));
        self::assertSame('10.00 QB', $this->formatter->disksize('10000000000000000000000000000000'));
        self::assertSame('100.00 QB', $this->formatter->disksize('99999999999999999999999999999999'));
        self::assertSame(
            '10,000.00 QB',
            $this->formatter->disksize(10_000_000_000_000_000_000_000_000_000_000_000),
        );
    }

    public function testCurrency(): void {
        $formatter = $this->formatter->forLocale('en_US');

        self::assertSame('$10.00', $formatter->currency(10));
        self::assertSame('€10.00', $formatter->currency(10, 'EUR'));
        self::assertSame('$10.03', $formatter->currency(10.0324234));
    }

    public function testCurrencyConfig(): void {
        $formatter = $this->formatter->forLocale('ru_RU');

        self::assertSame("10,00\u{00A0}₽", $formatter->currency(10));
        self::assertSame("10,03\u{00A0}₽", $formatter->currency(10.0324234));
    }
    // </editor-fold>
}
