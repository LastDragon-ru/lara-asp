<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExec;

use Exception;
use Illuminate\Process\Factory;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExec\Exceptions\TargetExecFailed;
use Override;

use function trim;

/**
 * Executes the `<target>` and returns result.
 *
 * The working directory is equal to the file directory. If you want to run
 * Artisan command, please check `include:artisan` instruction.
 *
 * @implements InstructionContract<Parameters>
 */
class Instruction implements InstructionContract {
    public function __construct(
        protected readonly Factory $factory,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:exec';
    }

    #[Override]
    public static function getPriority(): ?int {
        return null;
    }

    #[Override]
    public static function getParameters(): string {
        return Parameters::class;
    }

    #[Override]
    public function __invoke(Context $context, InstructionParameters $parameters): string {
        try {
            return trim(
                $this->factory->newPendingProcess()
                    ->path((string) $context->file->getPath()->getDirectoryPath())
                    ->run($parameters->target)
                    ->throw()
                    ->output(),
            );
        } catch (Exception $exception) {
            throw new TargetExecFailed($context, $exception);
        }
    }
}
