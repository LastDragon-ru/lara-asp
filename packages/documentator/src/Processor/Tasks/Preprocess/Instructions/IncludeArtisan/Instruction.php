<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan;

use Exception;
use Illuminate\Contracts\Console\Kernel;
use LastDragon_ru\LaraASP\Core\Application\ApplicationResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\InstructionFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Exceptions\ArtisanCommandError;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Exceptions\ArtisanCommandFailed;
use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

use function dirname;
use function getenv;
use function putenv;
use function strtr;
use function trim;

/**
 * Executes the `<target>` as Artisan command and returns result.
 *
 * Please note that the working directory will not be changed to the file
 * directory (like `include:exec` do). This behavior is close to how Artisan
 * normally works (I'm also not sure that it is possible to change the current
 * working directory in any robust way when you call Artisan command from code).
 * You can use one of the special variables inside command args instead.
 *
 * Also, the command will not inherit the current verbosity level, it will be
 * run with default/normal level if it is not specified in its arguments.
 *
 * @implements InstructionContract<Parameters>
 */
class Instruction implements InstructionContract {
    public function __construct(
        protected readonly ApplicationResolver $application,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:artisan';
    }

    #[Override]
    public static function getParameters(): string {
        return Parameters::class;
    }

    #[Override]
    public function __invoke(Context $context, string $target, mixed $parameters): string {
        $verbosity = $this->setVerbosity(null);

        try {
            $app    = $this->application->getInstance();
            $kernel = $app->make(Kernel::class);
            $input  = new StringInput($this->getCommand($context, $target, $parameters));
            $output = new BufferedOutput();
            $result = $kernel->handle($input, $output);

            if ($result !== Command::SUCCESS) {
                throw new ArtisanCommandFailed($context, $result);
            }

            return trim($output->fetch());
        } catch (InstructionFailed $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new ArtisanCommandError($context, $exception);
        } finally {
            $this->setVerbosity($verbosity);
        }
    }

    protected function getCommand(Context $context, string $target, Parameters $parameters): string {
        $file      = $context->file->getPath();
        $directory = dirname($file);
        $target    = strtr($target, [
            '{$directory}' => $directory,
            '{$file}'      => $file,
        ]);

        return $target;
    }

    protected function setVerbosity(?int $verbosity): ?int {
        // Symfony sets `SHELL_VERBOSITY` via `putenv`. We need to overwrite it,
        // otherwise instruction output will be empty when `--quiet` passed to
        // the `preprocess` command.

        // phpcs:disable SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable

        $env      = 'SHELL_VERBOSITY';
        $previous = getenv($env);
        $previous = $previous !== false ? (int) $previous : null;

        if ($verbosity !== null) {
            putenv("{$env}={$verbosity}");

            $_ENV[$env]    = $verbosity;
            $_SERVER[$env] = $verbosity;
        } else {
            putenv($env);

            unset($_ENV[$env]);
            unset($_SERVER[$env]);
        }

        // phpcs:enable

        return $previous;
    }
}
