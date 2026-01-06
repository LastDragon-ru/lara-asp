<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Closure;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as Contract;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Dependency;
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
        protected readonly Closure $save,
        /**
         * @var Closure(File): void
         */
        protected readonly Closure $queue,
        /**
         * @var Closure(DirectoryPath|FilePath): void
         */
        protected readonly Closure $delete,
    ) {
        $this->casts = [];
        $this->files = new WeakMap();
    }

    #[Override]
    public function get(FilePath $path): File {
        $path = $this->path($path);

        ($this->dispatcher)(new Dependency($path, DependencyResult::Found));

        $file = $this->fs->get($path);

        ($this->run)($file);

        return $file;
    }

    #[Override]
    public function find(FilePath $path): ?File {
        $file   = null;
        $path   = $this->path($path);
        $exists = $this->fs->exists($path);

        if ($exists) {
            ($this->dispatcher)(new Dependency($path, DependencyResult::Found));

            $file = $this->fs->get($path);

            ($this->run)($file);
        } else {
            ($this->dispatcher)(new Dependency($path, DependencyResult::NotFound));
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
        $file = $path instanceof File ? $path : null;
        $path = $this->path($path instanceof File ? $path->path : $path);

        ($this->dispatcher)(new Dependency($path, DependencyResult::Saved));

        try {
            $saved = $this->fs->write($file ?? $path, $content);

            ($this->save)($saved);
        } finally {
            if (($saved ?? $file) !== null) {
                unset($this->files[$saved ?? $file]);
            }
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

            ($this->dispatcher)(new Dependency($filepath, DependencyResult::Queued));
            ($this->queue)($this->fs->get($filepath));
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(DirectoryPath|FilePath|File|iterable $path): void {
        $iterator = match (true) {
            $path instanceof FilePath || $path instanceof DirectoryPath => [$path],
            $path instanceof File                                       => [$path->path],
            default                                                     => $path,
        };

        foreach ($iterator as $delete) {
            $delete = $this->path($delete);

            ($this->dispatcher)(new Dependency($delete, DependencyResult::Deleted));

            $this->fs->delete($delete);

            ($this->delete)($delete);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function search(
        ?DirectoryPath $directory = null,
        array|string $include = [],
        array|string $exclude = [],
        bool $hidden = false,
    ): iterable {
        $path  = $this->path($directory ?? new DirectoryPath('.'));
        $found = $this->fs->search($path, (array) $include, (array) $exclude, $hidden);

        return $found;
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
