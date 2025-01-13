<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use PhpParser\NameContext;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

use function file_get_contents;
use function mb_trim;
use function preg_replace_callback;

use const PREG_UNMATCHED_AS_NULL;

/**
 * @internal
 */
class PhpDocumentFactory {
    /**
     * @var array<string, NameContext>
     */
    private array $context = [];

    public function __construct(
        protected readonly Markdown $markdown,
        protected readonly LinkFactory $factory,
    ) {
        // empty
    }

    public function __invoke(PhpDoc $phpdoc, ?FilePath $path, ?NameContext $context = null): Document {
        $text = $phpdoc->getText();

        if ($path !== null) {
            $context ??= $this->getContext((string) $path);
            $text      = mb_trim(
                (string) preg_replace_callback(
                    pattern : '/\{@(?:see|link)\s+(?P<reference>[^}\s]+)\s?}/imu',
                    callback: function (array $matches) use ($context): string {
                        $result    = $matches[0];
                        $reference = $this->factory->create(
                            $matches['reference'],
                            static function (string $class) use ($context): string {
                                return (string) $context->getResolvedClassName(new Name($class));
                            },
                        );

                        if ($reference !== null) {
                            $result = "`{$reference}`";
                        }

                        return $result;
                    },
                    subject : $text,
                    flags   : PREG_UNMATCHED_AS_NULL,
                ),
            );
        }

        return $this->markdown->parse($text, $path);
    }

    private function getContext(string $path): NameContext {
        // Resolved?
        if (isset($this->context[$path])) {
            return $this->context[$path];
        }

        // Resolve
        $resolver  = new NameResolver();
        $traverser = new NodeTraverser();
        $parser    = (new ParserFactory())->createForNewestSupportedVersion();
        $stmts     = (array) $parser->parse((string) file_get_contents($path));

        $traverser->addVisitor($resolver);
        $traverser->traverse($stmts);

        // Save
        $this->context[$path] = $resolver->getNameContext();

        // Return
        return $this->context[$path];
    }
}
