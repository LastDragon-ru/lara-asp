<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use League\CommonMark\Node\Node;
use Throwable;

use function sprintf;

class DataMissed extends MarkdownError {
    public function __construct(
        protected readonly Node $node,
        /**
         * @var class-string<Data<*>>
         */
        protected readonly string $data,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('Data `%s` missed for `%s` node.', $this->data, $this->node::class),
            $previous,
        );
    }

    public function getNode(): Node {
        return $this->node;
    }

    /**
     * @return class-string<Data<*>>
     */
    public function getData(): string {
        return $this->data;
    }
}
