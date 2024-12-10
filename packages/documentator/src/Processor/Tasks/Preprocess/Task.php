<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use Exception;
use Generator;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Node as GeneratedNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Changeset;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task as TaskContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\InstanceList;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\PreprocessFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Mutations\InstructionsRemove;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use League\CommonMark\Node\NodeIterator;
use Override;
use function is_array;
use function json_decode;
use function json_encode;
use function ksort;
use function rawurldecode;
use function trim;
use const JSON_THROW_ON_ERROR;

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
 *
 * * `<instruction>` the instruction name (unknown instructions will be ignored)
 * * `<target>` usually the path to the file or directory, but see the instruction description
 * * `<parameters>` optional JSON string with additional parameters (can be
 *    wrapped by `(...)`, `"..."`, or `'...'`). The [Serializer](../../../../../serializer/README.md)
 *    package is used for deserialization.
 *
 * ## Limitations
 *
 * * Nested `<instruction>` not supported.
 */
class Task implements TaskContract {
    protected const BlockMarker = 'preprocess';

    /**
     * @var InstanceList<Instruction<Parameters>>
     */
    private InstanceList $instructions;

    public function __construct(
        ContainerResolver $container,
        protected readonly Serializer $serializer,
        protected readonly Markdown $markdown,
    ) {
        $this->instructions = new InstanceList($container, $this->key(...));
    }

    /**
     * @param Instruction<Parameters>|class-string<Instruction<Parameters>> $task
     */
    private function key(Instruction|string $task): string {
        return $task::getName();
    }

    /**
     * @return list<class-string<Instruction<Parameters>>>
     */
    public function getInstructions(): array {
        return $this->instructions->classes();
    }

    /**
     * @template I of Instruction<*>
     *
     * @param I|class-string<I> $instruction
     */
    public function addInstruction(Instruction|string $instruction): static {
        $this->instructions->add($instruction); // @phpstan-ignore argument.type

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['md'];
    }

    /**
     * @return Generator<mixed, Dependency<*>, mixed, bool>
     */
    #[Override]
    public function __invoke(Directory $root, File $file): Generator {
        // Just in case
        yield from [];

        // Markdown?
        $document = $file->getMetadata($this->markdown);

        if ($document === null) {
            return false;
        }

        // Process
        $parsed  = $this->parse($root, $file, $document);
        $mutated = false;

        foreach ($parsed as $group) {
            // Run
            $changes = [];

            foreach ($group as $hash => $token) {
                // Run
                try {
                    // Run
                    $content = ($token->instruction)($token->context, $token->target, $token->parameters);

                    if ($content instanceof Generator) {
                        yield from $content;

                        $content = $content->getReturn();
                    }

                    // Markdown?
                    if ($content instanceof Document) {
                        $content = (string) $token->context->toInlinable($content);
                    }
                } catch (ProcessorError $exception) {
                    throw $exception;
                } catch (Exception $exception) {
                    throw new PreprocessFailed($exception);
                }

                // Wrap
                $content = GeneratedNode::get(static::BlockMarker.'/'.$hash, $content);

                // Replace
                foreach ($token->nodes as $node) {
                    $location = null;
                    $text     = "{$content}\n";
                    $next     = $node->next();

                    if ($next instanceof GeneratedNode) {
                        $location = Location::get($next);
                    } else {
                        $location    = Location::get($node);
                        $instruction = trim((string) $document->getText($location));
                        $text        = "{$instruction}\n{$text}";
                    }

                    $changes[] = [$location, $text];
                }
            }

            // Mutate
            if ($changes !== []) {
                $document = $document->mutate(new Changeset($changes));
                $mutated  = true;
            }
        }

        // Mutate
        if ($mutated) {
            $file->setContent((string) $document);
        }

        // Return
        return true;
    }

    /**
     * @return array<int, array<string, Token<*>>>
     */
    protected function parse(Directory $root, File $file, Document $document): array {
        // Empty?
        if ($this->instructions->isEmpty()) {
            return [];
        }

        // Extract all possible instructions
        $tokens   = [];
        $mutation = new InstructionsRemove($this->instructions);

        foreach ($document->node->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            // Instruction?
            if (!Utils::isInstruction($node, $this->instructions)) {
                continue;
            }

            // Exists?
            $name        = $node->getLabel();
            $instruction = $this->instructions->get($name)[0] ?? null;

            if ($instruction === null) {
                continue;
            }

            // Hash
            $priority = $instruction::getPriority() ?? 0;
            $target   = rawurldecode($node->getDestination());
            $params   = $this->getParametersJson($target, $node->getTitle());
            $hash     = Text::hash("{$name}({$params})");

            // Parsed?
            if (isset($tokens[$priority][$hash])) {
                $tokens[$priority][$hash]->nodes[] = $node;

                continue;
            }

            // Parse
            $context    = new Context($root, $file, $document, $node, $mutation);
            $parameters = $instruction::getParameters();
            $parameters = $this->serializer->deserialize($parameters, $params, 'json');

            $tokens[$priority][$hash] = new Token(
                $instruction,
                $context,
                $target,
                $parameters,
                [
                    $node,
                ],
            );
        }

        // Sort
        ksort($tokens);

        // Return
        return $tokens;
    }

    private function getParametersJson(string $target, ?string $json): string {
        $parameters           = $json !== null && $json !== ''
            ? (array) json_decode($json, true, flags: JSON_THROW_ON_ERROR)
            : [];
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
