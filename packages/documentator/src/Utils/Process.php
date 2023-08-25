<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as SymfonyProcess;

use function trim;

class Process {
    public function __construct() {
        // empty
    }

    /**
     * @param list<string> $command
     */
    public function run(array $command, string $cwd = null): string {
        $process = new SymfonyProcess($command, $cwd);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }
}
