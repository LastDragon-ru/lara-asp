<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Composer;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

use function array_key_exists;
use function array_unique;
use function array_values;
use function mb_ltrim;
use function mb_strlen;
use function mb_substr;
use function str_starts_with;
use function uksort;

class Package {
    /**
     * @var array<string, list<DirectoryPath>>|null
     */
    private ?array $namespaces = null;
    /**
     * @var array<string, non-empty-list<FilePath>|null>
     */
    private array $resolved = [];

    public function __construct(
        public readonly ComposerJson $json,
    ) {
        // empty
    }

    /**
     * @return non-empty-list<FilePath>|null
     */
    public function resolve(string $class): ?array {
        $class = $this->normalize($class);

        if (!array_key_exists($class, $this->resolved)) {
            $namespaces = $this->getNamespaces();
            $paths      = null;

            foreach ($namespaces as $namespace => $directories) {
                if (str_starts_with($class, $namespace)) {
                    $suffix = mb_substr($class, mb_strlen($namespace));

                    foreach ($directories as $directory) {
                        $paths[] = $directory->file("{$suffix}.php");
                    }
                }
            }

            $this->resolved[$class] = $paths !== null
                ? array_values(array_unique($paths))
                : null;
        }

        return $this->resolved[$class];
    }

    public function normalize(string $class): string {
        return '\\'.mb_ltrim($class, '\\');
    }

    /**
     * @return array<string, list<DirectoryPath>>
     */
    private function getNamespaces(): array {
        if (!isset($this->namespaces)) {
            $this->namespaces = [];
            $sections         = [
                $this->json->autoload?->psr4,
                $this->json->autoloadDev?->psr4,
            ];

            foreach ($sections as $section) {
                foreach ((array) $section as $namespace => $paths) {
                    $namespace = $this->normalize($namespace);

                    foreach ((array) $paths as $path) {
                        $this->namespaces[$namespace][] = new DirectoryPath($path);
                    }
                }
            }

            uksort($this->namespaces, static function (string $a, string $b): int {
                return mb_strlen($b) <=> mb_strlen($a);
            });
        }

        return $this->namespaces;
    }
}
