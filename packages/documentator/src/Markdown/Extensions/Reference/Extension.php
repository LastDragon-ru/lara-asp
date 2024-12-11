<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\ParserStart as ReferenceParser;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentPreParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use Override;

/**
 * Find Reference nodes and their location inside the document
 * (by default, they are not added to the AST)
 *
 * @see https://github.com/thephpleague/commonmark/discussions/1036
 *
 * @internal
 */
class Extension implements ExtensionInterface {
    #[Override]
    public function register(EnvironmentBuilderInterface $environment): void {
        $referenceParser = new ReferenceParser();

        $environment
            ->addBlockStartParser($referenceParser)
            ->addEventListener(
                DocumentPreParsedEvent::class,
                static function (DocumentPreParsedEvent $event) use ($referenceParser): void {
                    $referenceParser->setReferenceMap($event->getDocument()->getReferenceMap());
                },
            )
            ->addRenderer(Node::class, new Renderer());
    }
}
