<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Defaults\Laravel;

use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Defaults\Output as DefaultOutput;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Verbosity;
use Override;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

use function max;
use function min;
use function strip_tags;

class Output extends DefaultOutput {
    public function __construct(
        protected readonly OutputInterface $output,
    ) {
        parent::__construct(max(80, min((new Terminal())->getWidth(), 150)));
    }

    #[Override]
    public function write(string $line, Verbosity $verbosity): void {
        $this->output->writeln(
            $line,
            match ($verbosity) {
                Verbosity::Debug       => OutputInterface::VERBOSITY_DEBUG,
                Verbosity::Normal      => OutputInterface::VERBOSITY_NORMAL,
                Verbosity::Verbose     => OutputInterface::VERBOSITY_VERBOSE,
                Verbosity::VeryVerbose => OutputInterface::VERBOSITY_VERY_VERBOSE,
            },
        );
    }

    #[Override]
    public function length(string $string): int {
        return parent::length(strip_tags($string));
    }
}
