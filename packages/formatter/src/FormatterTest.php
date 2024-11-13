<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use DateTime;
use IntlDateFormatter;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Formatter\Config\Config;
use LastDragon_ru\LaraASP\Formatter\Config\Format;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\DateTimeFormat;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\DurationFormatIntl;
use LastDragon_ru\LaraASP\Formatter\Config\Formats\DurationFormatPattern;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlNumberFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlOptions;
use LastDragon_ru\LaraASP\Formatter\Formats\Secret\SecretFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\Secret\SecretOptions;
use LastDragon_ru\LaraASP\Formatter\Testing\Package\TestCase;
use NumberFormatter;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

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
        self::assertNotEquals($this->formatter->getLocale(), $formatter->getLocale());
        self::assertSame($formatter, $formatter->forLocale($locale));
        self::assertEquals($locale, $formatter->forTimezone('Europe/Moscow')->getLocale());
    }

    public function testForTimezone(): void {
        $timezone  = 'Europe/Moscow';
        $formatter = $this->formatter->forTimezone($timezone);

        self::assertNotSame($this->formatter, $formatter);
        self::assertNotEquals($this->formatter->getTimezone(), $formatter->getTimezone());
        self::assertSame($formatter, $formatter->forTimezone($timezone));
        self::assertEquals($timezone, $formatter->forLocale('ru_RU')->getTimezone());
    }

    public function testInteger(): void {
        self::assertEquals('1', $this->formatter->integer(1.45));
        self::assertEquals('2', $this->formatter->integer(1.5));
        self::assertEquals('1,000', $this->formatter->integer(1000));
        self::assertEquals('1,001', $this->formatter->integer(1000.99));
        self::assertEquals("1\u{00A0}000", $this->formatter->forLocale('ru_RU')->integer(1000));
    }

    public function testDecimal(): void {
        self::assertEquals('1,000.00', $this->formatter->decimal(1000));
        self::assertEquals('1,000.99', $this->formatter->decimal(1000.99));
        self::assertEquals("1\u{00A0}000,99", $this->formatter->forLocale('ru_RU')->decimal(1000.99));
    }

    public function testDecimalConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::Decimal] = new Format(
                IntlNumberFormat::class,
                new IntlOptions(
                    style     : NumberFormatter::DECIMAL,
                    attributes: [
                        NumberFormatter::FRACTION_DIGITS => 4,
                    ],
                ),
                [
                    'ru_RU' => new IntlOptions(
                        attributes: [
                            NumberFormatter::FRACTION_DIGITS => 2,
                            NumberFormatter::ROUNDING_MODE   => NumberFormatter::ROUND_FLOOR,
                        ],
                    ),
                ],
            );
        });

        self::assertEquals('1,000.0000', $this->formatter->decimal(1000));
        self::assertEquals('1,000.0001', $this->formatter->decimal(1000.000099));
        self::assertEquals("1\u{00A0}000,00", $this->formatter->forLocale('ru_RU')->decimal(1000.0099));
    }

    public function testOrdinal(): void {
        self::assertEquals('1st', $this->formatter->ordinal(1));
        self::assertEquals('10.', $this->formatter->forLocale('ru_RU')->ordinal(10));
    }

    public function testString(): void {
        self::assertEquals('string', $this->formatter->string('   string   '));
    }

    public function testSpellout(): void {
        self::assertEquals(
            'ninety-nine',
            $this->formatter->spellout(99),
        );
        self::assertEquals(
            'one thousand three hundred twenty-four point two five',
            $this->formatter->spellout(1324.25),
        );
        self::assertEquals(
            'девяносто пять',
            $this->formatter->forLocale('ru_RU')->spellout(95),
        );
        self::assertEquals(
            'двадцать пять целых пять десятых',
            $this->formatter->forLocale('ru_RU')->spellout(25.5),
        );
    }

    public function testPercent(): void {
        self::assertEquals('10%', $this->formatter->percent(10));
        self::assertEquals('25%', $this->formatter->percent(24.59));
        self::assertEquals("56\u{00A0}%", $this->formatter->forLocale('ru_RU')->percent(56.09));
    }

    public function testPercentConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->formats[Formatter::Percent] = new Format(
                IntlNumberFormat::class,
                new IntlOptions(
                    style     : NumberFormatter::PERCENT,
                    attributes: [
                        NumberFormatter::FRACTION_DIGITS => 2,
                    ],
                ),
            );
        });

        self::assertEquals('10.99%', $this->formatter->percent(10.99));
    }

    public function testDuration(): void {
        self::assertEquals('03:25:45.120', $this->formatter->duration(12_345.12));
        self::assertEquals('03:25:45.001', $this->formatter->forLocale('ru_RU')->duration(12_345.0005));
    }

    public function testDurationConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->global->duration->formats[Formatter::Default] = new DurationFormatIntl();
        });

        self::assertEquals('3:25:45', $this->formatter->duration(12_345));
        self::assertEquals("12\u{00A0}345", $this->formatter->forLocale('ru_RU')->duration(12_345));
    }

    public function testDurationCustomFormat(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->global->duration->formats[Formatter::Default] = new DurationFormatPattern('mm:ss');
        });

        self::assertEquals('02:03', $this->formatter->duration(123.456));
    }

    public function testTime(): void {
        $time = DateTime::createFromFormat('H:i:s', '23:24:59');

        self::assertIsObject($time);
        self::assertEquals('11:24 PM', str_replace("\u{202F}", ' ', $this->formatter->time($time)));
    }

    public function testTimeConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $format           = Cast::to(DateTimeFormat::class, $config->global->datetime->formats[Formatter::Time]);
            $format->timeType = IntlDateFormatter::MEDIUM;
        });

        $time = DateTime::createFromFormat('H:i:s', '23:24:59');

        self::assertIsObject($time);
        self::assertEquals('11:24:59 PM', str_replace("\u{202F}", ' ', $this->formatter->time($time)));
    }

    public function testTimeCustomFormat(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $format          = Cast::to(DateTimeFormat::class, $config->global->datetime->formats[Formatter::Time]);
            $format->pattern = 'HH:mm:ss.SSS';
        });

        $time = DateTime::createFromFormat('H:i:s', '23:24:59');

        self::assertIsObject($time);
        self::assertEquals('23:24:59.000', $this->formatter->time($time));
    }

    public function testDate(): void {
        $date = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00');

        self::assertIsObject($date);
        self::assertEquals('5/12/05', $this->formatter->date($date));
    }

    public function testDateConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $format           = Cast::to(DateTimeFormat::class, $config->global->datetime->formats[Formatter::Date]);
            $format->dateType = IntlDateFormatter::MEDIUM;
        });

        $date = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00');

        self::assertIsObject($date);
        self::assertEquals('May 12, 2005', $this->formatter->date($date));
    }

    public function testDateCustomFormat(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $format          = Cast::to(DateTimeFormat::class, $config->global->datetime->formats[Formatter::Date]);
            $format->pattern = 'd MMM YYYY';
        });

        $date = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00');

        self::assertIsObject($date);
        self::assertEquals('12 May 2005', $this->formatter->date($date));
    }

    public function testDatetime(): void {
        $datetime = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00');

        self::assertIsObject($datetime);
        self::assertEquals('5/12/05, 11:00 PM', str_replace("\u{202F}", ' ', $this->formatter->datetime($datetime)));
    }

    public function testDatetimeConfig(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $format           = Cast::to(
                DateTimeFormat::class,
                $config->global->datetime->formats[Formatter::DateTime],
            );
            $format->dateType = IntlDateFormatter::MEDIUM;
            $format->timeType = IntlDateFormatter::MEDIUM;
        });

        $datetime = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00');

        self::assertIsObject($datetime);
        self::assertEquals(
            'May 12, 2005, 11:00:00 PM',
            str_replace("\u{202F}", ' ', $this->formatter->datetime($datetime)),
        );
    }

    public function testDatetimeCustomFormat(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $format          = Cast::to(DateTimeFormat::class, $config->global->datetime->formats[Formatter::DateTime]);
            $format->pattern = 'd MMM YYYY || HH:mm:ss';
        });

        $datetime = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00');

        self::assertIsObject($datetime);
        self::assertEquals('12 May 2005 || 23:00:00', $this->formatter->datetime($datetime));
    }

    public function testScientific(): void {
        self::assertEquals('1E1', $this->formatter->scientific(10));
        self::assertEquals('1.00324234E1', $this->formatter->scientific(10.0324234));
        self::assertEquals('1.00324234E8', $this->formatter->scientific(100_324_234));
        self::assertEquals('-1,00324234E8', $this->formatter->forLocale('ru_RU')->scientific(-100_324_234));
    }

    public function testSecret(): void {
        self::assertEquals('', $this->formatter->secret(null));
        self::assertEquals('*', $this->formatter->secret('1'));
        self::assertEquals('**', $this->formatter->secret('12'));
        self::assertEquals('***', $this->formatter->secret('123'));
        self::assertEquals('****', $this->formatter->secret('1234'));
        self::assertEquals('*****', $this->formatter->secret('12345'));
        self::assertEquals('*****6', $this->formatter->secret('123456'));
        self::assertEquals('*****67', $this->formatter->secret('1234567'));
        self::assertEquals('*****678', $this->formatter->secret('12345678'));
        self::assertEquals('*****6789', $this->formatter->secret('123456789'));
        self::assertEquals('*****67890', $this->formatter->secret('1234567890'));
        self::assertEquals('******78901', $this->formatter->secret('12345678901'));
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

        self::assertEquals('', $this->formatter->secret(null));
        self::assertEquals('*', $this->formatter->secret('1'));
        self::assertEquals('**', $this->formatter->secret('12'));
        self::assertEquals('***', $this->formatter->secret('123'));
        self::assertEquals('***4', $this->formatter->secret('1234'));
        self::assertEquals('***45', $this->formatter->secret('12345'));
        self::assertEquals('***456', $this->formatter->secret('123456'));
        self::assertEquals('****567', $this->formatter->secret('1234567'));
        self::assertEquals('*****678', $this->formatter->secret('12345678'));
        self::assertEquals('******78', $this->formatter->forLocale('ru_RU')->secret('12345678'));
    }

    public function testFilesize(): void {
        self::assertEquals('0 B', $this->formatter->filesize(null));
        self::assertEquals('0 B', $this->formatter->filesize(0));
        self::assertEquals('10 B', $this->formatter->filesize(10));
        self::assertEquals('1.00 MiB', $this->formatter->filesize(1023 * 1024));
        self::assertEquals('10.33 MiB', $this->formatter->filesize(10 * 1024 * 1024 + 1024 * 334));
        self::assertEquals('10.00 GiB', $this->formatter->filesize(10 * 1024 * 1024 * 1024));
        self::assertEquals('0.87 EiB', $this->formatter->filesize(999_999_999_999_999_999));
        self::assertEquals('8.00 EiB', $this->formatter->filesize(PHP_INT_MAX));
        self::assertEquals('10.00 QiB', $this->formatter->filesize(10 * pow(2, 100)));
        self::assertEquals('10.00 QiB', $this->formatter->filesize('12676506002282294014967032053760'));
        self::assertEquals('100.00 QiB', $this->formatter->filesize('126765060022822940149670320537699'));
        self::assertEquals(
            '10,000.00 QiB',
            $this->formatter->filesize(10 * 1000 * pow(2, 100)),
        );
    }

    public function testDisksize(): void {
        self::assertEquals('0 B', $this->formatter->disksize(null));
        self::assertEquals('0 B', $this->formatter->disksize(0));
        self::assertEquals('10 B', $this->formatter->disksize(10));
        self::assertEquals('1.00 MB', $this->formatter->disksize(999 * 1000));
        self::assertEquals('10.83 MB', $this->formatter->disksize(10 * 1024 * 1024 + 1024 * 334));
        self::assertEquals('10.00 GB', $this->formatter->disksize(10 * 1000 * 1000 * 1000));
        self::assertEquals('9.22 EB', $this->formatter->disksize(PHP_INT_MAX));
        self::assertEquals('10.00 QB', $this->formatter->disksize(10_000_000_000_000_000_000_000_000_000_000));
        self::assertEquals('10.00 QB', $this->formatter->disksize('10000000000000000000000000000000'));
        self::assertEquals('100.00 QB', $this->formatter->disksize('99999999999999999999999999999999'));
        self::assertEquals(
            '10,000.00 QB',
            $this->formatter->disksize(10_000_000_000_000_000_000_000_000_000_000_000),
        );
    }

    public function testCurrency(): void {
        $formatter = $this->formatter->forLocale('en_US');

        self::assertEquals('$10.00', $formatter->currency(10));
        self::assertEquals('$10.03', $formatter->currency(10.0324234));
    }

    public function testCurrencyConfig(): void {
        $formatter = $this->formatter->forLocale('ru_RU');

        self::assertEquals("10,00\u{00A0}₽", $formatter->currency(10));
        self::assertEquals("10,03\u{00A0}₽", $formatter->currency(10.0324234));
    }
    // </editor-fold>
}
