<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use DateTime;
use IntlDateFormatter;
use LastDragon_ru\LaraASP\Formatter\Testing\Package\TestCase;
use NumberFormatter;

use function config;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Formatter\Formatter
 */
class FormatterTest extends TestCase {
    protected Formatter $formatter;

    // <editor-fold desc="Setup">
    // =========================================================================
    public function setUp(): void {
        parent::setUp();

        $this->formatter = $this->app->make(Formatter::class);
    }

    public function tearDown(): void {
        parent::tearDown();

        unset($this->formatter);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::forLocale
     */
    public function testForLocale(): void {
        $locale    = 'ru_RU';
        $formatter = $this->formatter->forLocale($locale);

        $this->assertNotSame($this->formatter, $formatter);
        $this->assertNotEquals($this->formatter->getLocale(), $formatter->getLocale());
    }

    /**
     * @covers ::integer
     */
    public function testInteger(): void {
        $this->assertEquals('1', $this->formatter->integer(1.45));
        $this->assertEquals('2', $this->formatter->integer(1.5));
        $this->assertEquals('1,000', $this->formatter->integer(1000));
        $this->assertEquals('1,001', $this->formatter->integer(1000.99));
        $this->assertEquals("1\u{00A0}000", $this->formatter->forLocale('ru_RU')->integer(1000));
    }

    /**
     * @covers ::decimal
     */
    public function testDecimal(): void {
        $this->assertEquals('1,000.00', $this->formatter->decimal(1000));
        $this->assertEquals('1,000.99', $this->formatter->decimal(1000.99));
        $this->assertEquals("1\u{00A0}000,99", $this->formatter->forLocale('ru_RU')->decimal(1000.99));
    }

    /**
     * @covers ::decimal
     */
    public function testDecimalConfig(): void {
        config([
            Package::Name.'.options.'.Formatter::Decimal                          => 4,
            Package::Name.'.locales.ru_RU.'.Formatter::Decimal.'.intl_attributes' => [
                NumberFormatter::FRACTION_DIGITS => 9, // should be ignored
                NumberFormatter::ROUNDING_MODE   => NumberFormatter::ROUND_FLOOR,
            ],
        ]);

        $this->assertEquals('1,000.0000', $this->formatter->decimal(1000));
        $this->assertEquals('1,000.0001', $this->formatter->decimal(1000.000099));
        $this->assertEquals("1\u{00A0}000,0000", $this->formatter->forLocale('ru_RU')->decimal(1000.000099));
    }

    /**
     * @covers ::ordinal
     */
    public function testOrdinal(): void {
        $this->assertEquals('1st', $this->formatter->ordinal(1));
        $this->assertEquals('10.', $this->formatter->forLocale('ru_RU')->ordinal(10));
    }

    /**
     * @covers ::string
     */
    public function testString(): void {
        $this->assertEquals('string', $this->formatter->string('   string   '));
    }

    /**
     * @covers ::spellout
     */
    public function testSpellout(): void {
        $this->assertEquals(
            'one thousand three hundred twenty-four point two five',
            $this->formatter->spellout(1324.25),
        );
        $this->assertEquals(
            'двадцать пять целых пять десятых',
            $this->formatter->forLocale('ru_RU')->spellout(25.5),
        );
    }

    /**
     * @covers ::percent
     */
    public function testPercent(): void {
        $this->assertEquals('10%', $this->formatter->percent(10));
        $this->assertEquals('25%', $this->formatter->percent(24.59));
        $this->assertEquals("56\u{00A0}%", $this->formatter->forLocale('ru_RU')->percent(56.09));
    }

    /**
     * @covers ::percent
     */
    public function testPercentConfig(): void {
        config([
            Package::Name.'.options.'.Formatter::Percent => 2,
        ]);

        $this->assertEquals('10.99%', $this->formatter->percent(10.99));
    }

    /**
     * @covers ::duration
     */
    public function testDuration(): void {
        $this->assertEquals('3:25:45', $this->formatter->duration(12_345));
        $this->assertEquals("12\u{00A0}345", $this->formatter->forLocale('ru_RU')->duration(12_345));
    }

    /**
     * @covers ::time
     */
    public function testTime(): void {
        $time = DateTime::createFromFormat('H:i:s', '23:24:59') ?: null;

        $this->assertEquals('11:24 PM', $this->formatter->time($time));
        $this->assertEquals('2:24 AM', $this->formatter->time($time, null, 'Europe/Moscow'));
    }

    /**
     * @covers ::time
     */
    public function testTimeConfig(): void {
        config([
            Package::Name.'.options.'.Formatter::Time => IntlDateFormatter::MEDIUM,
        ]);

        $time = DateTime::createFromFormat('H:i:s', '23:24:59') ?: null;

        $this->assertEquals('11:24:59 PM', $this->formatter->time($time));
    }

    /**
     * @covers ::time
     */
    public function testTimeCustomFormat(): void {
        config([
            Package::Name.'.options.'.Formatter::Time        => 'custom',
            Package::Name.'.all.'.Formatter::Time.'.custom'  => 'HH:mm:ss.SSS',
            Package::Name.'.all.'.Formatter::Time.'.custom2' => 'HH:mm:ss.SSS',
        ]);

        $time = DateTime::createFromFormat('H:i:s', '23:24:59') ?: null;

        $this->assertEquals('23:24:59.000', $this->formatter->time($time));
        $this->assertEquals('23:24:59.000', $this->formatter->time($time, 'custom2'));
    }

    /**
     * @covers ::date
     */
    public function testDate(): void {
        $date = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00') ?: null;

        $this->assertEquals('5/12/05', $this->formatter->date($date));
        $this->assertEquals('5/13/05', $this->formatter->date($date, null, 'Europe/Moscow'));
    }

    /**
     * @covers ::date
     */
    public function testDateConfig(): void {
        config([
            Package::Name.'.options.'.Formatter::Date => IntlDateFormatter::MEDIUM,
        ]);

        $date = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00') ?: null;

        $this->assertEquals('May 12, 2005', $this->formatter->date($date));
    }

    /**
     * @covers ::date
     */
    public function testDateCustomFormat(): void {
        config([
            Package::Name.'.options.'.Formatter::Date        => 'custom',
            Package::Name.'.all.'.Formatter::Date.'.custom'  => 'd MMM YYYY',
            Package::Name.'.all.'.Formatter::Date.'.custom2' => 'd MMM YYYY',
        ]);

        $date = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00') ?: null;

        $this->assertEquals('12 May 2005', $this->formatter->date($date));
        $this->assertEquals('12 May 2005', $this->formatter->date($date, 'custom2'));
    }

    /**
     * @covers ::datetime
     */
    public function testDatetime(): void {
        $datetime = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00') ?: null;

        $this->assertEquals('5/12/05, 11:00 PM', $this->formatter->datetime($datetime));
        $this->assertEquals('5/13/05, 3:00 AM', $this->formatter->datetime($datetime, null, 'Europe/Moscow'));
    }

    /**
     * @covers ::datetime
     */
    public function testDatetimeConfig(): void {
        config([
            Package::Name.'.options.'.Formatter::DateTime => IntlDateFormatter::MEDIUM,
        ]);

        $datetime = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00') ?: null;

        $this->assertEquals('May 12, 2005, 11:00:00 PM', $this->formatter->datetime($datetime));
    }

    /**
     * @covers ::datetime
     */
    public function testDatetimeCustomFormat(): void {
        config([
            Package::Name.'.options.'.Formatter::DateTime        => 'custom',
            Package::Name.'.all.'.Formatter::DateTime.'.custom'  => 'd MMM YYYY || HH:mm:ss',
            Package::Name.'.all.'.Formatter::DateTime.'.custom2' => 'd MMM YYYY || HH:mm:ss',
        ]);

        $datetime = DateTime::createFromFormat('d.m.Y H:i:s', '12.05.2005 23:00:00') ?: null;

        $this->assertEquals('12 May 2005 || 23:00:00', $this->formatter->datetime($datetime));
        $this->assertEquals('12 May 2005 || 23:00:00', $this->formatter->datetime($datetime, 'custom2'));
    }

    /**
     * @covers ::scientific
     */
    public function testScientific(): void {
        $this->assertEquals('1.00324234E1', $this->formatter->scientific(10.0324234));
        $this->assertEquals('1.00324234E8', $this->formatter->scientific(100_324_234));
        $this->assertEquals('-1,00324234E8', $this->formatter->forLocale('ru_RU')->scientific(-100_324_234));
    }

    /**
     * @covers ::secret
     */
    public function testSecret(): void {
        $this->assertEquals('', $this->formatter->secret(null));
        $this->assertEquals('*', $this->formatter->secret('1'));
        $this->assertEquals('**', $this->formatter->secret('12'));
        $this->assertEquals('***', $this->formatter->secret('123'));
        $this->assertEquals('****', $this->formatter->secret('1234'));
        $this->assertEquals('*****', $this->formatter->secret('12345'));
        $this->assertEquals('*****6', $this->formatter->secret('123456'));
        $this->assertEquals('*****67', $this->formatter->secret('1234567'));
        $this->assertEquals('*****678', $this->formatter->secret('12345678'));
        $this->assertEquals('*****6789', $this->formatter->secret('123456789'));
        $this->assertEquals('*****67890', $this->formatter->secret('1234567890'));
        $this->assertEquals('******78901', $this->formatter->secret('12345678901'));
    }

    /**
     * @covers ::secret
     */
    public function testSecretConfig(): void {
        config([
            Package::Name.'.options.'.Formatter::Secret => 3,
        ]);

        $this->assertEquals('', $this->formatter->secret(null));
        $this->assertEquals('*', $this->formatter->secret('1'));
        $this->assertEquals('**', $this->formatter->secret('12'));
        $this->assertEquals('***', $this->formatter->secret('123'));
        $this->assertEquals('***4', $this->formatter->secret('1234'));
        $this->assertEquals('***45', $this->formatter->secret('12345'));
        $this->assertEquals('***456', $this->formatter->secret('123456'));
        $this->assertEquals('****567', $this->formatter->secret('1234567'));
        $this->assertEquals('*****678', $this->formatter->secret('12345678'));
    }

    /**
     * @covers ::filesize
     */
    public function testFilesize(): void {
        $this->assertEquals('10 bytes', $this->formatter->filesize(10));
        $this->assertEquals('10.33 MB', $this->formatter->filesize(10 * 1024 * 1024 + 1024 * 334));
    }

    /**
     * @covers ::currency
     */
    public function testCurrency(): void {
        $this->assertEquals('$10.03', $this->formatter->currency(10.0324234));
        $this->assertEquals("RUB\u{00A0}100,324,234.00", $this->formatter->currency(100_324_234, 'RUB'));
        $this->assertEquals("100,99\u{00A0}₽", $this->formatter->forLocale('ru_RU')->currency(100.985, 'RUB'));
    }

    /**
     * @covers ::currency
     */
    public function testCurrencyConfig(): void {
        config([
            Package::Name.'.options.'.Formatter::Currency => 'RUB',
        ]);

        $this->assertEquals("RUB\u{00A0}10.03", $this->formatter->currency(10.0324234));
    }
    // </editor-fold>
}
