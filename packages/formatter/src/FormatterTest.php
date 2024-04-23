<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use DateTime;
use IntlDateFormatter;
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
        $this->setConfig([
            Package::Name.'.options.'.Formatter::Decimal                                     => 4,
            Package::Name.'.locales.ru_RU.'.Formatter::Decimal.'.'.Formatter::IntlAttributes => [
                NumberFormatter::FRACTION_DIGITS => 9, // should be ignored
                NumberFormatter::ROUNDING_MODE   => NumberFormatter::ROUND_FLOOR,
            ],
        ]);

        self::assertEquals('1,000.0000', $this->formatter->decimal(1000));
        self::assertEquals('1,000.0001', $this->formatter->decimal(1000.000099));
        self::assertEquals("1\u{00A0}000,0000", $this->formatter->forLocale('ru_RU')->decimal(1000.000099));
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
            'one thousand three hundred twenty-four point two five',
            $this->formatter->spellout(1324.25),
        );
        self::assertEquals(
            'двадцать пять целых пять десятых',
            $this->formatter->forLocale('ru_RU')->spellout(25.5),
        );
    }

    public function testPercent(): void {
        self::assertEquals('10%', $this->formatter->percent(10));
        self::assertEquals('25%', $this->formatter->percent(24.59));
        self::assertEquals('24.59%', $this->formatter->percent(24.59, 2));
        self::assertEquals("56\u{00A0}%", $this->formatter->forLocale('ru_RU')->percent(56.09));
    }

    public function testPercentConfig(): void {
        $this->setConfig([
            Package::Name.'.options.'.Formatter::Percent => 2,
        ]);

        self::assertEquals('10.99%', $this->formatter->percent(10.99));
    }

    public function testDuration(): void {
        self::assertEquals('03:25:45.120', $this->formatter->duration(12_345.12));
        self::assertEquals('03:25:45.001', $this->formatter->forLocale('ru_RU')->duration(12_345.0005));
    }

    public function testDurationConfig(): void {
        $this->setConfig([
            Package::Name.'.options.'.Formatter::Duration => NumberFormatter::DURATION,
        ]);

        self::assertEquals('3:25:45', $this->formatter->duration(12_345));
        self::assertEquals("12\u{00A0}345", $this->formatter->forLocale('ru_RU')->duration(12_345));
    }

    public function testDurationCustomFormat(): void {
        $this->setConfig([
            Package::Name.'.options.'.Formatter::Duration        => 'custom',
            Package::Name.'.all.'.Formatter::Duration.'.custom'  => 'mm:ss',
            Package::Name.'.all.'.Formatter::Duration.'.custom2' => 'H:mm:ss.SSS',
        ]);

        self::assertEquals('02:03', $this->formatter->duration(123.456));
        self::assertEquals('0:02:03.456', $this->formatter->duration(123.456, 'custom2'));
    }

    public function testTime(): void {
        $time = DateTime::createFromFormat('H:i:s', '23:24:59') ?: null;

        self::assertEquals('11:24 PM', str_replace("\u{202F}", ' ', $this->formatter->time($time)));
        self::assertEquals(
            '2:24 AM',
            str_replace("\u{202F}", ' ', $this->formatter->time($time, null, 'Europe/Moscow')),
        );
    }

    public function testTimeConfig(): void {
        $this->setConfig([
            Package::Name.'.options.'.Formatter::Time => IntlDateFormatter::MEDIUM,
        ]);

        $time = DateTime::createFromFormat('H:i:s', '23:24:59') ?: null;

        self::assertEquals('11:24:59 PM', str_replace("\u{202F}", ' ', $this->formatter->time($time)));
    }

    public function testTimeCustomFormat(): void {
        $this->setConfig([
            Package::Name.'.options.'.Formatter::Time        => 'custom',
            Package::Name.'.all.'.Formatter::Time.'.custom'  => 'HH:mm:ss.SSS',
            Package::Name.'.all.'.Formatter::Time.'.custom2' => 'HH:mm:ss.SSS',
        ]);

        $time = DateTime::createFromFormat('H:i:s', '23:24:59') ?: null;

        self::assertEquals('23:24:59.000', $this->formatter->time($time));
        self::assertEquals('23:24:59.000', $this->formatter->time($time, 'custom2'));
    }

    public function testDate(): void {
        $date = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00') ?: null;

        self::assertEquals('5/12/05', $this->formatter->date($date));
        self::assertEquals('5/13/05', $this->formatter->date($date, null, 'Europe/Moscow'));
    }

    public function testDateConfig(): void {
        $this->setConfig([
            Package::Name.'.options.'.Formatter::Date => IntlDateFormatter::MEDIUM,
        ]);

        $date = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00') ?: null;

        self::assertEquals('May 12, 2005', $this->formatter->date($date));
    }

    public function testDateCustomFormat(): void {
        $this->setConfig([
            Package::Name.'.options.'.Formatter::Date        => 'custom',
            Package::Name.'.all.'.Formatter::Date.'.custom'  => 'd MMM YYYY',
            Package::Name.'.all.'.Formatter::Date.'.custom2' => 'd MMM YYYY',
        ]);

        $date = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00') ?: null;

        self::assertEquals('12 May 2005', $this->formatter->date($date));
        self::assertEquals('12 May 2005', $this->formatter->date($date, 'custom2'));
    }

    public function testDatetime(): void {
        $datetime = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00') ?: null;

        self::assertEquals('5/12/05, 11:00 PM', str_replace("\u{202F}", ' ', $this->formatter->datetime($datetime)));
        self::assertEquals(
            '5/13/05, 3:00 AM',
            str_replace("\u{202F}", ' ', $this->formatter->datetime($datetime, null, 'Europe/Moscow')),
        );
    }

    public function testDatetimeConfig(): void {
        $this->setConfig([
            Package::Name.'.options.'.Formatter::DateTime => IntlDateFormatter::MEDIUM,
        ]);

        $datetime = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00') ?: null;

        self::assertEquals(
            'May 12, 2005, 11:00:00 PM',
            str_replace("\u{202F}", ' ', $this->formatter->datetime($datetime)),
        );
    }

    public function testDatetimeCustomFormat(): void {
        $this->setConfig([
            Package::Name.'.options.'.Formatter::DateTime        => 'custom',
            Package::Name.'.all.'.Formatter::DateTime.'.custom'  => 'd MMM YYYY || HH:mm:ss',
            Package::Name.'.all.'.Formatter::DateTime.'.custom2' => 'd MMM YYYY || HH:mm:ss',
        ]);

        $datetime = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00') ?: null;

        self::assertEquals('12 May 2005 || 23:00:00', $this->formatter->datetime($datetime));
        self::assertEquals('12 May 2005 || 23:00:00', $this->formatter->datetime($datetime, 'custom2'));
    }

    public function testScientific(): void {
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
        $this->setConfig([
            Package::Name.'.options.'.Formatter::Secret => 3,
        ]);

        self::assertEquals('', $this->formatter->secret(null));
        self::assertEquals('*', $this->formatter->secret('1'));
        self::assertEquals('**', $this->formatter->secret('12'));
        self::assertEquals('***', $this->formatter->secret('123'));
        self::assertEquals('***4', $this->formatter->secret('1234'));
        self::assertEquals('***45', $this->formatter->secret('12345'));
        self::assertEquals('***456', $this->formatter->secret('123456'));
        self::assertEquals('****567', $this->formatter->secret('1234567'));
        self::assertEquals('*****678', $this->formatter->secret('12345678'));
    }

    public function testFilesize(): void {
        self::assertEquals('0 B', $this->formatter->filesize(null));
        self::assertEquals('0 B', $this->formatter->filesize(0));
        self::assertEquals('10 B', $this->formatter->filesize(10));
        self::assertEquals('1.00 MiB', $this->formatter->filesize(1023 * 1024, 2));
        self::assertEquals('10.33 MiB', $this->formatter->filesize(10 * 1024 * 1024 + 1024 * 334));
        self::assertEquals('10.00 GiB', $this->formatter->filesize(10 * 1024 * 1024 * 1024, 2));
        self::assertEquals('0.87 EiB', $this->formatter->filesize(999_999_999_999_999_999, 2));
        self::assertEquals('8.00 EiB', $this->formatter->filesize(PHP_INT_MAX, 2));
        self::assertEquals('10.00 QiB', $this->formatter->filesize(10 * pow(2, 100), 2));
        self::assertEquals('10.00 QiB', $this->formatter->filesize('12676506002282294014967032053760', 2));
        self::assertEquals('100.00 QiB', $this->formatter->filesize('126765060022822940149670320537699', 2));
        self::assertEquals(
            '10,000.00 QiB',
            $this->formatter->filesize(10 * 1000 * pow(2, 100), 2),
        );
    }

    public function testDisksize(): void {
        self::assertEquals('0 B', $this->formatter->disksize(null));
        self::assertEquals('0 B', $this->formatter->disksize(0));
        self::assertEquals('10 B', $this->formatter->disksize(10));
        self::assertEquals('1.00 MB', $this->formatter->disksize(999 * 1000, 2));
        self::assertEquals('10.83 MB', $this->formatter->disksize(10 * 1024 * 1024 + 1024 * 334));
        self::assertEquals('10.00 GB', $this->formatter->disksize(10 * 1000 * 1000 * 1000, 2));
        self::assertEquals('9.22 EB', $this->formatter->disksize(PHP_INT_MAX, 2));
        self::assertEquals('10.00 QB', $this->formatter->disksize(10_000_000_000_000_000_000_000_000_000_000, 2));
        self::assertEquals('10.00 QB', $this->formatter->disksize('10000000000000000000000000000000', 2));
        self::assertEquals('100.00 QB', $this->formatter->disksize('99999999999999999999999999999999', 2));
        self::assertEquals(
            '10,000.00 QB',
            $this->formatter->disksize(10_000_000_000_000_000_000_000_000_000_000_000, 2),
        );
    }

    public function testCurrency(): void {
        self::assertEquals('$10.03', $this->formatter->currency(10.0324234));
        self::assertEquals("RUB\u{00A0}100,324,234.00", $this->formatter->currency(100_324_234, 'RUB'));
        self::assertEquals("100,99\u{00A0}₽", $this->formatter->forLocale('ru_RU')->currency(100.985, 'RUB'));
    }

    public function testCurrencyConfig(): void {
        $this->setConfig([
            Package::Name.'.options.'.Formatter::Currency => 'RUB',
        ]);

        self::assertEquals("RUB\u{00A0}10.03", $this->formatter->currency(10.0324234));
    }
    // </editor-fold>
}
