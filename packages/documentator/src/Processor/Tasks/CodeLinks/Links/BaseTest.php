<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\Parsed;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ParsedClass;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ParsedFile;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File as FileImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\LinkTarget;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDocumentFactory;
use LastDragon_ru\LaraASP\Testing\Mockery\PropertiesMock;
use LastDragon_ru\LaraASP\Testing\Mockery\WithProperties;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Mockery;
use Override;
use PhpParser\NameContext;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Base::class)]
final class BaseTest extends TestCase {
    use WithProcessor;

    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderGetTitle')]
    public function testGetTitle(?string $expected, string $value): void {
        $link = new class($value) extends Base {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getSource(File $file, Package $package): array|FilePath|null {
                return null;
            }

            #[Override]
            public function __toString(): string {
                return $this->class;
            }

            #[Override]
            protected function getTargetNode(ClassLike $class): ?Node {
                return null;
            }
        };

        self::assertSame($expected, $link->getTitle());
    }

    #[DataProvider('dataProviderIsSimilar')]
    public function testIsSimilar(bool $expected, Link $a, Link $b): void {
        self::assertSame($expected, $a->isSimilar($b));
    }

    public function testGetTarget(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $resolver   = $this->getDependencyResolver($filesystem);
        $base       = new DirectoryPath('/path/to/directory');
        $path       = new FilePath('/path/to/directory/file.md');
        $file       = new FileImpl($filesystem, $path);

        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem
            ->shouldReceive('path')
            ->once()
            ->andReturn($base);
        $filesystem
            ->shouldReceive('begin')
            ->once()
            ->passthru();
        $filesystem
            ->shouldReceive('read')
            ->with($file)
            ->once()
            ->andReturn(
                <<<'PHP'
                <?php declare(strict_types = 1);

                /**
                 * @deprecated for testing
                 */
                class A {
                    public const Constant = 123;
                }
                PHP,
            );

        $filesystem->begin($base);

        $link = new class('A') extends Base {
            #[Override]
            protected function getTargetNode(ClassLike $class): Node {
                return $class;
            }

            #[Override]
            public function __toString(): string {
                return '';
            }
        };

        self::assertEquals(
            new LinkTarget((new FilePath('file.md'))->normalized(), true, null, null),
            $link->getTarget($resolver, $file),
        );
    }

    public function testGetTargetClassNotMatch(): void {
        $file  = new FilePath('file.php');
        $class = Mockery::mock(ClassLike::class, new WithProperties(), PropertiesMock::class);
        $class
            ->shouldUseProperty('namespacedName')
            ->value(new Name('App\\A'));
        $source   = Mockery::mock(File::class);
        $classes  = static function (ParsedFile $file) use ($class): array {
            return [
                new ParsedClass(Mockery::mock(PhpDocumentFactory::class), $file, $class),
            ];
        };
        $parsed   = new ParsedFile($file, Mockery::mock(NameContext::class), $classes);
        $resolver = Mockery::mock(Resolver::class);
        $resolver
            ->shouldReceive('cast')
            ->with($source, Parsed::class)
            ->once()
            ->andReturn($parsed);

        $link = Mockery::mock(Base::class, [$this::class]);
        $link->shouldAllowMockingProtectedMethods();
        $link->makePartial();
        $link
            ->shouldReceive('getTargetNode')
            ->never();

        self::assertNull($link->getTarget($resolver, $source));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{?string, string}>
     */
    public static function dataProviderGetTitle(): array {
        return [
            'empty' => [null, ''],
            'UN'    => ['Class', 'Class'],
            'QN'    => ['Class::$property', 'App\\Class::$property'],
            'FQN'   => ['Class::method()', '\\App\\Class::method()'],
        ];
    }

    /**
     * @return array<string, array{bool, Link, Link}>
     */
    public static function dataProviderIsSimilar(): array {
        $link = new BaseTest_BaseLink('\A', 'a');

        return [
            'same'                    => [false, $link, $link],
            'same class'              => [false, new BaseTest_BaseLink('\A', 'a'), new BaseTest_BaseLink('\A', 'a')],
            'same title'              => [true, new BaseTest_BaseLink('\A', 'a'), new BaseTest_BaseLink('\B', 'a')],
            'no title'                => [false, new BaseTest_BaseLink('\A'), new BaseTest_BaseLink('\B')],
            'different class'         => [true, new BaseTest_BaseLink('\A\B'), new BaseTest_BaseLink('\B\B')],
            'no title but same'       => [true, new BaseTest_BaseLink('\A'), new BaseTest_Link('\A')],
            'same class but not base' => [true, new BaseTest_BaseLink('\A', 'a'), new BaseTest_Link('\A', 'a')],
        ];
    }
    //</editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class BaseTest_BaseLink extends Base {
    public function __construct(
        string $class,
        public readonly ?string $title = null,
    ) {
        parent::__construct($class);
    }

    #[Override]
    protected function getTargetNode(ClassLike $class): ?Node {
        return null;
    }

    #[Override]
    public function getTitle(): ?string {
        return $this->title;
    }

    #[Override]
    public function __toString(): string {
        return $this->class;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
readonly class BaseTest_Link implements Link {
    public function __construct(
        public string $class,
        public ?string $title = null,
    ) {
        // empty
    }

    #[Override]
    public function __toString(): string {
        return $this->class;
    }

    #[Override]
    public function getTitle(): ?string {
        return $this->title;
    }

    #[Override]
    public function isSimilar(Link $link): bool {
        return false;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSource(File $file, Package $package): array|FilePath|null {
        return null;
    }

    #[Override]
    public function getTarget(Resolver $resolver, File $source): ?LinkTarget {
        return null;
    }
}
