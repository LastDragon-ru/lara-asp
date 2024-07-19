<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use Exception;
use Generator;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task as TaskContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\PreprocessFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Instruction as IncludeArtisan;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock\Instruction as IncludeDocBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Instruction as IncludeDocumentList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Instruction as IncludeExample;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExec\Instruction as IncludeExec;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeFile\Instruction as IncludeFile;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeGraphqlDirective\Instruction as IncludeGraphqlDirective;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Instruction as IncludePackageList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Instruction as IncludeTemplate;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use Override;

use function array_map;
use function array_values;
use function hash;
use function is_array;
use function json_decode;
use function json_encode;
use function ksort;
use function mb_strlen;
use function mb_substr;
use function preg_match_all;
use function rawurldecode;
use function str_ends_with;
use function str_starts_with;
use function strtr;
use function trim;
use function uksort;

use const JSON_THROW_ON_ERROR;
use const PREG_SET_ORDER;
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
 */
class Task implements TaskContract {
    protected const Warning = 'Generated automatically. Do not edit.';
    protected const Regexp  = <<<'REGEXP'
        /^
        (?P<expression>
          \[(?P<instruction>[^\]=]+)(?:=[^]]+)?\]:\s+(?P<target>(?:[^<][^ ]+?)|(?:<[^>]+?>))
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
     * @var array<string, ResolvedInstruction<covariant Parameters>>
     */
    private array $instructions = [];

    public function __construct(
        protected readonly ContainerResolver $container,
        protected readonly Serializer $serializer,
    ) {
        $this->addInstruction(IncludeFile::class);
        $this->addInstruction(IncludeExec::class);
        $this->addInstruction(IncludeExample::class);
        $this->addInstruction(IncludeArtisan::class);
        $this->addInstruction(IncludeTemplate::class);
        $this->addInstruction(IncludeDocBlock::class);
        $this->addInstruction(IncludePackageList::class);
        $this->addInstruction(IncludeDocumentList::class);
        $this->addInstruction(IncludeGraphqlDirective::class);
    }

    /**
     * @return list<class-string<Instruction<Parameters>>>
     */
    public function getInstructions(): array {
        return array_values(array_map(static fn ($i) => $i->getClass(), $this->instructions));
    }

    /**
     * @template I of Instruction<covariant Parameters>
     *
     * @param I|class-string<I> $instruction
     */
    public function addInstruction(Instruction|string $instruction): static {
        // @phpstan-ignore-next-line argument.type (Assigment is fine...)
        $this->instructions[$instruction::getName()] = new ResolvedInstruction($this->container, $instruction);

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getExtensions(): array {
        return ['md'];
    }

    /**
     * @return Generator<mixed, Dependency<*>, mixed, bool>
     */
    #[Override]
    public function __invoke(Directory $root, File $file): Generator {
        // Process
        $parsed  = $this->parse($root, $file);
        $replace = [];
        $warning = static::Warning;

        foreach ($parsed->tokens as $hash => $token) {
            try {
                // Run
                $content = ($token->instruction)($token->context, $token->target, $token->parameters);

                if ($content instanceof Generator) {
                    yield from $content;

                    $content = $content->getReturn();
                }

                $content = trim($content);
            } catch (ProcessorError $exception) {
                throw $exception;
            } catch (Exception $exception) {
                throw new PreprocessFailed($exception);
            }

            foreach ($token->matches as $match => $expression) {
                $prefix          = <<<RESULT
                    {$expression}
                    [//]: # (start: {$hash})
                    [//]: # (warning: {$warning})
                    RESULT;
                $suffix          = <<<RESULT
                    [//]: # (end: {$hash})
                    RESULT;
                $replace[$match] = match (true) {
                    $content !== '' => <<<RESULT
                        {$prefix}

                        {$content}

                        {$suffix}
                        RESULT,
                    default         => <<<RESULT
                        {$prefix}
                        [//]: # (empty)
                        {$suffix}
                        RESULT,
                };
            }
        }

        // Sort
        uksort($replace, static function (string $a, string $b): int {
            return mb_strlen($b) <=> mb_strlen($a);
        });

        // Replace
        $file->setContent(strtr($file->getContent(), $replace));

        return true;
    }

    protected function parse(Directory $root, File $file): TokenList {
        // Extract all possible instructions
        $tokens  = [];
        $matches = [];

        if (!preg_match_all(static::Regexp, $file->getContent(), $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL)) {
            return new TokenList($tokens);
        }

        // Parse each of them
        foreach ($matches as $match) {
            // Instruction?
            $name        = (string) $match['instruction'];
            $instruction = $this->instructions[$name] ?? null;

            if (!$instruction) {
                continue;
            }

            // Hash
            $target = trim((string) $match['target']);
            $target = str_starts_with($target, '<') && str_ends_with($target, '>')
                ? mb_substr($target, 1, -1)
                : rawurldecode($target);
            $params = $this->getParametersJson($target, $match['parameters']);
            $hash   = $this->getHash("{$name}({$params})");

            // Parsed?
            if (isset($tokens[$hash])) {
                $tokens[$hash]->matches[$match[0]] = (string) $match['expression'];

                continue;
            }

            // Parse
            $context    = new Context($root, $file, (string) $match['target'], $match['parameters']);
            $parameters = $instruction->getClass()::getParameters();
            $parameters = $this->serializer->deserialize($parameters, $params, 'json');

            $tokens[$hash] = new Token(
                $instruction->getInstance(),
                $context,
                $target,
                $parameters,
                [
                    $match[0] => (string) $match['expression'],
                ],
            );
        }

        // Return
        return new TokenList($tokens);
    }

    protected function getHash(string $identifier): string {
        return hash('sha256', $identifier);
    }

    private function getParametersJson(string $target, ?string $json): string {
        $parameters           = (array) ($json ? json_decode($json, true, flags: JSON_THROW_ON_ERROR) : []);
        $parameters['target'] = $target;
        $parameters           = $this->getParametersJsonNormalize($parameters);
        $parameters           = json_encode($parameters, JSON_THROW_ON_ERROR);

        return $parameters;
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
