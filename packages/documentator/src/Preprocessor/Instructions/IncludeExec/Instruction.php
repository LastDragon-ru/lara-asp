<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExec;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Process\Factory;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ProcessableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetExecFailed;
use Override;

use function dirname;
use function trim;

class Instruction implements ProcessableInstruction {
    protected readonly Factory $factory;

    public function __construct() {
        $this->factory = Container::getInstance()->make(Factory::class); // next(documentator): Inject in constructor
    }

    #[Override]
    public static function getName(): string {
        return 'include:exec';
    }

    #[Override]
    public static function getDescription(): string {
        return 'Executes the `<target>` and returns result.';
    }

    #[Override]
    public static function getTargetDescription(): ?string {
        return 'Path to the executable.';
    }

    #[Override]
    public function process(string $path, string $target): string {
        try {
            return trim($this->factory->newPendingProcess()->path(dirname($path))->run($target)->throw()->output());
        } catch (Exception $exception) {
            throw new TargetExecFailed($path, $target, $exception);
        }
    }
}
