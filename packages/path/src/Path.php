<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use Override;
use Stringable;

use function array_pop;
use function basename;
use function count;
use function explode;
use function implode;
use function in_array;
use function mb_rtrim;
use function mb_strlen;
use function mb_substr;
use function preg_match;
use function str_ends_with;
use function str_repeat;
use function str_replace;

/**
 * It differs slightly from the Symfony FileSystem Path component.
 *
 * + The home path (`~`) treatment as absolute (because in almost all cases it is absolute).
 * + URL/URI/etc not supported and treatment as path.
 *
 * @property-read TPath $name
 * @property-read Type  $type
 * @property-read bool  $normalized
 *
 * @template TPath of string = string
 *
 * @phpstan-sealed DirectoryPath|FilePath
 */
abstract class Path implements Stringable {
    protected ?bool $isNormalized = null; // `private` will lead to an error https://github.com/phpstan/phpstan/issues/13836

    /**
     * @internal `private` will lead to an error https://github.com/phpstan/phpstan/issues/
     */
    protected ?Type $cachedType = null;

    public function __construct(
        /**
         * @var TPath
         */
        public readonly string $path,
    ) {
        // empty
    }

    /**
     * @return TPath
     */
    #[Override]
    public function __toString(): string {
        return $this->path;
    }

    /**
     * @deprecated %{VERSION} Will be replaced to property hooks soon.
     */
    public function __isset(string $name): bool {
        return $this->__get($name) !== null;
    }

    /**
     * @deprecated %{VERSION} Will be replaced to property hooks soon.
     */
    public function __get(string $name): mixed {
        return match ($name) {
            'name'       => basename($this->path),
            'type'       => $this->cachedType   ??= self::getType($this->path),
            'normalized' => $this->isNormalized ??= static::normalize($this->path) === $this->path,
            default      => null,
        };
    }

    /**
     * @return DirectoryPath|FilePath
     */
    public static function make(string $path): self {
        $name = basename($path);
        $path = $name === '' || $name === '.' || $name === '..' || !str_ends_with($path, $name)
            ? new DirectoryPath($path)
            : new FilePath($path);

        return $path;
    }

    public function is(Type ...$type): bool {
        return in_array($this->type, $type, true);
    }

    /**
     * @return ($path is DirectoryPath ? DirectoryPath : FilePath)
     */
    public function resolve(self $path): self {
        return $path->is(Type::Relative) ? $this->concat($path) : $path->normalized();
    }

    /**
     * @return ($path is DirectoryPath ? DirectoryPath : FilePath)
     */
    public function concat(self $path): self {
        $concat = $this->directory()->path.$path->path;
        $concat = $path::normalize($concat);
        $concat = $path instanceof DirectoryPath
            ? new DirectoryPath($concat)
            : new FilePath($concat); // @phpstan-ignore argument.type (ok. it will throw error if empty)

        $this->sync($concat)->isNormalized = true;

        return $concat;
    }

    public function parent(): DirectoryPath {
        return (new DirectoryPath("{$this->path}/.."))->normalized();
    }

    /**
     * @param non-empty-string $path
     */
    public function file(string $path): FilePath {
        return $this->resolve(new FilePath($path));
    }

    public function directory(?string $path = null): DirectoryPath {
        return $path !== null
            ? $this->resolve(new DirectoryPath($path))
            : $this->parent();
    }

    /**
     * @return ($path is DirectoryPath ? DirectoryPath|null : FilePath|null)
     */
    public function relative(self $path): ?self {
        // Relative?
        if ($path->is(Type::Relative)) {
            return $path->normalized();
        }

        // Resolvable?
        if (!$this->is($path->type)) {
            return null;
        }

        // Convert
        $root  = explode('/', $this->directory()->path);
        $parts = explode('/', $path->normalized()->path);
        $count = 0;

        foreach ($root as $i => $part) {
            if ($part !== ($parts[$i] ?? '')) {
                break;
            }

            unset($parts[$i]);

            $count++;
        }

        $repeat                 = count($root) - $count - 1;
        $relative               = ($repeat > 0 ? str_repeat('../', $repeat) : '').implode('/', $parts);
        $relative               = $path::normalize($relative);
        $relative               = $path instanceof DirectoryPath
            ? new DirectoryPath($relative)
            : new FilePath($relative); // @phpstan-ignore argument.type (ok. it will throw error if empty)
        $relative->cachedType   = Type::Relative;
        $relative->isNormalized = true;

        // Return
        return $relative;
    }

    /**
     * @return ($this is DirectoryPath ? DirectoryPath : FilePath)
     */
    public function normalized(): self {
        if ($this->normalized) {
            // @phpstan-ignore return.type (sealed not narrowed correctly, see https://github.com/phpstan/phpstan/issues/13839)
            return $this;
        }

        $path = static::normalize($this->path);
        $path = $this instanceof DirectoryPath
            ? new DirectoryPath($path)
            : new FilePath($path); // @phpstan-ignore argument.type (ok. it will throw error if empty)

        $this->sync($path)->isNormalized = true;

        return $path;
    }

    public function equals(?self $path): bool {
        return $path instanceof $this
            && $path->normalized()->path === $this->normalized()->path;
    }

    /**
     * @template T of self
     *
     * @param T $path
     *
     * @return T
     */
    protected function sync(self $path): self {
        $path->cachedType   = $this->cachedType;
        $path->isNormalized = $this->isNormalized;

        return $path;
    }

    protected static function normalize(string $path): string {
        // Empty?
        if ($path === '') {
            return '';
        }

        // Normalize
        $path    = str_replace('\\', '/', $path);
        $root    = self::getRoot($path);
        $result  = [];
        $default = '..';

        if ($root !== '') {
            $path    = mb_substr($path, mb_strlen($root));
            $root    = mb_rtrim($root, '/').'/';
            $default = '';
        }

        foreach (explode('/', $path) as $part) {
            // Ignore?
            if ($part === '' || $part === '.') {
                continue;
            }

            // Skip?
            if ($part === '..' && ($result[count($result) - 1] ?? $default) !== '..') {
                array_pop($result);

                continue;
            }

            // Save
            $result[] = $part;
        }

        // Return
        return $root.implode('/', $result);
    }

    private static function getType(string $path): Type {
        return match (str_replace('\\', '/', self::getRoot($path))) {
            ''        => Type::Relative,
            '~', '~/' => Type::Home,
            default   => Type::Absolute,
        };
    }

    private static function getRoot(string $path): string {
        // Empty?
        if ($path === '') {
            return '';
        }

        // Root?
        $first = mb_substr($path, 0, 1);

        if (self::isRoot($first)) {
            return $first;
        }

        // Home?
        $second = mb_substr($path, 1, 1);

        if (self::isRoot($second) && $first === '~') {
            return $first.$second;
        }

        // Win drive?
        $third = mb_substr($path, 2, 1);

        if (self::isRoot($third) && $second === ':' && preg_match('/[a-z]/ui', $first) !== false) {
            return $first.$second.$third;
        }

        // Nope
        return '';
    }

    private static function isRoot(string $path): bool {
        return ($path === '' || $path === '/' || $path === '\\');
    }
}
