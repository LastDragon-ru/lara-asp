<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use Exception;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Node as GeneratedNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Changeset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Cleanup;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\MakeInlinable;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Move;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Generated\Unwrap;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Text as TextMutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\DependencyResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileSave;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\PreprocessError;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\PreprocessFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Instruction as IncludeArtisan;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock\Instruction as IncludeDocBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Instruction as IncludeDocumentList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Instruction as IncludeExample;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExec\Instruction as IncludeExec;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeFile\Instruction as IncludeFile;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeGraphqlDirective\Instruction as IncludeGraphqlDirective;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Instruction as IncludeTemplate;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use League\CommonMark\Node\NodeIterator;
use Override;

use function is_array;
use function json_decode;
use function json_encode;
use function ksort;
use function mb_trim;
use function rawurldecode;

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
class Task implements FileTask {
    protected const string BlockMarker = 'preprocess';

    private readonly Instructions $instructions;

    public function __construct(
        ContainerResolver $container,
        protected readonly Serializer $serializer,
    ) {
        $this->instructions = new Instructions($container);

        $this->addInstruction(IncludeFile::class);
        $this->addInstruction(IncludeExec::class);
        $this->addInstruction(IncludeExample::class);
        $this->addInstruction(IncludeArtisan::class);
        $this->addInstruction(IncludeTemplate::class);
        $this->addInstruction(IncludeDocBlock::class);
        $this->addInstruction(IncludeDocumentList::class);
        $this->addInstruction(IncludeGraphqlDirective::class);
    }

    /**
     * @internal
     *
     * @return list<class-string<Instruction<Parameters>>>
     */
    public function getInstructions(): array {
        return $this->instructions->classes();
    }

    /**
     * The last added instructions have a bigger priority.
     *
     * @template P of Parameters
     * @template I of Instruction<P>
     *
     * @param I|class-string<I> $instruction
     */
    public function addInstruction(Instruction|string $instruction): static {
        $this->instructions->add($instruction, [$instruction::getName()]);

        return $this;
    }

    /**
     * @template P of Parameters
     * @template I of Instruction<P>
     *
     * @param I|class-string<I> $instruction
     */
    public function removeInstruction(Instruction|string $instruction): static {
        $this->instructions->remove($instruction);

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function glob(): array|string {
        return '*.md';
    }

    #[Override]
    public function __invoke(DependencyResolver $resolver, File $file): void {
        // Process
        $document = $file->as(Document::class);
        $parsed   = $this->parse($resolver, $file, $document);
        $mutated  = false;

        foreach ($parsed as $group) {
            // Run
            $changes = [];

            foreach ($group as $hash => $token) {
                // Run
                try {
                    // Run
                    $content = ($token->instruction)($token->context, $token->parameters);

                    // Markdown?
                    if ($content instanceof Document) {
                        $content = (string) $content
                            ->mutate(
                                new MakeInlinable(Utils::getSeed($token->context, $content)),
                                new Unwrap(),
                            )
                            ->mutate(
                                new Cleanup(),
                                new Move($file->path),
                            );
                    }
                } catch (PreprocessError $exception) {
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
                        $instruction = mb_trim((string) $document->mutate(new TextMutation($location)));
                        $text        = "{$instruction}\n{$text}";
                    }

                    $changes[] = new Replace($location, $text);
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
            $resolver->resolve(new FileSave($file, $document));
        }
    }

    /**
     * @return array<int, array<string, Token<*>>>
     */
    protected function parse(DependencyResolver $resolver, File $file, Document $document): array {
        // Empty?
        if (!$this->instructions->has()) {
            return [];
        }

        // Extract all possible instructions
        $tokens = [];

        foreach ($document->node->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            // Instruction?
            if (!($node instanceof ReferenceNode) || !Utils::isInstruction($node, $this->instructions)) {
                continue;
            }

            // Exists?
            $name        = $node->getLabel();
            $instruction = $this->instructions->first($name);

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
            $context    = new Context($resolver, $file, $document, $node);
            $parameters = $instruction::getParameters();
            $parameters = $this->serializer->deserialize($parameters, $params, 'json');

            $tokens[$priority][$hash] = new Token(
                $instruction,
                $context,
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
