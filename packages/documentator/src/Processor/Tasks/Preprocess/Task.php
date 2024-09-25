<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use Exception;
use Generator;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Changeset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Generated\Block as GeneratedNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils as MarkdownUtils;
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
 * * `<parameters>` optional JSON string with additional parameters
 *   (can be wrapped by `(...)`, `"..."`, or `'...'`)
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

        if (!$document) {
            return false;
        }

        // Process
        $parsed  = $this->parse($root, $file, $document);
        $changes = [];

        foreach ($parsed->tokens as $hash => $token) {
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
                    $location = MarkdownUtils::getLocation($next);
                } else {
                    $location = MarkdownUtils::getLocation($node);

                    if ($location) {
                        $instruction = trim((string) $document->getText($location));
                        $text        = "{$instruction}\n{$text}";
                    }
                }

                if ($location) {
                    $changes[] = [$location, $text];
                }
            }
        }

        // Mutate
        if ($changes) {
            $file->setContent(
                (string) $document->mutate(new Changeset($changes)),
            );
        }

        // Return
        return true;
    }

    protected function parse(Directory $root, File $file, Document $document): TokenList {
        // Empty?
        if ($this->instructions->isEmpty()) {
            return new TokenList();
        }

        // Extract all possible instructions
        $tokens   = [];
        $mutation = new InstructionsRemove($this->instructions);

        foreach ($document->getNode()->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            // Instruction?
            if (!Utils::isInstruction($node, $this->instructions)) {
                continue;
            }

            // Exists?
            $name        = $node->getLabel();
            $instruction = $this->instructions->get($name)[0] ?? null;

            if (!$instruction) {
                continue;
            }

            // Hash
            $target = rawurldecode($node->getDestination());
            $params = $this->getParametersJson($target, $node->getTitle());
            $hash   = Text::hash("{$name}({$params})");

            // Parsed?
            if (isset($tokens[$hash])) {
                $tokens[$hash]->nodes[] = $node;

                continue;
            }

            // Parse
            $context    = new Context($root, $file, $node->getDestination(), $node->getTitle() ?: null, $mutation);
            $parameters = $instruction::getParameters();
            $parameters = $this->serializer->deserialize($parameters, $params, 'json');

            $tokens[$hash] = new Token(
                $instruction,
                $context,
                $target,
                $parameters,
                [
                    $node,
                ],
            );
        }

        // Return
        return new TokenList($tokens);
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
