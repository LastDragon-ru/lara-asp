<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PreprocessFailed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeCommand;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExample;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeFile;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;

use function preg_replace_callback;
use function rawurldecode;
use function sha1;
use function trim;

use const PREG_UNMATCHED_AS_NULL;

/**
 * Replaces special instructions in Markdown.
 *
 *      [<instruction>]: <target>
 *      [<instruction>=name]: <target>
 *
 * Supported instructions:
 *
 * | `<instruction>`   | `<target>`                     | Description                                              |
 * |-------------------|--------------------------------|----------------------------------------------------------|
 * | `include:file`    | path to the file               | Include content of the file as is.                       |
 * | `include:command` | the command to execute         | Execute the command and include output.                  |
 * | `include:example` | path to the example file       | Include file in the code block + its output if possible. |
 *
 * Limitations:
 * - `<instruction>` will be processed everywhere in the file (eg within the code
 *   block) and may give unpredictable results.
 * - `<instruction>` cannot be inside text.
 * - Nested `<instruction>` doesn't supported.
 *
 * @todo Use https://github.com/thephpleague/commonmark?
 */
class Preprocessor {
    protected const Warning = 'Generated automatically. Do not edit.';
    protected const Regexp  = <<<'REGEXP'
        /^
        (?P<expression>
          \[(?P<instruction>[^\]=]+)(?:=[^]]+)?\]:\s(?P<target>[^ ]+?)
        )
        (?P<content>\R
          \[\/\/\]:\s\#\s\(start:\s(?P<hash>[^)]+)\)
          .*?
          \[\/\/\]:\s\#\s\(end:\s(?P=hash)\)
        )?
        $/imsx
        REGEXP;

    /**
     * @var array<string, array{class-string<Instruction>, ?Instruction}>
     */
    private array $instructions = [];

    public function __construct() {
        $this->addInstruction(IncludeFile::class);
        $this->addInstruction(IncludeCommand::class);
        $this->addInstruction(IncludeExample::class);
        $this->addInstruction(IncludePackageList::class);
    }

    /**
     * @param Instruction|class-string<Instruction> $instruction
     */
    public function addInstruction(Instruction|string $instruction): static {
        $this->instructions[$instruction::getName()] = $instruction instanceof Instruction
            ? [$instruction::class, $instruction]
            : [$instruction, null];

        return $this;
    }

    protected function getInstruction(string $name): ?Instruction {
        if (!isset($this->instructions[$name])) {
            return null;
        }

        if (!isset($this->instructions[$name][1])) {
            $this->instructions[$name][1] = Container::getInstance()->make(
                $this->instructions[$name][0],
            );
        }

        return $this->instructions[$name][1];
    }

    public function process(string $path, string $string): string {
        $path   = Path::normalize($path);
        $cache  = [];
        $result = preg_replace_callback(
            pattern : static::Regexp,
            callback: function (array $matches) use (&$cache, $path): string {
                $hash        = $this->getHash("{$matches['instruction']}={$matches['target']}");
                $content     = $cache[$hash] ?? null;
                $instruction = $this->getInstruction($matches['instruction']);

                if ($content === null) {
                    $target       = rawurldecode($matches['target']);
                    $content      = trim($instruction?->process($path, $target) ?? '');
                    $cache[$hash] = $content;
                }

                // Return
                $warning = static::Warning;
                $prefix  = <<<RESULT
                    {$matches['expression']}
                    [//]: # (start: {$hash})
                    [//]: # (warning: {$warning})
                    RESULT;
                $suffix  = <<<RESULT
                    [//]: # (end: {$hash})
                    RESULT;

                if ($content) {
                    $content = <<<RESULT
                        {$prefix}

                        {$content}

                        {$suffix}
                        RESULT;
                } elseif ($instruction) {
                    $content = <<<RESULT
                        {$prefix}
                        [//]: # (empty)
                        {$suffix}
                        RESULT;
                } else {
                    $content = $matches['expression'];
                }

                return $content;
            },
            subject : $string,
            flags   : PREG_UNMATCHED_AS_NULL,
        );

        if ($result === null) {
            throw new PreprocessFailed('Unexpected error.');
        }

        return $result;
    }

    protected function getHash(string $identifier): string {
        return sha1($identifier);
    }
}
