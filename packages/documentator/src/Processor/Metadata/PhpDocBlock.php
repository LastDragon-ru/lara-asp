<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use Override;
use PhpParser\NameContext;
use PhpParser\Node\Name;

use function preg_replace_callback;
use function trim;

use const PREG_UNMATCHED_AS_NULL;

/**
 * @implements Metadata<?Document>
 */
class PhpDocBlock implements Metadata {
    public function __construct(
        protected readonly PhpClass $class,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(File $file): mixed {
        // Class?
        $class = $file->getMetadata($this->class);

        if (!$class) {
            return null;
        }

        // Parse
        $content = (new PhpDoc($class->class->getDocComment()?->getText()))->getText();
        $content = $this->preprocess($class->context, $content);
        $content = trim($content);

        if (!$content) {
            return new Document('', $file->getPath());
        }

        // Create
        return new Document($content, $file->getPath());
    }

    private function preprocess(NameContext $context, string $string): string {
        return (string) preg_replace_callback(
            pattern : '/\{@(?:see|link)\s+(?P<class>[^}\s\/:]+)(?:::(?P<method>[^(]+\(\)))?\s?\}/imu',
            callback: static function (array $matches) use ($context): string {
                $class  = (string) $context->getResolvedClassName(new Name($matches['class']));
                $method = $matches['method'] ?? null;
                $result = $method
                    ? "`{$class}::{$method}`"
                    : "`{$class}`";

                return $result;
            },
            subject : $string,
            flags   : PREG_UNMATCHED_AS_NULL,
        );
    }
}
