<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Input;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Generated\ParserStart as GenerateParser;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Locator\Listener;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Locator\Parser;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\ParserStart as ReferenceParser;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Event\DocumentPreParsedEvent;
use League\CommonMark\Extension\CommonMark\Parser\Inline\BacktickParser;
use League\CommonMark\Extension\CommonMark\Parser\Inline\CloseBracketParser;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\Footnote\Parser\FootnoteRefParser;
use Override;

/**
 * Customized Parser.
 *
 * We use it for:
 * * find Reference nodes and their location inside the document
 *   (by default, they are not added to the AST)
 * * determine location of the Links/Images/etc
 *
 * @see https://github.com/thephpleague/commonmark/discussions/1036
 * @see Coordinate
 *
 * @internal
 */
class Extension implements ExtensionInterface {
    #[Override]
    public function register(EnvironmentBuilderInterface $environment): void {
        $referenceParser = new ReferenceParser();

        $environment
            ->addExtension(new FootnoteExtension())
            ->addBlockStartParser($referenceParser)
            ->addBlockStartParser(new GenerateParser(), 100)
            ->addInlineParser(new Parser(new CloseBracketParser()), 100)
            ->addInlineParser(new Parser(new FootnoteRefParser()), 100)
            ->addInlineParser(new Parser(new BacktickParser()), 200)
            ->addEventListener(
                DocumentPreParsedEvent::class,
                static function (DocumentPreParsedEvent $event) use ($referenceParser): void {
                    Input::set($event->getDocument(), $event->getMarkdown());

                    $referenceParser->setReferenceMap($event->getDocument()->getReferenceMap());
                },
            )
            ->addEventListener(
                DocumentParsedEvent::class,
                new Listener(),
            );
    }
}
