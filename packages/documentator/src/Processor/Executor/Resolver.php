<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as Contract;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use WeakMap;

/**
 * @internal
 */
class Resolver implements Contract {
    /**
     * @var array<class-string<Cast<object>>, Cast<object>>
     */
    private array $casts;
    /**
     * @var WeakMap<File, array<class-string<Cast<object>>, object>>
     */
    private WeakMap $files;

    public function __construct(
        private readonly ContainerResolver $container,
        protected readonly Dispatcher $dispatcher,
        protected readonly FileSystem $fs,
        /**
         * @var Closure(File): void
         */
        protected readonly Closure $run,
        /**
         * @var Closure(File): void
         */
        protected readonly Closure $queue,
    ) {
        $this->casts = [];
        $this->files = new WeakMap();
    }

    #[Override]
    public function get(FilePath $path): File {
        $path   = $this->path($path);
        $result = ($this->dispatcher)(new DependencyBegin($path), DependencyResult::Resolved);

        try {
            $file = $this->fs->get($path);

            ($this->run)($file);
        } catch (Exception $exception) {
            $result = DependencyResult::Error;

            throw $exception;
        } finally {
            ($this->dispatcher)(new DependencyEnd($result));
        }

        return $file;
    }

    #[Override]
    public function find(FilePath $path): ?File {
        $path   = $this->path($path);
        $result = ($this->dispatcher)(new DependencyBegin($path), DependencyResult::Resolved);

        try {
            $file = $this->fs->exists($path)
                ? $this->fs->get($path)
                : null;

            if ($file === null) {
                $result = DependencyResult::NotFound;
            } else {
                ($this->run)($file);
            }
        } catch (Exception $exception) {
            $result = DependencyResult::Error;

            throw $exception;
        } finally {
            ($this->dispatcher)(new DependencyEnd($result));
        }

        return $file;
    }

    #[Override]
    public function cast(File|FilePath $path, string $cast): object {
        $file = $path instanceof File ? $path : $this->get($path);

        if (!isset($this->files[$file][$cast])) {
            $this->casts[$cast]      ??= $this->container->getInstance()->make($cast);
            $this->files[$file]      ??= [];
            $this->files[$file][$cast] = ($this->casts[$cast])($this, $file);
        }

        return $this->files[$file][$cast]; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }

    #[Override]
    public function save(File|FilePath $path, string $content): void {
        $file   = $path instanceof File ? $path : null;
        $path   = $this->path($path instanceof File ? $path->path : $path);
        $result = ($this->dispatcher)(new DependencyBegin($path), DependencyResult::Saved);

        try {
            $saved = $this->fs->write($file ?? $path, $content);
        } catch (Exception $exception) {
            $result = DependencyResult::Error;

            throw $exception;
        } finally {
            if (($saved ?? $file) !== null) {
                unset($this->files[$saved ?? $file]);
            }

            ($this->dispatcher)(new DependencyEnd($result));
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function queue(FilePath|iterable $path): void {
        $iterator = $path instanceof FilePath ? [$path] : $path;

        foreach ($iterator as $file) {
            $filepath = $this->path($file);
            $result   = ($this->dispatcher)(new DependencyBegin($filepath), DependencyResult::Queued);

            try {
                $file = $this->fs->get($filepath);

                ($this->queue)($file);
            } catch (Exception $exception) {
                $result = DependencyResult::Error;

                throw $exception;
            } finally {
                ($this->dispatcher)(new DependencyEnd($result));
            }
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function search(
        array|string $include,
        array|string $exclude,
        ?DirectoryPath $directory = null,
    ): iterable {
        $path = match (true) {
            $directory instanceof DirectoryPath => $directory,
            $directory === null                 => new DirectoryPath('.'),
        };
        $path  = $this->path($path);
        $files = $this->fs->search($path, (array) $include, (array) $exclude);

        return $files;
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
            'input'     => $this->fs->input,
            'output'    => $this->fs->output,
            'directory' => $this->fs->directory,
            default     => null,
        };
    }

    /**
     * @template T of DirectoryPath|FilePath
     *
     * @param T $path
     *
     * @return new<T>
     */
    protected function path(DirectoryPath|FilePath $path): DirectoryPath|FilePath {
        $path = match (true) {
            $path->relative => $this->directory->resolve($path),
            default         => $path->normalized(),
        };

        return $path;
    }
}
