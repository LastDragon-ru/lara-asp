<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use Exception;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem\ContentCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Markdown\MarkdownCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ClassCommentCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ClassMarkdownCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ClassObjectCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ComposerPackageCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Serializer\SerializableCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CastFromFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CastToFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use RuntimeException;
use UnexpectedValueException;
use WeakMap;

use function class_exists;
use function class_implements;
use function sprintf;

class Caster {
    /**
     * @var WeakMap<File, array<class-string<object>, object>>
     */
    private WeakMap              $files;
    private readonly Casts       $casts;
    private readonly ContentCast $content;

    public function __construct(
        protected readonly ContainerResolver $container,
        protected readonly FileSystemAdapter $adapter,
    ) {
        $this->files   = new WeakMap();
        $this->casts   = new Casts($container);
        $this->content = new ContentCast($this->adapter);

        $this->addBuiltInCasts();
    }

    protected function addBuiltInCasts(): void {
        $this->addCast(ComposerPackageCast::class);
        $this->addCast(ClassMarkdownCast::class);
        $this->addCast(ClassCommentCast::class);
        $this->addCast(ClassObjectCast::class);
        $this->addCast(SerializableCast::class);
        $this->addCast(MarkdownCast::class);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function get(File $file, string $class): mixed {
        if (!isset($this->files[$file][$class])) {
            try {
                $cast  = $this->getCast($file, $class);
                $value = $cast->castTo($file, $class);

                if ($value instanceof $class) {
                    $this->files[$file]       ??= [];
                    $this->files[$file][$class] = $value;
                } else {
                    throw new UnexpectedValueException(
                        sprintf(
                            'Expected `%s`, got `%s` (cast `%s`).',
                            $class,
                            ($value !== null ? $value::class : 'null'),
                            $cast::class,
                        ),
                    );
                }
            } catch (Exception $exception) {
                throw new CastToFailed($file, $class, $exception);
            }
        }

        return $this->files[$file][$class]; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }

    /**
     * @param class-string $class
     */
    public function has(File $file, string $class): bool {
        return isset($this->files[$file][$class]);
    }

    /**
     * @template T of object
     *
     * @param T $value
     */
    public function set(File $file, object $value): void {
        try {
            $cast                                  = $this->getCast($file, $value::class);
            $this->files[$file]                  ??= [];
            $this->files[$file][$cast::getClass()] = $value;
        } catch (Exception $exception) {
            throw new CastToFailed($file, $value::class, $exception);
        }
    }

    public function reset(File $file): void {
        $this->files[$file] = [];
    }

    public function serialize(File $file, object $value): string {
        try {
            $cast   = $this->getCast($file, $value::class);
            $string = $cast->castFrom($file, $value);

            if ($string === null) {
                throw new RuntimeException('Cast not found.');
            }
        } catch (Exception $exception) {
            throw new CastFromFailed($file, $value, $exception);
        }

        return $string;
    }

    /**
     * @template V of object
     * @template R of Cast<V>
     *
     * @param R|class-string<R> $cast
     */
    public function addCast(Cast|string $cast, ?int $priority = null): void {
        $tags       = [];
        $class      = $cast::getClass();
        $extensions = $cast::getExtensions();

        foreach ($extensions as $extension) {
            $tags[] = "{$class}:{$extension}";
        }

        $this->casts->add($cast, $tags, $priority);
    }

    /**
     * @template V of object
     * @template R of Cast<V>
     *
     * @param R|class-string<R> $cast
     */
    public function removeCast(Cast|string $cast): void {
        $this->casts->remove($cast);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return Cast<T>
     */
    protected function getCast(File $file, string $class): Cast {
        $cast = match (true) {
            $class === Content::class => $this->content,
            default                   => $this->casts->first(...$this->getTags($file, $class)),
        };

        if (!($cast instanceof Cast)) {
            throw new RuntimeException('Cast not found.');
        }

        return $cast; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }

    /**
     * @param class-string $class
     *
     * @return list<string>
     */
    protected function getTags(File $file, string $class): array {
        $tags       = [];
        $extensions = [$file->getExtension(), '*'];
        $implements = [$class, ...(class_exists($class) ? (array) class_implements($class) : [])];

        foreach ($implements as $interface) {
            foreach ($extensions as $extension) {
                $tags[] = "{$interface}:{$extension}";
            }
        }

        return $tags;
    }
}
