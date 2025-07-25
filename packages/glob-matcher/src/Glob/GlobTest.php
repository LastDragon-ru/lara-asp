<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob;

use Exception;
use LastDragon_ru\GlobMatcher\Package;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_diff_key;
use function array_keys;
use function assert;
use function basename;
use function count;
use function dirname;
use function file;
use function implode;
use function is_array;
use function is_bool;
use function is_string;
use function json_decode;
use function json_encode;
use function mb_substr;
use function mb_trim;
use function str_starts_with;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
#[CoversClass(Glob::class)]
final class GlobTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array<array-key, string> $expected
     * @param array<array-key, string> $paths
     */
    #[DataProvider('dataProviderIsMatch')]
    public function testIsMatch(array $expected, array $paths, string $pattern, ?Options $options): void {
        $actual = [];

        foreach ($paths as $path) {
            $match = (new Glob($pattern, $options))->isMatch($path);

            if ($match) {
                $actual[] = $path;
            }
        }

        self::assertSame($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{array<array-key, string>, array<array-key, string>, string, ?Options}>
     */
    public static function dataProviderIsMatch(): array {
        $data    = [];
        $default = new Options();
        $allowed = [
            'matchCase' => true,
            'globstar'  => true,
            'extended'  => true,
            'hidden'    => true,
        ];

        try {
            $file  = self::getTestData()->path('IsMatch/index.txt');
            $lines = self::getIsMatchIterator($file, basename($file));

            foreach ($lines as $line => $string) {
                try {
                    $test = json_decode($string, true, flags: JSON_THROW_ON_ERROR);
                    $test = (array) $test + [null, null, null, null];

                    assert(is_bool($test[0]) || is_array($test[0]));
                    assert(is_string($test[1]) || is_array($test[1]));
                    assert(is_string($test[2]));

                    $paths    = (array) $test[1];
                    $expected = is_array($test[0]) ? $test[0] : ($test[0] ? $paths : []);
                    $pattern  = $test[2];
                    $options  = $default;

                    if (is_array($test[3])) {
                        $invalid = array_keys(array_diff_key($test[3], $allowed));

                        if ($invalid !== []) {
                            throw new Exception('Invalid options: `'.implode('`, `', $invalid).'`.');
                        }

                        $test[3] += [
                            'matchCase' => $options->matchCase,
                            'globstar'  => $options->globstar,
                            'extended'  => $options->extended,
                            'hidden'    => $options->hidden,
                        ];
                        $options  = new Options(
                            globstar : (bool) $test[3]['globstar'],
                            extended : (bool) $test[3]['extended'],
                            hidden   : (bool) $test[3]['hidden'],
                            matchCase: (bool) $test[3]['matchCase'],
                        );
                    }

                    $path           = json_encode(count($paths) > 1 ? $paths : $paths[0], JSON_THROW_ON_ERROR);
                    $path           = mb_trim($path, '"', Package::Encoding);
                    $negated        = $expected === [] ? 'not ' : '';
                    $message        = "Path `{$path}` should {$negated}match `{$pattern}` ({$line})";
                    $data[$message] = [
                        self::getIsMatchStrings($expected),
                        self::getIsMatchStrings($paths),
                        $pattern,
                        $options,
                    ];
                } catch (Exception $exception) {
                    $message                     = $exception->getMessage();
                    $data["{$line}: {$message}"] = [[$message], ['path'], 'invalid', $default];
                }
            }
        } catch (Exception $exception) {
            $message        = $exception->getMessage();
            $data[$message] = [[$message], ['path'], 'invalid', $default];
        }

        return $data;
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @return iterable<string, string>
     */
    private static function getIsMatchIterator(string $file, string $prefix = ''): iterable {
        // Read
        $lines = file($file);

        if ($lines === false) {
            throw new Exception("Failed to load test data from `{$file}`.");
        }

        // Process
        foreach ($lines as $line => $string) {
            // Prepare
            $line   = $line + 1;
            $string = mb_trim($string, encoding: Package::Encoding);

            // Ignored?
            if ($string === '' || str_starts_with($string, '#') || str_starts_with($string, '//')) {
                continue;
            }

            // Return
            $key = ($prefix !== '' ? "{$prefix}#L" : '').$line;

            if (str_starts_with($string, '@')) {
                $include = mb_substr($string, 1, encoding: Package::Encoding);
                $include = mb_trim($include, encoding: Package::Encoding);

                yield from self::getIsMatchIterator(
                    dirname($file).'/'.$include,
                    $key.' / '.$string,
                );
            } else {
                yield $key => $string;
            }
        }

        // Just for the case
        yield from [];
    }

    /**
     * @param array<array-key, mixed> $values
     *
     * @return array<array-key, string>
     */
    private static function getIsMatchStrings(array $values): array {
        // @phpstan-ignore return.type (data are trusted, so method exists only for phpstan to suppress the error)
        return $values;
    }
    // </editor-fold>
}
