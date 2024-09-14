<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Reference\Reference;
use Override;
use PhpParser\NameContext;
use PhpParser\Node\Name;

use function preg_replace_callback;
use function trim;

use const PREG_UNMATCHED_AS_NULL;

/**
 * @implements Metadata<?Document>
 */
class PhpClassMarkdown implements Metadata {
    public function __construct(
        protected readonly PhpClassComment $comment,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(File $file): mixed {
        // Comment?
        $comment = $file->getMetadata($this->comment);

        if (!$comment) {
            return null;
        }

        // Parse
        $content  = $this->preprocess($comment->context, $comment->comment->getText());
        $document = new Document(trim($content), $file->getPath());

        return $document;
    }

    private function preprocess(NameContext $context, string $string): string {
        return (string) preg_replace_callback(
            pattern : '/\{@(?:see|link)\s+(?P<reference>[^}\s]+)\s?}/imu',
            callback: static function (array $matches) use ($context): string {
                $result    = $matches[0];
                $reference = Reference::parse(
                    $matches['reference'],
                    static function (string $class) use ($context): string {
                        return (string) $context->getResolvedClassName(new Name($class));
                    },
                );

                if ($reference) {
                    $result = "`{$reference}`";
                }

                return $result;
            },
            subject : $string,
            flags   : PREG_UNMATCHED_AS_NULL,
        );
    }
}
