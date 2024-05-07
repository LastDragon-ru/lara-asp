<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use Illuminate\Console\Command;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Preprocessor;
use LastDragon_ru\LaraASP\Documentator\Utils\Markdown;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Override;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function getcwd;
use function gettype;
use function implode;
use function is_a;
use function is_scalar;
use function ksort;
use function rtrim;
use function strtr;
use function trim;
use function var_export;

/**
 * @see Preprocessor
 */
#[AsCommand(
    name       : Preprocess::Name,
    description: 'Preprocess Markdown files.',
)]
class Preprocess extends Command {
    public const Name = Package::Name.':preprocess';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    public $signature = self::Name.<<<'SIGNATURE'
        {path? : Directory to process.}
    SIGNATURE;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    public $help = <<<'HELP'
        %description%

        ## Instructions

        %instructions%
        HELP;

    public function __construct(
        protected readonly Preprocessor $preprocessor,
    ) {
        parent::__construct();
    }

    public function __invoke(Filesystem $filesystem): void {
        $cwd    = getcwd();
        $path   = Cast::toString($this->argument('path') ?? $cwd);
        $finder = Finder::create()
            ->ignoreVCSIgnored(true)
            ->in($path)
            ->exclude('vendor')
            ->exclude('node_modules')
            ->files()
            ->name('*.md');

        foreach ($finder as $file) {
            $this->components->task(
                $file->getPathname(),
                function () use ($filesystem, $file): void {
                    $path    = $file->getPathname();
                    $content = $file->getContents();
                    $result  = $this->preprocessor->process($path, $content);

                    if ($content !== $result) {
                        $filesystem->dumpFile($path, $result);
                    }
                },
            );
        }
    }

    #[Override]
    public function getProcessedHelp(): string {
        return strtr(parent::getProcessedHelp(), [
            '%description%'  => $this->getProcessedHelpDescription(),
            '%instructions%' => $this->getProcessedHelpInstructions(),
        ]);
    }

    protected function getProcessedHelpDescription(): string {
        return $this->getDocBlock(new ReflectionClass(Preprocessor::class));
    }

    protected function getProcessedHelpInstructions(): string {
        $instructions = $this->preprocessor->getInstructions();
        $help         = [];

        foreach ($instructions as $instruction) {
            $class      = new ReflectionClass($instruction);
            $name       = $instruction::getName();
            $desc       = $this->getDocBlock($class);
            $resolver   = $this->getProcessedHelpInstructionResolver($instruction, 2);
            $resolver   = trim($resolver ?: '_No description provided_.');
            $parameters = $this->getProcessedHelpInstructionParameters($instruction, 2);

            if ($parameters !== null) {
                $help[$name] = rtrim(
                    <<<HELP
                    ### `[{$name}]: <target> <parameters>`

                    * `<target>` - {$resolver}
                    * `<parameters>` - additional parameters
                    {$parameters}

                    {$desc}
                    HELP,
                );
            } else {
                $help[$name] = rtrim(
                    <<<HELP
                    ### `[{$name}]: <target>`

                    * `<target>` - {$resolver}

                    {$desc}
                    HELP,
                );
            }
        }

        ksort($help);

        return implode("\n\n", $help);
    }

    /**
     * @param class-string<Instruction<covariant mixed, covariant ?object>> $instruction
     * @param int<0, max>                                                   $padding
     */
    protected function getProcessedHelpInstructionResolver(string $instruction, int $padding): string {
        $class = new ReflectionClass($instruction::getResolver());
        $help  = $this->getDocBlock($class, $padding);
        $help  = rtrim($help);

        return $help;
    }

    /**
     * @param class-string<Instruction<covariant mixed, covariant ?object>> $instruction
     * @param int<0, max>                                                   $padding
     */
    protected function getProcessedHelpInstructionParameters(string $instruction, int $padding): ?string {
        // Has?
        $class = $instruction::getParameters();

        if ($class === null) {
            return null;
        } elseif (!is_a($class, Serializable::class, true)) {
            return ''; // not yet supported...
        } else {
            // empty
        }

        // Extract
        $class      = new ReflectionClass($class);
        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);
        $parameters = [];

        foreach ($properties as $property) {
            // Ignored?
            if (!$property->isPublic() || $property->isStatic()) {
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

        // Serialize
        $list = '';

        foreach ($parameters as $definition => $description) {
            $list .= "* `{$definition}` - {$description}\n";
        }

        $list = Markdown::setPadding($list, $padding);
        $list = rtrim($list);

        // Return
        return $list;
    }

    /**
     * @param ReflectionClass<object>|ReflectionProperty $object
     * @param int<0, max>                                $padding
     */
    private function getDocBlock(ReflectionClass|ReflectionProperty $object, int $padding = 0): string {
        $doc  = new PhpDoc($object->getDocComment() ?: null);
        $help = $doc->getText();
        $help = Markdown::setPadding($help, $padding);
        $help = rtrim($help);

        return $help;
    }
}
