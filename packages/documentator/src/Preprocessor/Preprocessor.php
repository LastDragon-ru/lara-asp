<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

// @phpcs:disable Generic.Files.LineLength.TooLong

use Exception;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Commands\Preprocess;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PreprocessFailed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocBlock\Instruction as IncludeDocBlock;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocumentList\Instruction as IncludeDocumentList;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExample\Instruction as IncludeExample;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExec\Instruction as IncludeExec;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeFile\Instruction as IncludeFile;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeGraphqlDirective\Instruction as IncludeGraphqlDirective;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList\Instruction as IncludePackageList;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeTemplate\Instruction as IncludeTemplate;
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
 * Replaces special instructions in Markdown. Instruction is the [link
 * reference definition](https://github.github.com/gfm/#link-reference-definitions),
 * so the syntax is:
 *
 * ```plain
 * [<instruction>]: <target>
 * [<instruction>]: <target> (<parameters>)
 * [<instruction>=name]: <target>
 * [<instruction>=name]: <target> (<parameters>)
 * ```
 *
 * Where:
 * * `<instruction>` the instruction name (unknown instructions will be ignored)
 * * `<target>` usually the path to the file or directory, but see the instruction description
 * * `<parameters>` optional JSON string with additional parameters
 *   (can be wrapped by `(...)`, `"..."`, or `'...'`)
 *
 * ## Limitations
 *
 * * `<instruction>` will be processed everywhere in the file (eg within
 *   the code block) and may give unpredictable results.
 * * `<instruction>` cannot be inside text.
 * * Nested `<instruction>` doesn't support.
 *
 * @todo Use https://github.com/thephpleague/commonmark?
 *
 * @see  Preprocess
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
        $/imsxu
        REGEXP;

    /**
     * @var array<string, array{class-string<Instruction<mixed, ?object>>,?Instruction<mixed, ?object>}>
     */
    private array $instructions = [];

    public function __construct(
        protected readonly ContainerResolver $container,
        protected readonly Serializer $serializer,
    ) {
        $this->addInstruction(IncludeFile::class);
        $this->addInstruction(IncludeExec::class);
        $this->addInstruction(IncludeExample::class);
        $this->addInstruction(IncludeTemplate::class);
        $this->addInstruction(IncludeDocBlock::class);
        $this->addInstruction(IncludePackageList::class);
        $this->addInstruction(IncludeDocumentList::class);
        $this->addInstruction(IncludeGraphqlDirective::class);
    }

    /**
     * @return list<class-string<Instruction<mixed, ?object>>>
     */
    public function getInstructions(): array {
        return array_column($this->instructions, 0);
    }

    /**
     * @template I of Instruction<covariant mixed, covariant ?object>
     *
     * @param I|class-string<I> $instruction
     */
    public function addInstruction(Instruction|string $instruction): static {
        // @phpstan-ignore-next-line Assigment is fine...
        $this->instructions[$instruction::getName()] = $instruction instanceof Instruction
            ? [$instruction::class, $instruction]
            : [$instruction, null];

        return $this;
    }

    /**
     * @return Instruction<mixed, ?object>|null
     */
    protected function getInstruction(string $name): ?Instruction {
        if (!isset($this->instructions[$name])) {
            return null;
        }

        if (!isset($this->instructions[$name][1])) {
            $this->instructions[$name][1] = $this->container->getInstance()->make(
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
                    // Instruction?
                    $instruction = $this->getInstruction($matches['instruction']);

                    if (!$instruction) {
                        return $matches['expression'];
                    }

                    // Hash
                    $target = $matches['target'];
                    $target = str_starts_with($target, '<') && str_ends_with($target, '>')
                        ? mb_substr($target, 1, -1)
                        : rawurldecode($target);
                    $json   = $this->getParametersJson($matches['parameters'] ?: '{}');
                    $hash   = $this->getHash("{$matches['instruction']}({$target}, {$json})");

                    // Content
                    $content = $cache[$hash] ?? null;

                    if ($content === null) {
                        $params       = $instruction::getParameters();
                        $params       = $params ? $this->serializer->deserialize($params, $json, 'json') : null;
                        $context      = new Context($path, $target, $matches['parameters']);
                        $resolver     = $this->container->getInstance()->make($instruction::getResolver());
                        $resolved     = $resolver->resolve($context, $params);
                        $content      = $instruction->process($context, $resolved, $params);
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
                    } else {
                        $content = <<<RESULT
                        {$prefix}
                        [//]: # (empty)
                        {$suffix}
                        RESULT;
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
