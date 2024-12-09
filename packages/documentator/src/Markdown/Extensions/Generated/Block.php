<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated;

use League\CommonMark\Node\Block\AbstractBlock;

use function trim;

/**
 * Represents the generated text inside document.
 *
 * ```
 * [//]: # (start: <id>)
 *
 * ... text ...
 *
 * [//]: # (end: <id>)
 * ```
 */
class Block extends AbstractBlock {
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $id,
    ) {
        parent::__construct();
    }

    public static function get(string $id, string $content): string {
        $prefix  = <<<RESULT
            [//]: # (start: {$id})
            [//]: # (warning: Generated automatically. Do not edit.)
            RESULT;
        $suffix  = <<<RESULT
            [//]: # (end: {$id})
            RESULT;
        $content = trim($content);
        $content = match (true) {
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

        return $content;
    }
}
