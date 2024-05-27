<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExec;

use Exception;
use Illuminate\Process\Factory;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExec\Exceptions\TargetExecFailed;
use Override;

use function dirname;
use function trim;

/**
 * Executes the `<target>` and returns result.
 *
 * The working directory is equal to the file directory. If you want to run
 * Artisan command, please check `include:artisan` instruction.
 *
 * @implements InstructionContract<string, null>
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
    public static function getResolver(): string {
        return Resolver::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return null;
    }

    #[Override]
    public function __invoke(Context $context, mixed $target, mixed $parameters): string {
        try {
            return trim(
                $this->factory->newPendingProcess()
                    ->path(dirname($context->file->getPath()))
                    ->run($target)
                    ->throw()
                    ->output(),
            );
        } catch (Exception $exception) {
            throw new TargetExecFailed($context, $exception);
        }
    }
}
