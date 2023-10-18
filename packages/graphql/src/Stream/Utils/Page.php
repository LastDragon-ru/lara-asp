<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Utils;

use function floor;
use function max;

/**
 * Converts offset&limit into page&size. Please note that although we are trying
 * to return the fewest items possible, the result may contain some extra items
 * at the beginning and/or at the end. So you need to slice the result to get
 * the expected items.
 */
class Page {
    /**
     * @var int<1, max>
     */
    public readonly int $pageNumber;

    /**
     * @var int<1, max>
     */
    public readonly int $pageSize;

    /**
     * @var int<0, max>
     */
    public readonly int $start;

    /**
     * @var int<0, max>
     */
    public readonly int $end;

    /**
     * @var int<1, max>
     */
    public readonly int $length;

    /**
     * @param int<1, max> $limit
     * @param int<0, max> $offset
     */
    public function __construct(int $limit, int $offset) {
        $size = $limit - 1;

        do {
            $size += 1;
            $page  = floor($offset / $size);
            $end   = ($page + 1) * $size;
        } while ($end < $offset + $limit);

        $this->length     = $limit;
        $this->start      = max(0, (int) ($offset - $size * $page));
        $this->end        = max(0, $size - $this->length - $this->start);
        $this->pageSize   = $size;
        $this->pageNumber = max(1, (int) $page + 1);
    }
}
