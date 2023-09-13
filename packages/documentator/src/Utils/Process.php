<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as SymfonyProcess;

use function is_string;
use function trim;

class Process {
    public function __construct() {
        // empty
    }

    /**
     * @param list<string>|string $command
     */
    public function run(array|string $command, string $cwd = null): string {
        $process = is_string($command)
            ? SymfonyProcess::fromShellCommandline($command, $cwd)
            : new SymfonyProcess($command, $cwd);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }
}
