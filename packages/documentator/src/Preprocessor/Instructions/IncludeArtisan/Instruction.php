<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeArtisan;

use Exception;
use Illuminate\Contracts\Console\Kernel;
use LastDragon_ru\LaraASP\Core\Application\ApplicationResolver;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\InstructionFailed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeArtisan\Exceptions\ArtisanCommandError;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeArtisan\Exceptions\ArtisanCommandFailed;
use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

use function trim;

/**
 * Executes the `<target>` as Artisan command and returns result.
 *
 * @implements InstructionContract<string, null>
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
            $app    = $this->application->getInstance();
            $kernel = $app->make(Kernel::class);
            $input  = new StringInput($target);
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
        }
    }
}
