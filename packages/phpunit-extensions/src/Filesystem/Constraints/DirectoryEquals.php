<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Filesystem\Constraints;

use FilesystemIterator;
use LastDragon_ru\Path\DirectoryPath;
use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;
use SebastianBergmann\Comparator\ComparisonFailure;
use SplDoublyLinkedList;
use SplFileInfo;
use SplQueue;

use function array_keys;
use function array_merge;
use function array_unique;
use function array_values;
use function assert;
use function ksort;
use function sort;

/**
 * Compares two directories. By default, directories are equal if the list of
 * their children is the same, and files have the same content. Permissions are
 * ignored. You can override {@see self::properties()} and {@see self::equal()}
 * to customize comparison logic.
 */
class DirectoryEquals extends Constraint {
    /**
     * @var ?array{
     *      array<string, list<?array<non-empty-string, scalar|null>>>,
     *      array<string, list<?array<non-empty-string, scalar|null>>>,
     *      }
     */
    private ?array $difference = null;

    public function __construct(
        protected readonly DirectoryPath $expected,
    ) {
        // empty
    }

    #[Override]
    public function toString(): string {
        return 'equals to directory '.Exporter::export($this->expected->path);
    }

    #[Override]
    protected function fail(mixed $other, string $description, ?ComparisonFailure $comparisonFailure = null): never {
        if ($this->difference !== null && $comparisonFailure === null) {
            $comparisonFailure = new ComparisonFailure(
                $this->difference[0],
                $this->difference[1],
                Exporter::export($this->difference[0]),
                Exporter::export($this->difference[1]),
            );
        }

        parent::fail($other, $description, $comparisonFailure);
    }

    #[Override]
    protected function failureDescription(mixed $other): string {
        return match (true) {
            $other instanceof DirectoryPath => 'directory '.Exporter::export($other->path).' '.$this->toString(),
            default                         => parent::failureDescription($other),
        };
    }

    #[Override]
    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): ?bool {
        $this->difference = null;

        try {
            return parent::evaluate($other, $description, $returnResult);
        } finally {
            $this->difference = null;
        }
    }

    #[Override]
    protected function matches(mixed $other): bool {
        // Directory?
        if (!($other instanceof DirectoryPath)) {
            return false;
        }

        // Same?
        if ($this->expected->equals($other)) {
            return true;
        }

        // Compare
        /** @var SplQueue<DirectoryPath> $queue */
        $queue = new SplQueue();

        $queue->setIteratorMode(SplDoublyLinkedList::IT_MODE_DELETE);
        $queue->push(new DirectoryPath('./'));

        foreach ($queue as $path) {
            // First, we are comparing the lists of children (quick)
            [$otherObjects, $otherProperties]       = $this->listing($other->resolve($path));
            [$expectedObjects, $expectedProperties] = $this->listing($this->expected->resolve($path));

            if ($expectedProperties !== $otherProperties) {
                $this->difference = $this->difference($path, $expectedProperties, $otherProperties);
                break;
            }

            // And content of expected/actual file next (full)
            foreach ($expectedObjects as $filename => $object) {
                // Directory?
                if ($object->isDir()) {
                    $queue[] = $path->directory($filename);
                    continue;
                }

                // Compare file
                if (!isset($otherObjects[$filename]) || !$this->equal($object, $otherObjects[$filename])) {
                    $this->difference = $this->difference(
                        $path,
                        [$filename => ['name' => $filename, 'content' => 'not']],
                        [$filename => ['name' => $filename, 'content' => 'equals']],
                    );

                    break 2;
                }
            }
        }

        return $this->difference === null;
    }

    /**
     * Returns properties (name, size, etc) for quick comparison.
     *
     * @return array{name: string}&array<non-empty-string, scalar|null>
     */
    protected function properties(SplFileInfo $info): array {
        return [
            'name' => $info->getFilename().($info->isDir() ? '/' : ''),
            'size' => $info->isFile() ? (int) $info->getSize() : null,
        ];
    }

    /**
     * Compares content of the files. Called only if the quick comparison
     * doesn't see the difference.
     */
    protected function equal(SplFileInfo $a, SplFileInfo $b): bool {
        $equal  = true;
        $aFile  = $a->openFile();
        $bFile  = $b->openFile();
        $buffer = 8192;

        while (!$aFile->eof() && !$bFile->eof()) {
            $aData = $aFile->fread($buffer);
            $bData = $bFile->fread($buffer);

            if ($aData === false || $bData === false || $aData !== $bData) {
                $equal = false;
                break;
            }
        }

        return $equal;
    }

    /**
     * @return array{array<string, SplFileInfo>, array<string, array<non-empty-string, scalar|null>>}
     */
    private function listing(DirectoryPath $directory): array {
        $list     = [0 => [], 1 => []];
        $iterator = new FilesystemIterator($directory->path);

        foreach ($iterator as $info) {
            assert($info instanceof SplFileInfo, 'https://github.com/phpstan/phpstan/issues/8093');

            $properties                   = $this->properties($info);
            $list[0][$properties['name']] = $info;
            $list[1][$properties['name']] = $properties;
        }

        ksort($list[1]);

        return $list;
    }

    /**
     * @param array<string, array<non-empty-string, scalar|null>> $expected
     * @param array<string, array<non-empty-string, scalar|null>> $other
     *
     * @return array{
     *     array<string, list<?array<non-empty-string, scalar|null>>>,
     *     array<string, list<?array<non-empty-string, scalar|null>>>,
     *     }
     */
    private function difference(DirectoryPath $directory, array $expected, array $other): array {
        // Only different directories/files are interested
        $expectedPresent = [];
        $otherPresent    = [];
        $keys            = array_unique(array_merge(array_keys($expected), array_keys($other)));

        sort($keys);

        foreach ($keys as $key) {
            if (isset($expected[$key]) && isset($other[$key])) {
                if ($expected[$key] !== $other[$key]) {
                    $expectedPresent[$key] = $expected[$key];
                    $otherPresent[$key]    = $other[$key];
                } else {
                    continue;
                }
            } elseif (isset($expected[$key])) {
                $expectedPresent[$key] = $expected[$key];
                $otherPresent[$key]    = null;
            } elseif (isset($other[$key])) {
                $expectedPresent[$key] = null;
                $otherPresent[$key]    = $other[$key];
            } else {
                // empty
            }
        }

        $path = $directory->normalized()->path;

        return [
            [$path => array_values($expectedPresent)],
            [$path => array_values($otherPresent)],
        ];
    }
}
