<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Xml\Constraints;

use Closure;
use DOMDocument;
use Exception;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\PhpUnit\Utils\TestData;
use LastDragon_ru\PhpUnit\Xml\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;

use function preg_replace;
use function strtr;

/**
 * @internal
 */
#[CoversClass(XmlMatchesSchema::class)]
final class XmlMatchesSchemaTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param non-empty-string                  $schema
     * @param Closure(): (DOMDocument|FilePath) $factory
     */
    #[DataProvider('dataProviderEvaluate')]
    public function testEvaluate(true|string $expected, string $schema, Closure $factory): void {
        $schema    = TestData::get()->file($schema);
        $document  = $factory();
        $exception = null;

        try {
            (new XmlMatchesSchema($schema))->evaluate($document);
        } catch (Exception $exception) {
            // empty
        }

        if ($expected === true) {
            self::assertNull($exception);
        } else {
            self::assertInstanceOf(ExpectationFailedException::class, $exception);
            self::assertSame(
                $expected,
                preg_replace(
                    '/DOMDocument Object #\d+ \(\)/',
                    'DOMDocument Object #0 ()',
                    strtr(
                        $exception->getMessage(),
                        [
                            TestData::get()->directory()->path => '',
                        ],
                    ),
                ),
            );
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{true|string, non-empty-string, Closure(): (DOMDocument|FilePath)}>
     */
    public static function dataProviderEvaluate(): array {
        $dom = static function (FilePath $path): DOMDocument {
            $dom = new DOMDocument();

            $dom->load($path->path);

            return $dom;
        };

        return [
            'rng + dom = valid'    => [
                true,
                'schema.rng',
                static fn () => TestData::get()->file('document-valid.xml'),
            ],
            'rng + dom = invalid'  => [
                <<<'TXT'
                Failed asserting that DOMDocument Object #0 () matches schema 'schema.rng'.
                (error) Expecting an element root, got nothing (file: ``, line: `0`, code: `22`)
                TXT,
                'schema.rng',
                static fn () => $dom(TestData::get()->file('.invalid.xml')),
            ],
            'xsd + dom = valid'    => [
                true,
                'schema.xsd',
                static fn () => $dom(TestData::get()->file('document-valid.xml')),
            ],
            'xsd + dom = invalid'  => [
                <<<'TXT'
                Failed asserting that DOMDocument Object #0 () matches schema 'schema.xsd'.
                (error) Element 'a': This element is not expected. Expected is ( child ). (file: `document-invalid.xml`, line: `3`, code: `1871`)
                TXT,
                'schema.xsd',
                static fn () => $dom(TestData::get()->file('document-invalid.xml')),
            ],
            'rng + file = valid'   => [
                true,
                'schema.rng',
                static fn () => TestData::get()->file('document-valid.xml'),
            ],
            'rng + file = invalid' => [
                <<<'TXT'
                Failed asserting that file 'document-invalid.xml' matches schema 'schema.rng'.
                (error) Did not expect element a there (file: `document-invalid.xml`, line: `3`, code: `38`)
                TXT,
                'schema.rng',
                static fn () => TestData::get()->file('document-invalid.xml'),
            ],
            'xsd + file = valid'   => [
                true,
                'schema.xsd',
                static fn () => TestData::get()->file('document-valid.xml'),
            ],
            'xsd + file = invalid' => [
                <<<'TXT'
                Failed asserting that file 'document-invalid.xml' matches schema 'schema.xsd'.
                (error) Element 'a': This element is not expected. Expected is ( child ). (file: `document-invalid.xml`, line: `3`, code: `1871`)
                TXT,
                'schema.xsd',
                static fn () => TestData::get()->file('document-invalid.xml'),
            ],
        ];
    }
    // </editor-fold>
}
