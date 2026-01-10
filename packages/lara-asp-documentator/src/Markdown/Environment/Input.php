<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment;

use ArrayIterator;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use League\CommonMark\Input\MarkdownInput;
use League\CommonMark\Input\MarkdownInputInterface;
use Override;

use function count;

// todo(lara-asp-documentator/Markdown): DOM and Charset detection.

/**
 * Unlike the default implementation {@see MarkdownInput} our preserve trailing
 * EOLs that is required to preserve EOL(s) when save.
 *
 * @see MarkdownInput
 *
 * @internal
 */
class Input implements MarkdownInputInterface {
    /**
     * @var array<int, string>|null
     */
    private ?array $lines = null;

    public function __construct(
        protected string $content,
    ) {
        // empty
    }

    #[Override]
    public function getContent(): string {
        return $this->content;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getLines(): iterable {
        return new ArrayIterator($this->lines());
    }

    #[Override]
    public function getLineCount(): int {
        return count($this->lines());
    }

    /**
     * @return array<int, string>
     */
    private function lines(): array {
        if ($this->lines === null) {
            $this->lines = Text::getLines("\n".$this->content);

            unset($this->lines[0]);
        }

        return $this->lines;
    }
}
