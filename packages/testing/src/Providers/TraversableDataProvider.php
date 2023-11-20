<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use Override;
use Traversable;

use function iterator_to_array;

/**
 * @template T
 */
class TraversableDataProvider extends BaseDataProvider {
    /**
     * @var Traversable<T>
     */
    private Traversable $traversable;

    /**
     * @param Traversable<T> $traversable
     */
    public function __construct(Traversable $traversable) {
        $this->traversable = $traversable;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getData(bool $raw = false): array {
        return $this->replaceExpectedValues(iterator_to_array($this->traversable), $raw);
    }
}
