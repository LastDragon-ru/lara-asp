<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use Override;
use Stringable;

use function array_last;
use function array_pop;
use function array_slice;
use function count;
use function explode;
use function implode;
use function in_array;
use function mb_rtrim;
use function mb_strlen;
use function mb_strpos;
use function mb_strtoupper;
use function mb_substr;
use function preg_match;
use function str_ends_with;
use function str_repeat;
use function str_replace;
use function str_starts_with;

/**
 * URL/URI/etc not supported and treatment as path.
 *
 * @property-read TPath                  $name
 * @property-read Type                   $type
 * @property-read non-empty-list<string> $parts
 * @property-read bool                   $relative
 * @property-read bool                   $normalized
 *
 * @template TPath of string = string
 *
 * @phpstan-sealed DirectoryPath|FilePath
 */
abstract class Path implements Stringable {
    /**
     * @internal `private` will lead to an error https://github.com/phpstan/phpstan/issues/
     */
    protected ?bool $cNormalized = null;

    /**
     * @internal `private` will lead to an error https://github.com/phpstan/phpstan/issues/
     *
     * @var non-empty-list<string>
     */
    protected ?array $cParts = null;

    /**
     * @internal `private` will lead to an error https://github.com/phpstan/phpstan/issues/
     */
    protected ?Type $cType = null;

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
            'name'       => self::name($this->parts),
            'type'       => $this->cType       ??= self::type($this->path),
            'parts'      => $this->cParts      ??= self::parts($this->type, $this->path),
            'normalized' => $this->cNormalized ??= static::normalize($this->type, $this->parts) === $this->path,
            'relative'   => $this->is(Type::Relative, Type::WindowsRelative),
            default      => null,
        };
    }

    /**
     * @return DirectoryPath|FilePath
     */
    public static function make(string $path): self {
        $type  = self::type($path);
        $parts = self::parts($type, $path);
        $name  = self::name($parts);
        $path  = $path === '~' || $name === '' || $name === '.' || $name === '..' || !str_ends_with($path, $name)
            ? new DirectoryPath($path)
            : new FilePath($path);

        $path->cType  = $type;
        $path->cParts = $parts;

        return $path;
    }

    public function is(Type ...$type): bool {
        return in_array($this->type, $type, true);
    }

    /**
     * @return ($path is DirectoryPath ? DirectoryPath : FilePath)
     */
    public function resolve(self $path): self {
        if ($path->is(Type::Relative)) {
            $resolved = [...$this->directory()->parts, ...$path->parts];
            $resolved = $path::normalize($this->type, $resolved);
            $resolved = $path instanceof DirectoryPath
                ? new DirectoryPath($resolved)
                : new FilePath($resolved); // @phpstan-ignore argument.type (ok. it will throw error if empty)

            $resolved->cType       = $this->type;
            $resolved->cNormalized = true;
        } elseif ($path->is(Type::WindowsRelative)) {
            /**
             * Relative path resolves based on the current directory for the
             * same drive. We are using `$this` as the current directory.
             * If the drives do not match, the $path becomes absolute.
             *
             * @see https://learn.microsoft.com/en-us/dotnet/standard/io/file-path-formats
             */

            $currentDrive = mb_strtoupper(mb_substr($this->parts[0], 0, 1));
            $pathDrive    = mb_strtoupper(mb_substr($path->parts[0], 0, 1));

            if ($currentDrive === $pathDrive) {
                $type     = $this->type;
                $resolved = [...$this->directory()->parts, ...array_slice($path->parts, 1)];
            } else {
                $type     = Type::WindowsAbsolute;
                $resolved = [$path->parts[0].'/', ...array_slice($path->parts, 1)];
            }

            $resolved = $path::normalize($type, $resolved);
            $resolved = $path instanceof DirectoryPath
                ? new DirectoryPath($resolved)
                : new FilePath($resolved); // @phpstan-ignore argument.type (ok. it will throw error if empty)

            $resolved->cType       = $type;
            $resolved->cNormalized = true;
        } else {
            $resolved = $path->normalized();
        }

        return $resolved;
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
        if ($path->relative) {
            return $path->normalized();
        }

        // Resolvable?
        $root  = $this->directory()->parts;
        $parts = $path->normalized()->parts;

        if ($root[0] !== $parts[0]) {
            return null;
        }

        // Convert
        $count = 0;

        foreach ($root as $i => $part) {
            if ($part !== ($parts[$i] ?? '')) {
                break;
            }

            unset($parts[$i]);

            $count++;
        }

        $type     = Type::Relative;
        $repeat   = count($root) - $count;
        $relative = ($repeat > 0 ? str_repeat('../', $repeat) : '').implode('/', $parts);
        $relative = $path::normalize($type, $path::parts($type, $relative));
        $relative = $path instanceof DirectoryPath
            ? new DirectoryPath($relative)
            : new FilePath($relative); // @phpstan-ignore argument.type (ok. it will throw error if empty)

        $relative->cType       = $type;
        $relative->cNormalized = true;

        // Return
        return $relative;
    }

    /**
     * @return ($this is DirectoryPath ? DirectoryPath : FilePath)
     */
    public function normalized(): self {
        if ($this->cNormalized === true) {
            // @phpstan-ignore return.type (sealed not narrowed correctly, see https://github.com/phpstan/phpstan/issues/13839)
            return $this;
        }

        $path = static::normalize($this->type, $this->parts);
        $path = $this instanceof DirectoryPath
            ? new DirectoryPath($path)
            : new FilePath($path); // @phpstan-ignore argument.type (ok. it will throw error if empty)

        $path->cType       = $this->type;
        $path->cNormalized = true;

        return $path;
    }

    public function equals(?self $path): bool {
        return $this === $path
            || ($path instanceof $this && $path->normalized()->path === $this->normalized()->path);
    }

    /**
     * @param non-empty-list<string> $parts
     */
    protected static function normalize(Type $type, array $parts): string {
        // Body
        $result  = [];
        $default = $parts[0] === '' ? '..' : '';

        for ($i = 1, $c = count($parts); $i < $c; $i++) {
            // Ignore?
            if ($parts[$i] === '' || $parts[$i] === '.') {
                continue;
            }

            // Skip?
            if ($parts[$i] === '..' && ($result[count($result) - 1] ?? $default) !== '..') {
                array_pop($result);

                continue;
            }

            // Save
            $result[] = $parts[$i];
        }

        // Root
        $root = str_replace('\\', '/', $parts[0]);
        $root = match ($type) {
            default               => $root.(mb_substr($root, -1) !== '/' ? '/' : ''),
            Type::Relative        => '',
            Type::WindowsAbsolute,
            Type::WindowsRelative => mb_strtoupper($root),
        };

        // Return
        return $root.implode('/', $result);
    }

    private static function type(string $path): Type {
        // Empty?
        if ($path === '') {
            return Type::Relative;
        }

        // Check
        $prefix = mb_substr($path, 0, 3);
        $prefix = str_replace('\\', '/', $prefix);
        $first  = mb_substr($prefix, 0, 1);
        $second = mb_substr($prefix, 1, 1);
        $third  = mb_substr($prefix, 2, 1);
        $type   = Type::Relative;

        if ($first === '/' && $second === '/' && $third !== '/') {
            $type = Type::Unc;
        } elseif ($first === '~' && $second === '/') {
            $type = Type::Home;
        } elseif ($second === ':' && preg_match('/[a-z]/ui', $first) > 0) {
            $type = $third === '/' || $third === ''
                ? Type::WindowsAbsolute
                : Type::WindowsRelative;
        } elseif ($first === '/') {
            $type = Type::Absolute;
        } else {
            // empty
        }

        return $type;
    }

    /**
     * @return non-empty-list<string>
     */
    private static function parts(Type $type, string $path): array {
        $normalized = str_replace('\\', '/', $path);
        $length     = match ($type) {
            Type::Absolute        => 1,
            Type::Relative        => str_starts_with($normalized, './') ? 2 : 0,
            Type::Unc             => self::length($normalized, 4),
            Type::Home            => 2,
            Type::WindowsRelative => 2,
            Type::WindowsAbsolute => 3,
        };
        $prefix = match ($type) {
            Type::Relative => '',
            default        => mb_substr($path, 0, $length),
        };
        $suffix = mb_substr($normalized, $length);
        $suffix = mb_rtrim($suffix, '/\\');
        $parts  = $suffix !== '' && $suffix !== '.'
            ? [$prefix, ...explode('/', $suffix)]
            : [$prefix];

        return $parts;
    }

    private static function length(string $path, int $nth = 1): int {
        $length = 0;

        for ($i = $nth; $i > 0; $i--) {
            $position = mb_strpos($path, '/', $length);

            if ($position === false) {
                $length = mb_strlen($path);
                break;
            }

            $length = $position + 1;
        }

        return $length;
    }

    /**
     * @param list<string> $parts
     */
    private static function name(array $parts): string {
        return count($parts) > 1 ? array_last($parts) : '';
    }
}
