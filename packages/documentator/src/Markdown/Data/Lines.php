<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Data;

use League\CommonMark\Input\MarkdownInputInterface;

use function iterator_to_array;

/**
 * @internal
 */
class Lines {
    /**
     * @var array<array-key, string>|null
     */
    private ?array $lines = null;

    public function __construct(
        private readonly MarkdownInputInterface $input,
    ) {
        // empty
    }

    /**
     * @return array<array-key, string>
     */
    public function get(): array {
        if ($this->lines === null) {
            $this->lines = iterator_to_array($this->input->getLines());
        }

        return $this->lines;
    }
}
