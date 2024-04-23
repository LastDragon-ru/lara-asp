<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use Illuminate\Console\Command;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ParameterizableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Preprocessor;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Override;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function array_map;
use function explode;
use function getcwd;
use function gettype;
use function implode;
use function is_a;
use function is_scalar;
use function ksort;
use function rtrim;
use function str_replace;
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
        Replaces special instructions in Markdown. Instruction is the [link
        reference definition](https://github.github.com/gfm/#link-reference-definitions),
        so the syntax is:

        ```plain
        [<instruction>]: <target>
        [<instruction>]: <target> (<parameters>)
        [<instruction>=name]: <target>
        ```

        Where:
        * `<instruction>` the instruction name (unknown instructions will be ignored)
        * `<target>` usually the path to the file or directory, but see the instruction description
        * `<parameters>` optional JSON string with additional parameters
            (can be wrapped by `(...)`, `"..."`, or `'...'`)

        ## Instructions

        %instructions%

        ## Limitations

        * `<instruction>` will be processed everywhere in the file (eg within
          the code block) and may give unpredictable results.
        * `<instruction>` cannot be inside text.
        * Nested `<instruction>` doesn't support.
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
            '%instructions%' => $this->getInstructionsHelp(),
        ]);
    }

    protected function getInstructionsHelp(): string {
        $instructions = $this->preprocessor->getInstructions();
        $help         = [];

        foreach ($instructions as $instruction) {
            $name   = $instruction::getName();
            $desc   = $instruction::getDescription();
            $target = $instruction::getTargetDescription();
            $params = is_a($instruction, ParameterizableInstruction::class, true)
                ? $this->getInstructionParameters($instruction)
                : null;

            if ($target !== null && $params !== null) {
                $parameters = [];

                foreach ($params as $paramName => $paramDescription) {
                    $paramName        = trim($paramName);
                    $paramDescription = trim($paramDescription);
                    $parameters[]     = "`{$paramName}` - {$paramDescription}";
                }

                $prefix      = '  * ';
                $parameters  = $prefix.implode("\n{$prefix}", $parameters);
                $help[$name] = <<<HELP
                    ### `[{$name}]: <target> <parameters>`

                    * `<target>` - {$target}
                    * `<parameters>` - additional parameters
                    {$parameters}

                    {$desc}
                    HELP;
            } elseif ($target !== null) {
                $help[$name] = <<<HELP
                    ### `[{$name}]: <target>`

                    * `<target>` - {$target}

                    {$desc}
                    HELP;
            } else {
                $help[$name] = <<<HELP
                    ### `[{$name}]: .`

                    {$desc}
                    HELP;
            }
        }

        ksort($help);

        return implode("\n\n", $help);
    }

    /**
     * @template T of Serializable
     *
     * @param class-string<ParameterizableInstruction<T>> $instruction
     *
     * @return array<string, string>
     */
    protected function getInstructionParameters(string $instruction): array {
        // Explicit? (deprecated)
        $parameters = $instruction::getParametersDescription();

        if ($parameters) {
            return $parameters;
        }

        // Nope
        $class      = new ReflectionClass($instruction::getParameters());
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

            // Description
            $doc         = new PhpDoc($property->getDocComment() ?: null);
            $description = $doc->getSummary() ?: '_No description provided_.';
            $description = trim(
                implode(' ', array_map(rtrim(...), explode("\n", str_replace("\r\n", "\n", $description)))),
            );

            // Add
            $parameters[$definition] = $description;
        }

        return $parameters;
    }
}
