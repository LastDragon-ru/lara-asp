<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Filesystem\Constraints;

use Exception;
use LastDragon_ru\PhpUnit\Package\TestCase;
use LastDragon_ru\PhpUnit\Utils\TestData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;

use function array_merge;
use function strtr;

/**
 * @internal
 */
#[CoversClass(DirectoryEquals::class)]
final class DirectoryEqualsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testEvaluateSelf(): void {
        $expected   = TestData::get()->directory('source');
        $constraint = new DirectoryEquals($expected);

        self::assertTrue($constraint->evaluate($expected, '', true));
    }

    public function testEvaluateEquals(): void {
        $actual     = TestData::get()->directory('test-equals');
        $expected   = TestData::get()->directory('source');
        $constraint = new DirectoryEquals($expected);

        self::assertTrue($constraint->evaluate($actual, '', true));
    }

    /**
     * @param array<string, mixed> $expected
     */
    #[DataProvider('dataProviderEvaluateFail')]
    public function testEvaluateFail(array $expected, string $directory): void {
        $actual    = TestData::get()->directory($directory);
        $source    = TestData::get()->directory('source');
        $exception = null;

        try {
            (new DirectoryEquals($source))->evaluate($actual);
        } catch (Exception $exception) {
            // empty
        }

        self::assertInstanceOf(ExpectationFailedException::class, $exception);
        self::assertSame(
            array_merge(
                [
                    'message' => "Failed asserting that directory '{$actual->name}/' equals to directory 'source/'.",
                ],
                $expected,
            ),
            [
                'message'  => strtr($exception->getMessage(), [
                    $source->path => "{$source->name}/",
                    $actual->path => "{$actual->name}/",
                ]),
                'expected' => $exception->getComparisonFailure()?->getExpected(),
                'actual'   => $exception->getComparisonFailure()?->getActual(),
            ],
        );
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function dataProviderEvaluateFail(): array {
        return [
            'Size match, content not' => [
                [
                    'expected' => [
                        'b/' => [
                            [
                                'name'    => 'a.txt',
                                'content' => 'not',
                            ],
                        ],
                    ],
                    'actual'   => [
                        'b/' => [
                            [
                                'name'    => 'a.txt',
                                'content' => 'equals',
                            ],
                        ],
                    ],
                ],
                'test-size-match-content-not',
            ],
            'Size mismatch'           => [
                [
                    'expected' => [
                        './' => [
                            [
                                'name' => 'a.txt',
                                'size' => 6,
                            ],
                        ],
                    ],
                    'actual'   => [
                        './' => [
                            [
                                'name' => 'a.txt',
                                'size' => 29,
                            ],
                        ],
                    ],
                ],
                'test-size-mismatch',
            ],
            'Something missed'        => [
                [
                    'expected' => [
                        'b/' => [
                            [
                                'name' => 'a/',
                                'size' => null,
                            ],
                            [
                                'name' => 'b.txt',
                                'size' => 8,
                            ],
                        ],
                    ],
                    'actual'   => [
                        'b/' => [
                            null,
                            null,
                        ],
                    ],
                ],
                'test-something-missed',
            ],
            'Something added'         => [
                [
                    'expected' => [
                        'a/' => [
                            null,
                        ],
                    ],
                    'actual'   => [
                        'a/' => [
                            [
                                'name' => 'c.txt',
                                'size' => 8,
                            ],
                        ],
                    ],
                ],
                'test-something-added',
            ],
        ];
    }
    //</editor-fold>
}
