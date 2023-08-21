<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PreprocessFailed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeCommand;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExample;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeFile;

use function preg_replace_callback;
use function sha1;
use function trim;
use function urldecode;

use const PREG_UNMATCHED_AS_NULL;

/**
 * Replaces special instructions in Markdown.
 *
 *      [Link](<target> "<instruction>")
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
 */
class Preprocessor {
    protected const Warning = 'Generated automatically. Do not edit.';
    protected const Regexp  = <<<'REGEXP'
        /^
        (?P<preprocess>\[[^]]*?\]\((?P<identifier>(?P<target>.+?)\s+\"(?P<instruction>[^"]+?)\")\))
        (?P<content>\<!--\sstart:(?P<hash>[\S]+)\s.*?\<!--\send:(?P=hash)\s--\>)?
        $/iumsx
    REGEXP;

    /**
     * @var array<string, array{class-string<Instruction>, ?Instruction}>
     */
    private array $instructions = [];

    /**
     * @var array<string, string>
     */
    private array $cache = [];

    public function __construct() {
        $this->addInstruction(IncludeFile::class);
        $this->addInstruction(IncludeCommand::class);
        $this->addInstruction(IncludeExample::class);
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
        $result = preg_replace_callback(
            pattern : static::Regexp,
            callback: function (array $matches) use ($path): string {
                $identifier = $matches['identifier'];
                $hash       = $this->getHash($identifier);
                $content    = $this->cache[$hash] ?? null;

                if ($content === null) {
                    $instruction        = $this->getInstruction($matches['instruction']);
                    $target             = urldecode($matches['target']);
                    $content            = trim($instruction?->process($path, $target) ?? '');
                    $this->cache[$hash] = $content;
                }

                // Return
                if ($content) {
                    $warning = static::Warning;
                    $content = <<<RESULT
                        {$matches['preprocess']}<!-- start:{$hash} {$warning} -->

                        {$content}

                        <!-- end:{$hash} -->
                        RESULT;
                } else {
                    $content = $matches['preprocess'];
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
