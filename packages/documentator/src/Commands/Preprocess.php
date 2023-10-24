<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Preprocessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function getcwd;
use function implode;
use function ksort;
use function strtr;

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
        [<instruction>]: <target> (<params>)
        [<instruction>=name]: <target>
        ```

        Where:
        - `<instruction>` the instruction name (unknown instructions will be ignored)
        - `<target>` usually the path to the file or directory, but see the instruction description
        - `<params>` optional JSON string with additional parameters (can be wrapped by `(...)`, `"..."`, or `'...'`)

        ### Supported instructions

        %instructions%

        ### Limitations

        * `<instruction>` will be processed everywhere in the file (eg within
          the code block) and may give unpredictable results.
        * `<instruction>` cannot be inside text.
        * Nested `<instruction>` doesn't support.
        HELP;

    public function __invoke(Filesystem $filesystem, Preprocessor $preprocessor): void {
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
                static function () use ($filesystem, $preprocessor, $file): void {
                    $path    = $file->getPathname();
                    $content = $file->getContents();
                    $result  = $preprocessor->process($path, $content);

                    if ($content !== $result) {
                        $filesystem->dumpFile($path, $result);
                    }
                },
            );
        }
    }

    public function getProcessedHelp(): string {
        $preprocessor = Container::getInstance()->make(Preprocessor::class);

        return strtr(parent::getProcessedHelp(), [
            '%instructions%' => $this->getInstructionsHelp($preprocessor),
        ]);
    }

    protected function getInstructionsHelp(Preprocessor $preprocessor): string {
        $instructions = $preprocessor->getInstructions();
        $help         = [];

        foreach ($instructions as $instruction) {
            $name   = $instruction::getName();
            $desc   = $instruction::getDescription();
            $target = $instruction::getTargetDescription();

            if ($target !== null) {
                $help[$name] = <<<HELP
                    #### `[{$name}]: <target>`

                    * `<target>` - {$target}

                    {$desc}
                    HELP;
            } else {
                $help[$name] = <<<HELP
                    #### `[{$name}]: .`

                    {$desc}
                    HELP;
            }
        }

        ksort($help);

        return implode("\n\n", $help);
    }
}
