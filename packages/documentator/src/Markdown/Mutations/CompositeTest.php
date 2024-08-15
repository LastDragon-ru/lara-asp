<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;

use function array_merge;

/**
 * @internal
 */
#[CoversClass(Composite::class)]
final class CompositeTest extends TestCase {
    public function testInvoke(): void {
        $document  = new Document('');
        $node      = Cast::to(DocumentNode::class, (new ReflectionProperty($document, 'node'))->getValue($document));
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
        $actual   = $mutation($document, $node);
        $expected = array_merge($aChanges, $bChanges);

        self::assertEquals($expected, $actual);
    }
}
