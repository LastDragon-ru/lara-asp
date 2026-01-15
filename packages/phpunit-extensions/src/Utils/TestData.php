<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Utils;

use InvalidArgumentException;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use WeakMap;

use function debug_backtrace;
use function file_get_contents;
use function is_a;
use function is_object;
use function sprintf;

use const DEBUG_BACKTRACE_IGNORE_ARGS;
use const DEBUG_BACKTRACE_PROVIDE_OBJECT;

/**
 * Small helper to load data associated with test.
 */
class TestData {
    /**
     * @var WeakMap<TestCase, static>
     */
    private static WeakMap $map;
    private string         $name;
    private DirectoryPath  $path;

    /**
     * @param TestCase|class-string<TestCase> $class
     */
    final protected function __construct(TestCase|string $class) {
        // Class
        $class = new ReflectionClass($class);

        if ($class->isAnonymous()) {
            throw new InvalidArgumentException('Anonymous classes are not supported.');
        }

        // Path
        $path = $class->getFileName();

        if ($path === false) {
            throw new InvalidArgumentException('Internal PHP classes are not supported.');
        }

        // Properties
        $this->name = $class->getShortName();
        $this->path = (new FilePath($path))->directory($this->name);
    }

    public static function get(): static {
        // Search for TestCase
        $class = null;
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        foreach ($trace as $item) {
            if (isset($item['object']) && $item['object'] instanceof TestCase) {
                $class = $item['object'];
                break;
            } elseif (isset($item['class']) && is_a($item['class'], TestCase::class, true)) {
                $class = $item['class'];
                break;
            } else {
                // empty
            }
        }

        if ($class === null) {
            throw new LogicException(sprintf('Method can be called only inside `%s` instance.', TestCase::class));
        }

        // Initialize
        if (!isset(self::$map)) {
            self::$map = new WeakMap();
        }

        // Create
        $instance = null;

        if (is_object($class)) {
            self::$map[$class] ??= new static($class);
            $instance            = self::$map[$class];
        } else {
            $instance = new static($class);
        }

        return $instance;
    }

    /**
     * @param non-empty-string $path
     */
    public function file(string $path): FilePath {
        return $this->resolve(new FilePath($path));
    }

    public function directory(string $path = ''): DirectoryPath {
        return $this->resolve(new DirectoryPath($path));
    }

    /**
     * @param non-empty-string $path
     */
    public function content(string $path): string {
        return (string) file_get_contents($this->file($path)->path);
    }

    /**
     * @template T of DirectoryPath|FilePath
     *
     * @param T $path
     *
     * @return new<T>
     */
    private function resolve(DirectoryPath|FilePath $path): DirectoryPath|FilePath {
        $path = $this->path->resolve($path);

        if (!$this->path->contains($path) && !$this->path->equals($path)) {
            throw new InvalidArgumentException(sprintf('Path `%s` must be inside `%s`.', $path, $this->path));
        }

        return $path;
    }
}
