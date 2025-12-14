<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDocumentFactory;
use Override;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

use function array_map;

/**
 * @implements FileCast<ParsedFile>
 */
class Parsed implements FileCast {
    public function __construct(
        protected readonly PhpDocumentFactory $factory,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(Resolver $resolver, File $file): object {
        $resolver  = new NameResolver();
        $collector = new ParsedCollector();
        $traverser = new NodeTraverser();

        $traverser->addVisitor($resolver);
        $traverser->addVisitor($collector);

        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $stmts  = (array) $parser->parse($file->content);

        $traverser->traverse($stmts);

        return new ParsedFile(
            $file->path,
            $resolver->getNameContext(),
            function (ParsedFile $file) use ($collector): array {
                return array_map(
                    fn ($node) => new ParsedClass($this->factory, $file, $node),
                    $collector->classes,
                );
            },
        );
    }
}
