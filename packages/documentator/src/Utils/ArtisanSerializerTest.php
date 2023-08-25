<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Illuminate\Console\Parser;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function reset;

/**
 * @internal
 */
#[CoversClass(ArtisanSerializer::class)]
class ArtisanSerializerTest extends TestCase {
    #[TestWith(['user'])]
    #[TestWith(['user?'])]
    #[TestWith(['user*?'])]
    #[TestWith(['user=default'])]
    #[TestWith(['user=*default'])]
    public function testGetArgumentSignature(string $signature): void {
        $parsed   = Parser::parse("command {{$signature}}")[1] ?? [];
        $argument = reset($parsed);

        self::assertInstanceOf(InputArgument::class, $argument);
        self::assertEquals($signature, (new ArtisanSerializer())->getArgumentSignature($argument));
    }

    #[TestWith(['--user'])]
    #[TestWith(['--u|user'])]
    #[TestWith(['--user=*'])]
    #[TestWith(['--u|user=*'])]
    #[TestWith(['--u|user=default'])]
    #[TestWith(['--u|user=*default'])]
    public function testGetOptionSignature(string $signature): void {
        $parsed = Parser::parse("command {{$signature}}")[2] ?? [];
        $option = reset($parsed);

        self::assertInstanceOf(InputOption::class, $option);
        self::assertEquals($signature, (new ArtisanSerializer())->getOptionSignature($option));
    }
}
