<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use Override;
use PhpParser\NameContext;
use PhpParser\Node\Name;

use function mb_trim;
use function preg_replace_callback;
use function trigger_deprecation;

use const PREG_UNMATCHED_AS_NULL;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '7.0.0', 'Please use `%s` instead.', PhpClassComment::class);

/**
 * @deprecated 7.0.0 Please use {@see PhpClassComment} instead.
 *
 * @implements Metadata<?Document>
 */
readonly class PhpDocBlock implements Metadata {
    public function __construct(
        protected Markdown $markdown,
        protected LinkFactory $factory,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(File $file): mixed {
        // Class?
        $class = $file->getMetadata(PhpClass::class);

        if ($class === null) {
            return null;
        }

        // Prepare
        $content = (new PhpDoc($class->class->getDocComment()?->getText()))->getText();
        $content = $this->preprocess($class->context, $content);
        $content = mb_trim($content);

        // Create
        return $this->markdown->parse($content, $file->getPath());
    }

    private function preprocess(NameContext $context, string $string): string {
        return (string) preg_replace_callback(
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
            subject : $string,
            flags   : PREG_UNMATCHED_AS_NULL,
        );
    }
}
