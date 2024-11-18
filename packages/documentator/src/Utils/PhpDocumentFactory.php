<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use PhpParser\NameContext;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use ReflectionClass;
use ReflectionProperty;

use function file_get_contents;

/**
 * @internal
 */
class PhpDocumentFactory {
    /**
     * @var array<string, NameContext>
     */
    private array $context = [];

    public function __construct(
        protected readonly LinkFactory $factory,
    ) {
        // empty
    }

    /**
     * @param ReflectionClass<object>|ReflectionProperty $object
     */
    public function __invoke(ReflectionClass|ReflectionProperty $object): Document {
        $document = null;
        $path     = match (true) {
            $object instanceof ReflectionProperty => $object->getDeclaringClass()->getFileName(),
            default                               => $object->getFileName(),
        };

        if ($path !== false) {
            $phpdoc   = new PhpDoc((string) $object->getDocComment());
            $context  = $this->getContext($path);
            $document = $phpdoc->getDocument($this->factory, $context, new FilePath($path));
        } else {
            $phpdoc   = new PhpDoc((string) $object->getDocComment());
            $document = new Document($phpdoc->getText(), null);
        }

        return $document;
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
