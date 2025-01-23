<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Utils::class)]
final class UtilsTest extends TestCase {
    public function testIsPathRelative(): void {
        // Nope
        self::assertFalse(Utils::isPathRelative('tel:+70000000000'));
        self::assertFalse(Utils::isPathRelative('urn:example.example'));
        self::assertFalse(Utils::isPathRelative('//example.com/'));
        self::assertFalse(Utils::isPathRelative('https://example.com/'));
        self::assertFalse(Utils::isPathRelative('mailto:mail@example.com'));
        self::assertFalse(Utils::isPathRelative('/path/to/file.md'));

        // Yep
        self::assertTrue(Utils::isPathRelative('.'));
        self::assertTrue(Utils::isPathRelative('..'));
        self::assertTrue(Utils::isPathRelative('path/to/file.md'));
        self::assertTrue(Utils::isPathRelative('./path/to/file.md'));
        self::assertTrue(Utils::isPathRelative('?query'));
        self::assertTrue(Utils::isPathRelative('#fragment'));
    }

    public function testIsPathToSelf(): void {
        $md = $this->app()->make(Markdown::class);
        $a  = $md->parse('', new FilePath('/path/to/a.md'));
        $b  = $md->parse('', new FilePath('/path/to/b.md'));

        self::assertTrue(Utils::isPathToSelf($a, '.'));
        self::assertTrue(Utils::isPathToSelf($a, '#fragment'));
        self::assertTrue(Utils::isPathToSelf($a, './a.md#fragment'));
        self::assertTrue(Utils::isPathToSelf($a, '?a=bc'));
        self::assertTrue(Utils::isPathToSelf($a, 'a.md'));
        self::assertTrue(Utils::isPathToSelf($a, './a.md'));
        self::assertTrue(Utils::isPathToSelf($a, '../to/a.md'));
        self::assertFalse(Utils::isPathToSelf($a, '/a.md'));
        self::assertFalse(Utils::isPathToSelf($a, '../a.md'));

        self::assertTrue(Utils::isPathToSelf($b, '/path/to/b.md'));
        self::assertFalse(Utils::isPathToSelf($b, 'a.md'));
        self::assertFalse(Utils::isPathToSelf($b, './a.md#fragment'));

        self::assertTrue(Utils::isPathToSelf($a, new FilePath('a.md')));
        self::assertTrue(Utils::isPathToSelf($a, new FilePath('../to/a.md')));
    }

    public function testIsHeadingAtx(): void {
        self::assertTrue(Utils::isHeadingAtx('# Header'));
        self::assertFalse(
            Utils::isHeadingAtx(
                <<<'MARKDOWN'
            Header
            ------
            MARKDOWN,
            ),
        );
    }

    public function testIsHeadingSetext(): void {
        self::assertFalse(Utils::isHeadingSetext('# Header'));
        self::assertTrue(
            Utils::isHeadingSetext(
                <<<'MARKDOWN'
            Header
            ------
            MARKDOWN,
            ),
        );
    }

    public function testGetHeadingText(): void {
        self::assertSame(
            'Header',
            Utils::getHeadingText(
                <<<'MARKDOWN'
                # Header

                MARKDOWN,
            ),
        );
        self::assertSame(
            <<<'TEXT'
            Header
            line b
            TEXT,
            Utils::getHeadingText(
                <<<'MARKDOWN'
                Header
                line b
                ======

                MARKDOWN,
            ),
        );
    }
}
