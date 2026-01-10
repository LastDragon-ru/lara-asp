<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use Override;

/**
 * Generated block parser.
 *
 * ```markdown
 * [//]: # (start: block)
 *
 * Block content.
 *
 * [//]: # (end: block)
 * ```
 */
class Extension implements ExtensionInterface {
    #[Override]
    public function register(EnvironmentBuilderInterface $environment): void {
        $environment
            ->addBlockStartParser(new ParserStart())
            ->addRenderer(Node::class, new Renderer());
    }
}
