<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList;

use Generator;
use Iterator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Move;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Summary;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Title;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link\Unlink;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileIterator;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Template\Data as TemplateData;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Template\Document as TemplateDocument;
use LastDragon_ru\LaraASP\Documentator\Utils\Sorter;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Node;
use Override;

use function array_filter;
use function max;
use function mb_trim;
use function min;
use function str_replace;
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

    /**
     * @return Generator<mixed, Dependency<*>, mixed, string>
     */
    #[Override]
    public function __invoke(Context $context, InstructionParameters $parameters): Generator {
        $target    = $context->file->getDirectoryPath($parameters->target);
        $patterns  = array_filter((array) $parameters->include, static fn ($s) => $s !== '');
        $patterns  = $patterns === [] ? '*.md' : $patterns;
        $iterator  = Cast::to(Iterator::class, yield new FileIterator($target, $patterns, $parameters->depth));
        $documents = [];

        foreach ($iterator as $file) {
            // Prepare
            $file = Cast::to(File::class, $file);

            // Same?
            if ($context->file->isEqual($file)) {
                continue;
            }

            // Empty?
            $document = $file->as(Document::class);

            if ($document->isEmpty()) {
                continue;
            }

            // Add
            $move        = new Move($context->file->getFilePath($file->getName()));
            $title       = mb_trim((string) $document->mutate(new Title(), new Unlink()));
            $title       = mb_trim(str_replace("\n", ' ', Utils::getHeadingText($title)));
            $title       = $title === '' ? Text::getPathTitle($file->getName()) : $title;
            $summary     = mb_trim((string) $document->mutate(new Summary())->mutate($move));
            $documents[] = new TemplateDocument(
                $context->file->getRelativePath($file),
                $title,
                $summary,
            );
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
