<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php\ClassComment;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\LinkTarget;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use LastDragon_ru\LaraASP\Testing\Mockery\PropertiesMock;
use LastDragon_ru\LaraASP\Testing\Mockery\WithProperties;
use Mockery;
use Override;
use PhpParser\Comment\Doc;
use PhpParser\NameContext;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Base::class)]
final class BaseTest extends TestCase {
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
        $class = Mockery::mock(ClassLike::class, new WithProperties(), PropertiesMock::class);
        $class
            ->shouldUseProperty('namespacedName')
            ->value(new Name('App\\A'));
        $class
            ->shouldReceive('getDocComment')
            ->once()
            ->andReturn(
                Mockery::mock(Doc::class),
            );

        $context = Mockery::mock(NameContext::class);
        $comment = Mockery::mock(PhpDoc::class);
        $comment
            ->shouldReceive('isDeprecated')
            ->twice()
            ->andReturn(true, false);

        $data   = new ClassComment($class, $context, $comment);
        $source = Mockery::mock(File::class);
        $source
            ->shouldReceive('as')
            ->with(ClassComment::class)
            ->twice()
            ->andReturn($data);

        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getRelativePath')
            ->with($source)
            ->twice()
            ->andReturn(
                new FilePath('relative/path/to/class/a.php'),
            );

        $doc = Mockery::mock(Doc::class);
        $doc
            ->shouldReceive('getStartLine')
            ->once()
            ->andReturn(234);
        $doc
            ->shouldReceive('getText')
            ->once()
            ->andReturn('/** @deprecated */');

        $node = Mockery::mock(ClassMethod::class);
        $node
            ->shouldReceive('getStartLine')
            ->never();
        $node
            ->shouldReceive('getEndLine')
            ->once()
            ->andReturn(321);
        $node
            ->shouldReceive('getDocComment')
            ->once()
            ->andReturn($doc);

        $link = Mockery::mock(Base::class, ['\\App\\A']);
        $link->shouldAllowMockingProtectedMethods();
        $link->makePartial();
        $link
            ->shouldReceive('getTargetNode')
            ->twice()
            ->andReturn($class, $node);

        self::assertEquals(
            new LinkTarget(new FilePath('relative/path/to/class/a.php'), true, null, null),
            $link->getTarget($file, $source),
        );

        self::assertEquals(
            new LinkTarget(new FilePath('relative/path/to/class/a.php'), true, 234, 321),
            $link->getTarget($file, $source),
        );
    }

    public function testGetTargetClassNotMatch(): void {
        $file  = Mockery::mock(File::class);
        $class = Mockery::mock(ClassLike::class, new WithProperties(), PropertiesMock::class);
        $class
            ->shouldUseProperty('namespacedName')
            ->value(new Name('App\\A'));
        $context = Mockery::mock(NameContext::class);
        $comment = Mockery::mock(PhpDoc::class);
        $data    = new ClassComment($class, $context, $comment);
        $source  = Mockery::mock(File::class);
        $source
            ->shouldReceive('as')
            ->with(ClassComment::class)
            ->once()
            ->andReturn($data);

        $link = Mockery::mock(Base::class, [$this::class]);
        $link->shouldAllowMockingProtectedMethods();
        $link->makePartial();
        $link
            ->shouldReceive('getTargetNode')
            ->never();

        self::assertNull($link->getTarget($file, $source));
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
    public function getTarget(File $file, File $source): ?LinkTarget {
        return null;
    }
}
