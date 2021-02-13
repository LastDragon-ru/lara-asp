<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use DOMDocument;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;

use function basename;
use function dirname;
use function file_get_contents;
use function sprintf;
use function str_replace;
use function str_starts_with;

/**
 * Small helper to load data associated with test.
 */
class TestData {
    private ReflectionClass $test;

    public function __construct(string $test) {
        $this->test = new ReflectionClass($test);
    }

    public function path(string $path): string {
        $dir  = dirname(str_replace('\\', '/', $this->test->getFileName()));
        $name = basename($this->test->getFileName(), '.php');
        $path = str_starts_with($path, '.') ? $path : '/'.$path;
        $path = "{$dir}/{$name}{$path}";

        return $path;
    }

    public function file(string $path): SplFileInfo {
        return new SplFileInfo($this->path($path));
    }

    public function content(string $path): string {
        return file_get_contents($this->path($path));
    }

    public function dom(string $path): DOMDocument {
        $dom  = new DOMDocument();
        $path = $this->path($path);

        if (!$dom->load($path)) {
            throw new RuntimeException(sprintf("Failed to load XML from `%s`", $path));
        }

        return $dom;
    }
}
