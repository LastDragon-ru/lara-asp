<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Locator;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Parser\Inline\BacktickParser;
use League\CommonMark\Extension\CommonMark\Parser\Inline\CloseBracketParser;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\Footnote\Parser\FootnoteRefParser;
use Override;

/**
 * Determine locations of the Links/Images/etc.
 */
class Extension implements ExtensionInterface {
    #[Override]
    public function register(EnvironmentBuilderInterface $environment): void {
        $environment
            ->addInlineParser(new Parser(new CloseBracketParser()), 100)
            ->addInlineParser(new Parser(new BacktickParser()), 200)
            ->addEventListener(
                DocumentParsedEvent::class,
                new Listener(),
            );

        if ($environment->getConfiguration()->exists('footnote')) {
            $environment->addInlineParser(new Parser(new FootnoteRefParser()), 100);
        }
    }
}
