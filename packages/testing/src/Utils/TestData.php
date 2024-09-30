<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use DOMDocument;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentClass;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;

use function basename;
use function dirname;
use function ltrim;
use function sprintf;
use function str_replace;
use function str_starts_with;

/**
 * Small helper to load data associated with test.
 */
class TestData {
    private string $path;

    /**
     * @param class-string $test
     */
    public function __construct(string $test) {
        $path = (new ReflectionClass($test))->getFileName();

        if ($path === false) {
            throw new InvalidArgumentClass('$test', $test);
        }

        $this->path = $path;
    }

    public function path(string $path): string {
        $dir  = dirname(str_replace('\\', '/', $this->path));
        $name = basename($this->path, '.php');
        $path = str_starts_with($path, '.') || str_starts_with($path, '~') ? $path : '/'.ltrim($path, '/');
        $path = "{$dir}/{$name}{$path}";

        return $path;
    }

    public function file(string $path): SplFileInfo {
        return new SplFileInfo($this->path($path));
    }

    public function content(string $path): string {
        return Args::content($this->file($path));
    }

    /**
     * @return array<array-key, mixed>|string|int|float|bool|null
     */
    public function json(string $path = '.json'): array|string|int|float|bool|null {
        return Args::getJson($this->file($path), true);
    }

    public function dom(string $path = '.xml'): DOMDocument {
        $dom  = new DOMDocument();
        $path = $this->path($path);

        if (!$dom->load($path)) {
            throw new RuntimeException(sprintf('Failed to load XML from `%s`', $path));
        }

        return $dom;
    }
}
