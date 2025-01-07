<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\MetadataStorage;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClass;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Mockery\PropertiesMock;
use LastDragon_ru\LaraASP\Testing\Mockery\WithProperties;
use Mockery;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ClassMethodLink::class)]
final class ClassMethodLinkTest extends TestCase {
    public function testToString(): void {
        self::assertSame('Class::method()', (string) new ClassMethodLink('Class', 'method'));
        self::assertSame('App\\Class::method()', (string) new ClassMethodLink('App\\Class', 'method'));
        self::assertSame('\\App\\Class::method()', (string) new ClassMethodLink('\\App\\Class', 'method'));
    }

    public function testGetTitle(): void {
        self::assertSame('Class::method()', (new ClassMethodLink('Class', 'method'))->getTitle());
        self::assertSame('Class::method()', (new ClassMethodLink('App\\Class', 'method'))->getTitle());
        self::assertSame('Class::method()', (new ClassMethodLink('\\App\\Class', 'method'))->getTitle());
    }

    public function testGetTargetNode(): void {
        $storage = $this->app()->make(MetadataStorage::class);
        $file    = Mockery::mock(File::class, new WithProperties(), PropertiesMock::class);
        $file->makePartial();
        $file
            ->shouldUseProperty('metadata')
            ->value($storage);
        $file
            ->shouldReceive('getMetadata')
            ->with(Content::class)
            ->once()
            ->andReturn(
                <<<'PHP'
                <?php declare(strict_types = 1);

                class A {
                    protected function method(): void {
                        // empty
                    }
                }
                PHP,
            );

        $link = new class ('A', 'method') extends ClassMethodLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $class = $file->getMetadata(PhpClass::class);

        self::assertNotNull($class);

        $actual = $link->getTargetNode($class->class);

        self::assertInstanceOf(ClassMethod::class, $actual);
        self::assertSame('method', $actual->name->name);
    }
}
