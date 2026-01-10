<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\AstRestorer;

use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\AstRestorer\Footnote\Extension as FootnoteExtension;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use Override;

/**
 * Some nodes removed from the AST while/after parsing. We need them back.
 *
 * @see https://github.com/thephpleague/commonmark/issues/419
 *
 * @internal
 */
class Extension implements ExtensionInterface {
    #[Override]
    public function register(EnvironmentBuilderInterface $environment): void {
        (new FootnoteExtension())->register($environment);

        $environment
            ->addRenderer(Node::class, new Renderer());
    }
}
