<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use Illuminate\Console\Command;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Move;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Heading\Renumber;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\InstanceFactory;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Writer;
use LastDragon_ru\LaraASP\Documentator\Processor\Processor;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Task as CodeLinksTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Instruction as PreprocessIncludeArtisan;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock\Instruction as PreprocessIncludeDocBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Instruction as PreprocessIncludeDocumentList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Instruction as PreprocessIncludeExample;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExec\Instruction as PreprocessIncludeExec;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeFile\Instruction as PreprocessIncludeFile;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeGraphqlDirective\Instruction as PreprocessIncludeGraphqlDirective;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Instruction as PreprocessIncludePackageList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Instruction as PreprocessIncludeTemplate;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Task as PreprocessTask;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDocumentFactory;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Override;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Console\Attribute\AsCommand;
use UnitEnum;

use function array_map;
use function getcwd;
use function gettype;
use function implode;
use function is_a;
use function is_scalar;
use function ksort;
use function max;
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
    public const  Name              = Package::Name.':preprocess';
    private const DeprecationMarker = '💀';

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

    private ?PhpDocumentFactory $phpDocumentFactory = null;

    public function __construct(
        protected readonly ContainerResolver $container,
    ) {
        parent::__construct();
    }

    public function __invoke(Formatter $formatter): void {
        $cwd     = getcwd();
        $path    = new DirectoryPath(Cast::toString($this->argument('path') ?? $cwd));
        $exclude = array_map(strval(...), (array) $this->option('exclude'));

        $this->processor()
            ->listen((new Writer($this->output, $formatter))(...))
            ->exclude($exclude)
            ->run($path);
    }

    private function processor(): Processor {
        $processor = new Processor($this->container);

        foreach ($this->tasks() as $task) {
            $processor->task($task);
        }

        return $processor;
    }

    /**
     * @return list<InstanceFactory<covariant Task>|Task|class-string<covariant Task>>
     */
    protected function tasks(): array {
        return [
            new InstanceFactory(PreprocessTask::class, static function (PreprocessTask $task): void {
                $task->addInstruction(PreprocessIncludeFile::class);
                $task->addInstruction(PreprocessIncludeExec::class);
                $task->addInstruction(PreprocessIncludeExample::class);
                $task->addInstruction(PreprocessIncludeArtisan::class);
                $task->addInstruction(PreprocessIncludeTemplate::class);
                $task->addInstruction(PreprocessIncludeDocBlock::class);
                $task->addInstruction(PreprocessIncludePackageList::class);
                $task->addInstruction(PreprocessIncludeDocumentList::class);
                $task->addInstruction(PreprocessIncludeGraphqlDirective::class);
            }),
            CodeLinksTask::class,
        ];
    }

    #[Override]
    public function getProcessedHelp(): string {
        try {
            return strtr(parent::getProcessedHelp(), [
                '%tasks%' => trim($this->getProcessedHelpTasks(3)),
            ]);
        } finally {
            $this->phpDocumentFactory = null;
        }
    }

    protected function getProcessedHelpTasks(int $level): string {
        $help      = '';
        $heading   = str_repeat('#', $level);
        $default   = '_No description provided_.';
        $processor = $this->processor();

        foreach ($processor->tasks() as $index => $task) {
            $description = trim($this->getProcessedHelpTaskDescription($task, $level + 1));
            $description = $description !== '' ? $description : $default;
            $extensions  = '`'.implode('`, `', $task::getExtensions()).'`';
            $deprecated  = $this->getDeprecatedMark(new ReflectionClass($task));
            $title       = trim((string) $this->getProcessedHelpTaskTitle($task));
            $title       = $title !== '' ? $title : "Task №{$index}";
            $help       .= <<<MARKDOWN
                {$heading} {$title} ({$extensions}){$deprecated}

                {$description}


                MARKDOWN;
        }

        return $help !== '' ? $help : '_No tasks defined_.';
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
            $class      = new ReflectionClass($instruction);
            $name       = $instruction::getName();
            $desc       = $this->getDocBlock($class, null, $level + 1);
            $target     = trim(
                (string) $this->getProcessedHelpTaskPreprocessInstructionTarget($instruction, 'target', 2),
            );
            $target     = $target !== '' ? $target : '_No description provided_.';
            $params     = $this->getProcessedHelpTaskPreprocessParameters($instruction, 'target', 2);
            $deprecated = $this->getDeprecatedMark($class);

            if ($params !== null) {
                $help[$name] = rtrim(
                    <<<HELP
                    {$heading} `[{$name}]: <target> <parameters>`{$deprecated}

                    * `<target>` - {$target}
                    * `<parameters>` - additional parameters
                    {$params}

                    {$desc}
                    HELP,
                );
            } else {
                $help[$name] = rtrim(
                    <<<HELP
                    {$heading} `[{$name}]: <target>`{$deprecated}

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
                $definition = "`{$definition}`: `{$property->getType()}`";
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
                $default = match (true) {
                    $theDefault instanceof UnitEnum => $theDefault::class.'::'.$theDefault->name,
                    is_scalar($theDefault)          => var_export($theDefault, true),
                    $theDefault === null            => 'null',
                    default                         => '<'.gettype($theDefault).'>',
                };
                $definition = "{$definition} = `{$default}`";
            } else {
                // empty
            }

            // Add
            $definition                    = $this->getDeprecatedMark($property).$definition;
            $description                   = trim($this->getDocBlock($property, $padding));
            $description                   = $description !== '' ? $description : '_No description provided_.';
            $parameters[trim($definition)] = $description;
        }

        // Empty?
        if ($parameters === []) {
            return null;
        }

        // Sort
        ksort($parameters);

        // Serialize
        $list = '';

        foreach ($parameters as $definition => $description) {
            $list .= "* {$definition} - {$description}\n";
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
        // Load
        $this->phpDocumentFactory ??= $this->laravel->make(PhpDocumentFactory::class);
        $phpdoc                     = new PhpDoc((string) $object->getDocComment());
        $path                       = match (true) {
            $object instanceof ReflectionProperty => $object->getDeclaringClass()->getFileName(),
            default                               => $object->getFileName(),
        };
        $path = $path !== false ? new FilePath($path) : null;
        $help = ($this->phpDocumentFactory)($phpdoc, $path);

        // Move to cwd
        $cwd  = new DirectoryPath((string) getcwd());
        $help = $help->mutate(new Move($cwd->getFilePath('help.md')));

        // Level?
        if ($level !== null) {
            $level = max(1, min(6, $level));
            $help  = $help->mutate(new Renumber($level));
        }

        // To string
        $help = (string) $help;

        // Padding?
        if ($padding !== null) {
            $help = Text::setPadding($help, $padding);
        }

        // Return
        return trim($help);
    }

    /**
     * @param ReflectionClass<object>|ReflectionProperty $object
     */
    private function getDeprecatedMark(ReflectionClass|ReflectionProperty $object): string {
        $comment    = $object->getDocComment();
        $deprecated = $comment !== false && (new PhpDoc($comment))->isDeprecated();
        $deprecated = $deprecated ? ' '.self::DeprecationMarker : '';

        return $deprecated;
    }
}
