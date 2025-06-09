<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\FileSystem;

use ArrayIterator;
use Closure;
use Exception;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use SebastianBergmann\Comparator\ComparisonFailure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
#[CoversClass(DirectoryMatchesDirectory::class)]
final class DirectoryMatchesDirectoryTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param true|array{string, array{string, string}} $expected
     * @param Closure(): array<string, SplFileInfo>     $expectedContentFactory
     * @param Closure(): array<string, SplFileInfo>     $actualContentFactory
     */
    #[DataProvider('dataProviderEvaluate')]
    public function testEvaluate(
        true|array $expected,
        Closure $expectedContentFactory,
        Closure $actualContentFactory,
    ): void {
        $constraint = Mockery::mock(DirectoryMatchesDirectory::class, ['/path/to/expected']);
        $constraint->shouldAllowMockingProtectedMethods();
        $constraint->makePartial();
        $constraint
            ->shouldReceive('getContent')
            ->with('/path/to/expected')
            ->once()
            ->andReturn($expectedContentFactory());
        $constraint
            ->shouldReceive('getContent')
            ->with('/path/to/actual')
            ->once()
            ->andReturn($actualContentFactory());

        if ($expected === true) {
            self::assertTrue($constraint->evaluate('/path/to/actual', '', true));
        } else {
            $actual = null;

            $constraint
                ->shouldReceive('fail')
                ->once()
                ->andReturnUsing(
                    static function (
                        mixed $other,
                        string $description,
                        ?ComparisonFailure $failure = null,
                    ) use (
                        &$actual,
                    ): never {
                        $actual = [
                            $description,
                            [
                                $failure?->getExpectedAsString(),
                                $failure?->getActualAsString(),
                            ],
                        ];

                        throw new Exception();
                    },
                );

            try {
                $constraint->evaluate('/path/to/actual');
            } catch (Exception $exception) {
                // empty
            }

            self::assertEquals($expected, $actual);
        }
    }

    public function testToString(): void {
        $constraint = new DirectoryMatchesDirectory('path/to/directory');

        self::assertSame("matches directory 'path/to/directory'", $constraint->toString());
    }

    public function testGetContent(): void {
        $a      = self::getSplFileInfo(true, path: '/path/to/a');
        $b      = self::getSplFileInfo(false, path: '/path/b');
        $c      = self::getSplFileInfo(false, path: '/c');
        $finder = Mockery::mock(Finder::class);
        $finder
            ->shouldReceive('getIterator')
            ->once()
            ->andReturn(
                new ArrayIterator([
                    $a,
                    $b,
                    $c,
                ]),
            );

        $directory  = 'path/to/directory';
        $constraint = Mockery::mock(DirectoryMatchesDirectory::class, [$directory]);
        $constraint->shouldAllowMockingProtectedMethods();
        $constraint->makePartial();
        $constraint
            ->shouldReceive('getFinder')
            ->with($directory)
            ->once()
            ->andReturn($finder);

        self::assertEquals(
            [
                '/c'          => $c,
                '/path/b'     => $b,
                '/path/to/a/' => $a,
            ],
            $constraint->getContent($directory),
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{
     *      true|array{string, array{string, string}},
     *      Closure(): array<string, SplFileInfo>,
     *      Closure(): array<string, SplFileInfo>,
     *      }>
     */
    public static function dataProviderEvaluate(): array {
        return [
            'empty'           => [
                true,
                static fn () => [],
                static fn () => [],
            ],
            'equal'           => [
                true,
                static fn () => [
                    'a.txt' => self::getSplFileInfo(false, 'a'),
                ],
                static fn () => [
                    'a.txt' => self::getSplFileInfo(null, 'a'),
                ],
            ],
            'more files'      => [
                [
                    '',
                    [
                        <<<'TXT'
                        'a.txt'
                        TXT,
                        <<<'TXT'
                        'a.txt
                        b.txt'
                        TXT,
                    ],
                ],
                static fn () => [
                    'a.txt' => self::getSplFileInfo(),
                ],
                static fn () => [
                    'a.txt' => self::getSplFileInfo(),
                    'b.txt' => self::getSplFileInfo(),
                ],
            ],
            'less files'      => [
                [
                    '',
                    [
                        <<<'TXT'
                        'a.txt
                        b.txt'
                        TXT,
                        <<<'TXT'
                        'a.txt'
                        TXT,
                    ],
                ],
                static fn () => [
                    'a.txt' => self::getSplFileInfo(),
                    'b.txt' => self::getSplFileInfo(),
                ],
                static fn () => [
                    'a.txt' => self::getSplFileInfo(),
                ],
            ],
            'files not equal' => [
                [
                    <<<'TXT'
                    Content of the 'a.txt' file is different.
                    TXT,
                    [
                        "'a'",
                        "'b'",
                    ],
                ],
                static fn () => [
                    'a.txt' => self::getSplFileInfo(false, 'a'),
                ],
                static fn () => [
                    'a.txt' => self::getSplFileInfo(null, 'b'),
                ],
            ],
        ];
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    private static function getSplFileInfo(
        ?bool $isDir = null,
        ?string $content = null,
        ?string $path = null,
    ): SplFileInfo {
        $info = Mockery::mock(SplFileInfo::class);

        if ($content !== null) {
            $info
                ->shouldReceive('getContents')
                ->once()
                ->andReturn($content);
        }

        if ($isDir !== null) {
            $info
                ->shouldReceive('isDir')
                ->once()
                ->andReturn($isDir);
        }

        if ($path !== null) {
            $info
                ->shouldReceive('getRelativePathname')
                ->once()
                ->andReturn($path);
        }

        return $info;
    }
    // </editor-fold>
}
