<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExec;

use Exception;
use Illuminate\Process\Factory;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetExecFailed;
use Override;

use function trim;

/**
 * Executes the `<target>` and returns result.
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
                    ->path($context->directory->getPath())
                    ->run($target)
                    ->throw()
                    ->output(),
            );
        } catch (Exception $exception) {
            throw new TargetExecFailed($context, $exception);
        }
    }
}
