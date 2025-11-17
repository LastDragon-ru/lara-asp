<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use IteratorAggregate;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\Instances;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use Override;
use Traversable;

/**
 * @implements IteratorAggregate<int, class-string<Cast<covariant object>>>
 */
class Casts implements IteratorAggregate {
    /**
     * @var Instances<Cast<object>>
     */
    private Instances $instances;

    public function __construct(ContainerResolver $container) {
        $this->instances = new Instances($container, SortOrder::Desc);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getIterator(): Traversable {
        yield from $this->instances->getClasses();
    }

    /**
     * @return iterable<int, Cast<object>>
     */
    public function get(File $file): iterable {
        return $this->instances->get($file->getExtension(), '*');
    }

    /**
     * @template V of object
     * @template C of Cast<V>
     *
     * @param C|class-string<C> $cast
     */
    public function add(Cast|string $cast, ?int $priority = null): bool {
        $this->instances->add($cast, $cast::getExtensions(), $priority);

        return true;
    }

    /**
     * @template V of object
     * @template C of Cast<V>
     *
     * @param C|class-string<C> $cast
     */
    public function remove(Cast|string $cast): bool {
        $this->instances->remove($cast);

        return true;
    }
}
