<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_merge;

/**
 * @internal
 */
#[CoversClass(Composite::class)]
final class CompositeTest extends TestCase {
    public function testInvoke(): void {
        $markdown  = $this->app()->make(Markdown::class);
        $document  = $markdown->parse('');
        $aChanges  = [
            [new Location(1, 1, 10), 'a'],
        ];
        $bChanges  = [
            [new Location(2, 3, 0), 'b'],
        ];
        $aMutation = Mockery::mock(Mutation::class);
        $aMutation
            ->shouldReceive('__invoke')
            ->with($document)
            ->once()
            ->andReturn($aChanges);
        $bMutation = Mockery::mock(Mutation::class);
        $bMutation
            ->shouldReceive('__invoke')
            ->with($document)
            ->once()
            ->andReturn($bChanges);

        $mutation = new Composite($aMutation, $bMutation);
        $changes  = $mutation($document);
        $expected = array_merge($aChanges, $bChanges);
        $actual   = [];

        foreach ($changes as $change) {
            $actual[] = $change;
        }

        self::assertEquals($expected, $actual);
    }
}
