<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Extraction;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_merge;

/**
 * @internal
 */
#[CoversClass(CompositeExtraction::class)]
final class CompositeExtractionTest extends TestCase {
    public function testInvoke(): void {
        $markdown    = $this->app()->make(Markdown::class);
        $document    = $markdown->parse('');
        $aChanges    = [
            [new Location(1, 1, 10), 'a'],
        ];
        $bChanges    = [
            [new Location(2, 3, 0), 'b'],
        ];
        $aExtraction = Mockery::mock(Extraction::class);
        $aExtraction
            ->shouldReceive('__invoke')
            ->with($document)
            ->once()
            ->andReturn($aChanges);
        $bExtraction = Mockery::mock(Extraction::class);
        $bExtraction
            ->shouldReceive('__invoke')
            ->with($document)
            ->once()
            ->andReturn($bChanges);

        $extraction = new CompositeExtraction($aExtraction, $bExtraction);
        $locations  = $extraction($document);
        $expected   = array_merge($aChanges, $bChanges);
        $actual     = [];

        foreach ($locations as $location) {
            $actual[] = $location;
        }

        self::assertEquals($expected, $actual);
    }
}
