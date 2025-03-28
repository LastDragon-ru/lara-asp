<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock;

use Generator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Body;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Summary;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use Override;

/**
 * Includes the docblock of the first PHP class/interface/trait/enum/etc
 * from `<target>` file. Inline tags include as is except `@see`/`@link`
 * which will be replaced to FQCN (if possible). Other tags are ignored.
 *
 * @implements InstructionContract<Parameters>
 */
class Instruction implements InstructionContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:docblock';
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
    public function __invoke(Context $context, InstructionParameters $parameters): Generator {
        $target   = $context->file->getFilePath($parameters->target);
        $target   = Cast::to(File::class, yield new FileReference($target));
        $document = $target->as(Document::class);
        $result   = match (true) {
            $parameters->summary && $parameters->description => $document,
            $parameters->summary                             => $document->mutate(new Summary()),
            $parameters->description                         => $document->mutate(new Body()),
            default                                          => '',
        };

        return $result;
    }
}
