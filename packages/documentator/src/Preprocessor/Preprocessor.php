<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use Exception;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Documentator\Commands\Preprocess;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ParameterizableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ProcessableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PreprocessFailed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocumentList;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExample;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExec;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeFile;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;

use function array_column;
use function hash;
use function is_array;
use function json_decode;
use function json_encode;
use function ksort;
use function mb_substr;
use function preg_replace_callback;
use function rawurldecode;
use function str_ends_with;
use function str_starts_with;
use function trim;

use const JSON_THROW_ON_ERROR;
use const PREG_UNMATCHED_AS_NULL;

/**
 * Replaces special instructions in Markdown. Instruction is the link  reference
 * definition, so the syntax is:
 *
 *      [<instruction>]: <target>
 *      [<instruction>]: <target> (<params>)
 *      [<instruction>=name]: <target>
 *
 * Where:
 *  - `<instruction>` the instruction name (unknown instructions will be ignored)
 *  - `<target>` usually the path to the file or directory, but see the
 *      instruction description
 *  - `<params>` optional JSON string with additional parameters (can be wrapped
 *      by `(...)`, `"..."`, or `'...'`)
 *
 * Limitations:
 * - `<instruction>` will be processed everywhere in the file (eg within the code
 *   block) and may give unpredictable results.
 * - `<instruction>` cannot be inside text.
 * - Nested `<instruction>` doesn't supported.
 *
 * @todo Use https://github.com/thephpleague/commonmark?
 * @todo Sync with {@see Preprocess} command
 *
 * @see Preprocess
 */
class Preprocessor {
    protected const Warning = 'Generated automatically. Do not edit.';
    protected const Regexp  = <<<'REGEXP'
        /^
        (?P<expression>
          \[(?P<instruction>[^\]=]+)(?:=[^]]+)?\]:\s(?P<target>(?:[^ ]+?)|(?:<[^>]+?>))
          (?P<pBlock>\s(?:\(|(?P<pStart>['"]))(?P<parameters>.+?)(?:\)|(?P=pStart)))?
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

    public function __construct(
        protected readonly Serializer $serializer,
    ) {
        $this->addInstruction(IncludeFile::class);
        $this->addInstruction(IncludeExec::class);
        $this->addInstruction(IncludeExample::class);
        $this->addInstruction(IncludePackageList::class);
        $this->addInstruction(IncludeDocumentList::class);
    }

    /**
     * @return list<class-string<Instruction>>
     */
    public function getInstructions(): array {
        return array_column($this->instructions, 0);
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
        $result = null;

        try {
            $result = preg_replace_callback(
                pattern : static::Regexp,
                callback: function (array $matches) use (&$cache, $path): string {
                    // Hash
                    $instruction = $this->getInstruction($matches['instruction']);
                    $target      = $matches['target'];
                    $target      = str_starts_with($target, '<') && str_ends_with($target, '>')
                        ? mb_substr($target, 1, -1)
                        : rawurldecode($target);
                    $params      = null;
                    $hash        = $this->getHash("{$matches['instruction']}({$target})");

                    if ($instruction instanceof ParameterizableInstruction) {
                        $json   = $this->getParametersJson($matches['parameters'] ?: '{}');
                        $hash   = $this->getHash("{$matches['instruction']}({$target}, {$json})");
                        $params = $this->serializer->deserialize(
                            $instruction::getParameters(),
                            $matches['parameters'] ?: '{}',
                            'json',
                        );
                    }

                    // Content
                    $content = $cache[$hash] ?? null;

                    if ($content === null) {
                        if ($instruction instanceof ParameterizableInstruction) {
                            $content = $instruction->process($path, $target, $params);
                        } elseif ($instruction instanceof ProcessableInstruction) {
                            $content = $instruction->process($path, $target);
                        } else {
                            $content = '';
                        }

                        $content      = trim($content);
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
        } catch (PreprocessFailed $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new PreprocessFailed('Preprocess failed.', $exception);
        }

        if ($result === null) {
            throw new PreprocessFailed('Unexpected error.');
        }

        return $result;
    }

    protected function getHash(string $identifier): string {
        return hash('sha256', $identifier);
    }

    private function getParametersJson(string $json): string {
        return json_encode(
            $this->getParametersJsonNormalize(json_decode($json, true, flags: JSON_THROW_ON_ERROR)),
            JSON_THROW_ON_ERROR,
        );
    }

    private function getParametersJsonNormalize(mixed $value): mixed {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->getParametersJsonNormalize($v);
            }

            ksort($value);
        }

        return $value;
    }
}
