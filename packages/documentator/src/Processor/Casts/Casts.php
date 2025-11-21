<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use IteratorAggregate;
use LastDragon_ru\GlobMatcher\GlobMatcher;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\Instances;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use Override;
use Traversable;
use WeakMap;

use function array_diff_uassoc;
use function array_keys;

/**
 * @implements IteratorAggregate<int, class-string<Cast<covariant object>>>
 */
class Casts implements IteratorAggregate {
    /**
     * @var Instances<Cast<object>>
     */
    private Instances $instances;

    /**
     * @var array<non-empty-string, GlobMatcher>
     */
    private array $globs = [];

    /**
     * @var WeakMap<File, list<non-empty-string>>
     */
    private WeakMap $tags;

    public function __construct(ContainerResolver $container) {
        $this->tags      = new WeakMap();
        $this->instances = new Instances($container, SortOrder::Desc);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getIterator(): Traversable {
        yield from $this->instances->classes();
    }

    /**
     * @return iterable<int, Cast<object>>
     */
    public function get(File $file): iterable {
        return $this->instances->get(...$this->tags($file));
    }

    /**
     * @template V of object
     * @template C of Cast<V>
     *
     * @param C|class-string<C> $cast
     */
    public function add(Cast|string $cast, ?int $priority = null): void {
        $tags = [];

        foreach ((array) $cast::glob() as $tag) {
            $tags[] = $tag;

            if (!isset($this->globs[$tag])) {
                $this->globs[$tag] = new GlobMatcher($tag);
            }
        }

        $this->instances->add($cast, $tags, $priority);
    }

    /**
     * @template V of object
     * @template C of Cast<V>
     *
     * @param C|class-string<C> $cast
     */
    public function remove(Cast|string $cast): void {
        // Task
        $this->instances->remove($cast);

        // Tags
        $this->tags = new WeakMap();

        // Globs
        $tags = array_diff_uassoc(
            array_keys($this->globs),
            $this->instances->tags(),
            static function (mixed $a, mixed $b): int {
                return $a === $b ? 0 : 1;
            },
        );

        foreach ($tags as $tag) {
            unset($this->globs[$tag]);
        }
    }

    public function reset(): void {
        $this->instances->reset();
    }

    /**
     * @return list<non-empty-string>
     */
    private function tags(File $file): array {
        if (!isset($this->tags[$file])) {
            $tags = [];

            foreach ($this->globs as $tag => $matcher) {
                if ($matcher->match($file->name)) {
                    $tags[] = $tag;
                }
            }

            $this->tags[$file] = $tags;
        }

        return $this->tags[$file];
    }
}
