<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Factory as FactoryContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Task as CodeLinksTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Instruction as PreprocessIncludeArtisan;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock\Instruction as PreprocessIncludeDocBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Instruction as PreprocessIncludeDocumentList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Instruction as PreprocessIncludeExample;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExec\Instruction as PreprocessIncludeExec;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeFile\Instruction as PreprocessIncludeFile;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeGraphqlDirective\Instruction as PreprocessIncludeGraphqlDirective;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Instruction as PreprocessIncludePackageList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Instruction as PreprocessIncludeTemplate;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Task as PreprocessTask;
use Override;

class Factory implements FactoryContract {
    public function __construct(
        private readonly ContainerResolver $container,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(): Processor {
        $processor = new Processor($this->container);

        foreach ($this->tasks() as $task => $configurator) {
            $processor->task($task, $configurator);
        }

        return $processor;
    }

    /**
     * @return array<class-string<Task>, Closure(Task): void|null>
     */
    protected function tasks(): array {
        return [
            PreprocessTask::class => static function (PreprocessTask $task): void {
                $task->addInstruction(PreprocessIncludeFile::class);
                $task->addInstruction(PreprocessIncludeExec::class);
                $task->addInstruction(PreprocessIncludeExample::class);
                $task->addInstruction(PreprocessIncludeArtisan::class);
                $task->addInstruction(PreprocessIncludeTemplate::class);
                $task->addInstruction(PreprocessIncludeDocBlock::class);
                $task->addInstruction(PreprocessIncludePackageList::class);
                $task->addInstruction(PreprocessIncludeDocumentList::class);
                $task->addInstruction(PreprocessIncludeGraphqlDirective::class);
            },
            CodeLinksTask::class  => null,
        ];
    }
}
