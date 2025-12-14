<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Move;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Summary;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Template\Data as TemplateData;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Template\Document as TemplateDocument;
use LastDragon_ru\LaraASP\Documentator\Utils\Sorter;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Node;
use Override;

use function array_filter;
use function array_values;
use function max;
use function mb_trim;
use function min;
use function usort;

/**
 * Returns the list of `*.md` files in the `<target>` directory. Each file
 * must have `# Header` as the first construction. The first paragraph
 * after the Header will be used as a summary.
 *
 * @implements InstructionContract<Parameters>
 */
readonly class Instruction implements InstructionContract {
    public function __construct(
        protected PackageViewer $viewer,
        protected Sorter $sorter,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:document-list';
    }

    #[Override]
    public static function getPriority(): ?int {
        return null;
    }

    #[Override]
    public static function getParameters(): string {
        return Parameters::class;
    }

    #[Override]
    public function __invoke(Context $context, InstructionParameters $parameters): Document|string {
        $base      = $context->file->path;
        $include   = array_values(array_filter($parameters->include, static fn ($s) => $s !== ''));
        $exclude   = array_values(array_filter($parameters->exclude, static fn ($s) => $s !== ''));
        $iterator  = $context->resolver->search($include, $exclude, $parameters->target);
        $documents = [];

        foreach ($iterator as $path) {
            // Same?
            if ($base->equals($path)) {
                continue;
            }

            // Add
            $file     = $context->resolver->get($path);
            $document = $context->resolver->cast($file, Markdown::class);
            $move     = new Move($base->file($file->name));
            $path     = $base->relative($file->path);
            $title    = Utils::getTitle($document) ?? '';
            $summary  = mb_trim((string) $document->mutate(new Summary())->mutate($move));

            if ($path !== null && $title !== '') {
                $documents[] = new TemplateDocument($path, $title, $summary);
            }
        }

        // Empty?
        if ($documents === []) {
            return '';
        }

        // Sort
        $comparator = $this->sorter->forString($parameters->order);

        usort($documents, static function ($a, $b) use ($comparator): int {
            return $comparator($a->title, $b->title);
        });

        // Render
        $template = "document-list.{$parameters->template}";
        $level    = $this->getLevel($context->node, $parameters);
        $list     = $this->viewer->render($template, [
            'data' => new TemplateData($documents, $level),
        ]);

        // Return
        return $list;
    }

    /**
     * @return int<1,6>
     */
    private function getLevel(Node $node, Parameters $parameters): int {
        $level = match ($parameters->level) {
            0       => $this->getNodeLevel($node),
            null    => $this->getNodeLevel($node) + 1,
            default => $parameters->level,
        };
        $level = min($level, 6);
        $level = max($level, 1);

        return $level;
    }

    private function getNodeLevel(Node $block): int {
        $level = 0;

        do {
            $block = $block->previous();

            if ($block instanceof Heading) {
                $level = $block->getLevel();
                $block = null;
            }
        } while ($block !== null);

        return $level;
    }
}
