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
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;

/**
 * @internal
 */
#[CoversClass(ClassPropertyLink::class)]
final class ClassPropertyLinkTest extends TestCase {
    public function testToString(): void {
        self::assertEquals('Class::$property', (string) new ClassPropertyLink('Class', 'property'));
        self::assertEquals('App\\Class::$property', (string) new ClassPropertyLink('App\\Class', 'property'));
        self::assertEquals(
            '\\App\\Class::$property',
            (string) new ClassPropertyLink('\\App\\Class', 'property'),
        );
    }

    public function testGetTitle(): void {
        self::assertEquals('Class::$property', (new ClassPropertyLink('Class', 'property'))->getTitle());
        self::assertEquals('Class::$property', (new ClassPropertyLink('App\\Class', 'property'))->getTitle());
        self::assertEquals(
            'Class::$property',
            (new ClassPropertyLink('\\App\\Class', 'property'))->getTitle(),
        );
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
                    protected int $property = 123;
                }
                PHP,
            );

        $link = new class ('A', 'property') extends ClassPropertyLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $class = $file->getMetadata(PhpClass::class);

        self::assertNotNull($class);

        $actual = $link->getTargetNode($class->class);

        self::assertInstanceOf(Property::class, $actual);
        self::assertEquals(
            ['property'],
            array_map(
                static fn ($p) => $p->name->name,
                $actual->props,
            ),
        );
    }

    public function testGetTargetNodePromoted(): void {
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
                    public function __construct(
                        protected int $property = 123,
                    ) {
                        // empty
                    }
                }
                PHP,
            );

        $link = new class ('A', 'property') extends ClassPropertyLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $class = $file->getMetadata(PhpClass::class);

        self::assertNotNull($class);

        $actual = $link->getTargetNode($class->class);

        self::assertInstanceOf(Param::class, $actual);
        self::assertInstanceOf(Variable::class, $actual->var);
        self::assertEquals('property', $actual->var->name);
    }
}
