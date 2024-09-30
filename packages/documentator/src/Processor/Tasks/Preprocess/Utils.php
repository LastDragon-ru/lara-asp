<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Generated\Block as GeneratedNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils as MarkdownUtils;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\InstanceList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use League\CommonMark\Node\Node;

use function uniqid;

/**
 * @internal
 */
class Utils {
    /**
     * @param InstanceList<Instruction<Parameters>> $instructions
     *
     * @phpstan-assert-if-true ReferenceNode        $node
     */
    public static function isInstruction(Node $node, InstanceList $instructions): bool {
        return $node instanceof ReferenceNode
            && MarkdownUtils::getParent($node, GeneratedNode::class) === null
            && $instructions->has($node->getLabel());
    }

    public static function getSeed(Context $context, Document|File $file): string {
        $path = match (true) {
            $file instanceof Document => Path::getRelativePath($context->root->getPath(), $file->getPath() ?? ''),
            default                   => $file->getRelativePath($context->root),
        };
        $path = Text::hash($path ?: uniqid(self::class)); // @phpstan-ignore disallowed.function

        return $path;
    }
}
