<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_merge;

/**
 * @internal
 */
#[CoversClass(Composite::class)]
final class CompositeTest extends TestCase {
    public function testInvoke(): void {
        $document  = new class('') extends Document {
            #[Override]
            public function getNode(): DocumentNode {
                return parent::getNode();
            }
        };
        $node      = $document->getNode();
        $aChanges  = [
            [new Location(1, 1, 10), 'a'],
        ];
        $bChanges  = [
            [new Location(2, 3, 0), 'b'],
        ];
        $aMutation = Mockery::mock(Mutation::class);
        $aMutation
            ->shouldReceive('__invoke')
            ->with($document, $node)
            ->once()
            ->andReturn($aChanges);
        $bMutation = Mockery::mock(Mutation::class);
        $bMutation
            ->shouldReceive('__invoke')
            ->with($document, $node)
            ->once()
            ->andReturn($bChanges);

        $mutation = new Composite($aMutation, $bMutation);
        $changes  = $mutation($document, $node);
        $expected = array_merge($aChanges, $bChanges);
        $actual   = [];

        foreach ($changes as $change) {
            $actual[] = $change;
        }

        self::assertEquals($expected, $actual);
    }
}
