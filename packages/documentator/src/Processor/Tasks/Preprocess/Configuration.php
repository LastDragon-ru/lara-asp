<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use Attribute;
use LastDragon_ru\LaraASP\Documentator\Processor\InstanceConfiguration;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Instruction as IncludeArtisan;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock\Instruction as IncludeDocBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Instruction as IncludeDocumentList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Instruction as IncludeExample;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExec\Instruction as IncludeExec;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeFile\Instruction as IncludeFile;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeGraphqlDirective\Instruction as IncludeGraphqlDirective;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Instruction as IncludePackageList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Instruction as IncludeTemplate;
use Override;

/**
 * @extends InstanceConfiguration<Task>
 */
#[Attribute(Attribute::TARGET_CLASS)]
readonly class Configuration extends InstanceConfiguration {
    #[Override]
    public function __invoke(object $task): void {
        $task->addInstruction(IncludeFile::class);
        $task->addInstruction(IncludeExec::class);
        $task->addInstruction(IncludeExample::class);
        $task->addInstruction(IncludeArtisan::class);
        $task->addInstruction(IncludeTemplate::class);
        $task->addInstruction(IncludeDocBlock::class);
        $task->addInstruction(IncludePackageList::class);
        $task->addInstruction(IncludeDocumentList::class);
        $task->addInstruction(IncludeGraphqlDirective::class);
    }
}
