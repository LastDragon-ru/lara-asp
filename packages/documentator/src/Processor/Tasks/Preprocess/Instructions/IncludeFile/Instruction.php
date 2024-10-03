<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeFile;

use Generator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use Override;

/**
 * Includes the `<target>` file.
 *
 * @implements InstructionContract<Parameters>
 */
class Instruction implements InstructionContract {
    public function __construct(
        protected readonly Markdown $markdown,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:file';
    }

    #[Override]
    public static function getPriority(): ?int {
        return null;
    }

    #[Override]
    public static function getParameters(): string {
        return Parameters::class;
    }

    /**
     * @return Generator<mixed, Dependency<*>, mixed, Document|string>
     */
    #[Override]
    public function __invoke(Context $context, string $target, mixed $parameters): Generator {
        $file    = Cast::to(File::class, yield new FileReference($target));
        $content = $file->getMetadata($this->markdown) ?? $file->getContent();

        return $content;
    }
}
