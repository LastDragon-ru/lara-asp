<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Markdown\MarkdownCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ClassCommentCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ClassMarkdownCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ClassObjectCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ComposerPackageCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Serializer\SerializableCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CastFromFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CastToFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use RuntimeException;
use UnexpectedValueException;
use WeakMap;

use function interface_exists;
use function is_a;
use function sprintf;

/**
 * @internal
 */
class Caster {
    /**
     * @var WeakMap<File, array<class-string<object>, object>>
     */
    private WeakMap $files;

    public function __construct(
        protected readonly Casts $casts,
    ) {
        $this->files = new WeakMap();

        $this->addBuiltInCasts();
    }

    protected function addBuiltInCasts(): void {
        $this->casts->add(ComposerPackageCast::class);
        $this->casts->add(ClassMarkdownCast::class);
        $this->casts->add(ClassCommentCast::class);
        $this->casts->add(ClassObjectCast::class);
        $this->casts->add(SerializableCast::class);
        $this->casts->add(MarkdownCast::class);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function castTo(File $file, string $class): mixed {
        if (!isset($this->files[$file][$class])) {
            try {
                $cast  = $this->cast($file, $class);
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
                throw new CastToFailed($file->path, $class, $exception);
            }
        }

        return $this->files[$file][$class]; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }

    public function castFrom(File $file, object $value): ?string {
        // Same?
        $class = $value::class;

        if (($this->files[$file][$class] ?? null) === $value) {
            return null;
        }

        // Cast
        try {
            $cast   = $this->cast($file, $value::class);
            $string = $cast->castFrom($file, $value);

            if ($string === null) {
                throw new RuntimeException('Cast not found.');
            }
        } catch (Exception $exception) {
            throw new CastFromFailed($file->path, $value, $exception);
        }

        // Update
        $this->files[$file]                 = [];
        $this->files[$file][$cast::class()] = $value;

        // Return
        return $string;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return Cast<T>
     */
    private function cast(File $file, string $class): Cast {
        // Nope
        $cast  = null;
        $casts = $this->casts->get($file);

        foreach ($casts as $item) {
            $expected = $item::class();

            if ($expected === $class || (interface_exists($expected) && is_a($class, $expected, true))) {
                $cast = $item;
                break;
            }
        }

        if (!($cast instanceof Cast)) {
            throw new RuntimeException('Cast not found.');
        }

        return $cast; // @phpstan-ignore return.type (fixme(documentator): Caster)
    }
}
