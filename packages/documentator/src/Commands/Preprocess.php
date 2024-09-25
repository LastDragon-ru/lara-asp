<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use Illuminate\Console\Command;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\HeadingsLevel;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Factory;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Processor;
use LastDragon_ru\LaraASP\Documentator\Processor\Result;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Task as CodeLinksTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Task as PreprocessTask;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Override;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

use function array_map;
use function getcwd;
use function gettype;
use function implode;
use function is_a;
use function is_scalar;
use function ksort;
use function max;
use function mb_strlen;
use function min;
use function rtrim;
use function str_repeat;
use function strtr;
use function strval;
use function trim;
use function var_export;

/**
 * @see Processor
 */
#[AsCommand(
    name       : Preprocess::Name,
    description: 'Perform one or more task on the file.',
)]
class Preprocess extends Command {
    public const Name = Package::Name.':preprocess';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    public $signature = self::Name.<<<'SIGNATURE'
        {path?       : Directory to process.}
        {--exclude=* : Glob(s) to exclude.}
    SIGNATURE;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    public $help = <<<'HELP'
        ## Tasks

        %tasks%
        HELP;

    public function __construct(
        protected readonly Factory $factory,
    ) {
        parent::__construct();
    }

    public function __invoke(Formatter $formatter): void {
        $cwd      = getcwd();
        $path     = Cast::toString($this->argument('path') ?? $cwd);
        $path     = Path::normalize($path);
        $width    = min((new Terminal())->getWidth(), 150);
        $exclude  = array_map(strval(...), (array) $this->option('exclude'));
        $listener = function (string $path, Result $result, float $duration) use ($formatter, $width): void {
            [$resultMessage, $resultColor, $resultVerbosity] = match ($result) {
                Result::Failed  => ['FAIL', 'red', OutputInterface::VERBOSITY_NORMAL],
                Result::Success => ['DONE', 'green', OutputInterface::VERBOSITY_NORMAL],
                Result::Skipped => ['SKIP', 'gray', OutputInterface::VERBOSITY_VERBOSE],
                Result::Missed  => ['MISS', 'gray', OutputInterface::VERBOSITY_VERBOSE],
            };

            $duration = $formatter->duration($duration);
            $length   = $width - (mb_strlen($path) + mb_strlen($duration) + mb_strlen($resultMessage) + 5);
            $line     = $path
                .' '.($length > 0 ? '<fg=gray>'.str_repeat('.', $length).'</>' : '')
                .' '."<fg=gray>{$duration}</>"
                .' '."<fg={$resultColor};options=bold>{$resultMessage}</>";

            $this->output->writeln($line, OutputInterface::OUTPUT_NORMAL | $resultVerbosity);
        };

        $duration = ($this->factory)()->run($path, $exclude, $listener);

        $this->output->newLine();
        $this->output->writeln("<fg=green;options=bold>DONE ({$formatter->duration($duration)})</>");
    }

    #[Override]
    public function getProcessedHelp(): string {
        return strtr(parent::getProcessedHelp(), [
            '%tasks%' => trim($this->getProcessedHelpTasks(3)),
        ]);
    }

    protected function getProcessedHelpTasks(int $level): string {
        $help      = '';
        $heading   = str_repeat('#', $level);
        $processor = ($this->factory)();

        foreach ($processor->tasks() as $index => $task) {
            $description = '_No description provided_.';
            $description = trim($this->getProcessedHelpTaskDescription($task, $level + 1)) ?: $description;
            $extensions  = '`'.implode('`, `', $task::getExtensions()).'`';
            $title       = trim((string) $this->getProcessedHelpTaskTitle($task)) ?: "Task №{$index}";
            $help       .= <<<MARKDOWN
                {$heading} {$title} ({$extensions})

                {$description}


                MARKDOWN;
        }

        return $help ?: '_No tasks defined_.';
    }

    protected function getProcessedHelpTaskTitle(Task $task): ?string {
        return match (true) {
            $task instanceof PreprocessTask => 'Preprocess',
            $task instanceof CodeLinksTask  => 'Code Links',
            default                         => null,
        };
    }

    protected function getProcessedHelpTaskDescription(Task $task, int $level): string {
        $help = $this->getDocBlock(new ReflectionClass($task), null, $level);

        if ($task instanceof PreprocessTask) {
            $help .= "\n\n".$this->getProcessedHelpTaskPreprocessInstructions($task, $level);
        }

        return $help;
    }

    protected function getProcessedHelpTaskPreprocessInstructions(PreprocessTask $task, int $level): string {
        $instructions = $task->getInstructions();
        $heading      = str_repeat('#', $level);
        $help         = [];

        foreach ($instructions as $instruction) {
            $class  = new ReflectionClass($instruction);
            $name   = $instruction::getName();
            $desc   = $this->getDocBlock($class, null, $level + 1);
            $target = $this->getProcessedHelpTaskPreprocessInstructionTarget($instruction, 'target', 2);
            $target = trim($target ?: '_No description provided_.');
            $params = $this->getProcessedHelpTaskPreprocessParameters($instruction, 'target', 2);

            if ($params !== null) {
                $help[$name] = rtrim(
                    <<<HELP
                    {$heading} `[{$name}]: <target> <parameters>`

                    * `<target>` - {$target}
                    * `<parameters>` - additional parameters
                    {$params}

                    {$desc}
                    HELP,
                );
            } else {
                $help[$name] = rtrim(
                    <<<HELP
                    {$heading} `[{$name}]: <target>`

                    * `<target>` - {$target}

                    {$desc}
                    HELP,
                );
            }
        }

        ksort($help);

        return implode("\n\n", $help);
    }

    /**
     * @param class-string<Instruction<covariant Parameters>> $instruction
     * @param int<0, max>                                     $padding
     */
    protected function getProcessedHelpTaskPreprocessInstructionTarget(
        string $instruction,
        string $target,
        int $padding,
    ): ?string {
        $class = new ReflectionProperty($instruction::getParameters(), $target);
        $help  = $this->getDocBlock($class, $padding);

        return $help;
    }

    /**
     * @param class-string<Instruction<covariant Parameters>> $instruction
     * @param int<0, max>                                     $padding
     */
    protected function getProcessedHelpTaskPreprocessParameters(
        string $instruction,
        string $target,
        int $padding,
    ): ?string {
        // Serializable?
        $class = $instruction::getParameters();

        if (!is_a($class, Serializable::class, true)) {
            return ''; // not yet supported...
        }

        // Extract
        $class      = new ReflectionClass($class);
        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);
        $parameters = [];

        foreach ($properties as $property) {
            // Ignored?
            if (!$property->isPublic() || $property->isStatic() || $property->getName() === $target) {
                continue;
            }

            // Name
            $name       = $property->getName();
            $definition = $name;
            $hasDefault = $property->hasDefaultValue();
            $theDefault = $hasDefault
                ? $property->getDefaultValue()
                : null;

            if ($property->hasType()) {
                $definition = "{$definition}: {$property->getType()}";
            }

            if ($property->isPromoted()) {
                foreach ($class->getConstructor()?->getParameters() ?? [] as $parameter) {
                    if ($parameter->getName() === $name) {
                        $hasDefault = $parameter->isDefaultValueAvailable();
                        $theDefault = $hasDefault
                            ? $parameter->getDefaultValue()
                            : null;

                        break;
                    }
                }
            }

            if ($hasDefault) {
                $default    = is_scalar($theDefault) ? var_export($theDefault, true) : '<'.gettype($theDefault).'>';
                $definition = "{$definition} = {$default}";
            } else {
                // empty
            }

            // Add
            $parameters[trim($definition)] = trim(
                $this->getDocBlock($property, $padding) ?: '_No description provided_.',
            );
        }

        // Empty?
        if (!$parameters) {
            return null;
        }

        // Serialize
        $list = '';

        foreach ($parameters as $definition => $description) {
            $list .= "* `{$definition}` - {$description}\n";
        }

        $list = Text::setPadding($list, $padding);
        $list = rtrim($list);

        // Return
        return $list;
    }

    /**
     * @param ReflectionClass<object>|ReflectionProperty $object
     * @param ?int<0, max>                               $padding
     */
    private function getDocBlock(
        ReflectionClass|ReflectionProperty $object,
        ?int $padding = null,
        ?int $level = null,
    ): string {
        $help = (new PhpDoc($object->getDocComment() ?: null))->getText();

        if ($level !== null) {
            $level = max(1, min(6, $level));
            $help  = (string) (new Document($help))->mutate(new HeadingsLevel($level));
        }

        if ($padding !== null) {
            $help = Text::setPadding($help, $padding);
        }

        return trim($help);
    }
}
