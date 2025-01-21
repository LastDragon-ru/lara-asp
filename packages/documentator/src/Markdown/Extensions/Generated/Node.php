<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Locatable;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\EndMarkerLocation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\StartMarkerLocation;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document;
use Override;

use function mb_trim;
use function str_contains;

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
class Node extends AbstractBlock implements Locatable {
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
        $content = mb_trim($content);
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

    #[Override]
    public function locate(Document $document, Location $location): void {
        // Lines
        $lines = Lines::get($document);

        if ($lines === []) {
            return;
        }

        // Start
        $start = $this->getStartMarkerLocation($location, $lines);

        StartMarkerLocation::set($this, $start);

        // End
        $end = $this->getEndMarkerLocation($location, $lines);

        if ($end !== null) {
            EndMarkerLocation::set($this, $end);
        }
    }

    /**
     * @param array<int, string> $lines
     */
    private function getStartMarkerLocation(Location $location, array $lines): Location {
        $startLine = $location->startLine;
        $endLine   = $startLine;
        $index     = $startLine + 1;

        if (str_contains($lines[$index] ?? '', '[//]: # (warning:')) {
            $endLine++;
            $index++;
        }

        if (($lines[$index] ?? '') === '') {
            $endLine++;
        }

        return new Location($startLine, $endLine, 0, null, $location->startLinePadding, $location->internalPadding);
    }

    /**
     * @param array<int, string> $lines
     */
    private function getEndMarkerLocation(Location $location, array $lines): ?Location {
        $endLocation = null;

        for ($i = 0, $c = $location->endLine - $location->startLine; $i < $c; $i++) {
            $startLine = $location->endLine - $i;
            $line      = $lines[$startLine] ?? '';

            if (str_contains($line, '[//]: # (end:')) {
                $endLocation = new Location(
                    $startLine,
                    $location->endLine,
                    0,
                    null,
                    $location->startLinePadding,
                    $location->internalPadding,
                );

                break;
            } elseif ($line !== '') {
                break;
            } else {
                // empty
            }
        }

        return $endLocation;
    }
}
