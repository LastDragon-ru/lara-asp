<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Illuminate\Console\Parser;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function reset;

/**
 * @internal
 */
#[CoversClass(ArtisanSerializer::class)]
final class ArtisanSerializerTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderGetArgumentSignature')]
    public function testGetArgumentSignature(string $signature): void {
        $parsed = Parser::parse("command {{$signature}}")[1] ?? [];

        self::assertIsArray($parsed);

        $argument = reset($parsed);

        self::assertInstanceOf(InputArgument::class, $argument);
        self::assertSame($signature, (new ArtisanSerializer())->getArgumentSignature($argument));
    }

    #[DataProvider('dataProviderGetOptionSignature')]
    public function testGetOptionSignature(string $signature): void {
        $parsed = Parser::parse("command {{$signature}}")[2] ?? [];

        self::assertIsArray($parsed);

        $option = reset($parsed);

        self::assertInstanceOf(InputOption::class, $option);
        self::assertSame($signature, (new ArtisanSerializer())->getOptionSignature($option));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<int, array{string}>
     */
    public static function dataProviderGetArgumentSignature(): array {
        return [
            ['user'],
            ['user?'],
            ['user*?'],
            ['user=default'],
            ['user=*default'],
        ];
    }

    /**
     * @return array<int, array{string}>
     */
    public static function dataProviderGetOptionSignature(): array {
        return [
            ['--user'],
            ['--u|user'],
            ['--user=*'],
            ['--u|user=*'],
            ['--u|user=default'],
            ['--u|user=*default'],
        ];
    }
    // </editor-fold>
}
