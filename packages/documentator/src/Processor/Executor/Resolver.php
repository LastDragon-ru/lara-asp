<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as ResolverContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved as Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult as Result;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;

use function is_string;

/**
 * @internal
 */
class Resolver implements ResolverContract {
    private readonly DirectoryPath $iHome;
    private readonly DirectoryPath $oHome;

    public function __construct(
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
        $this->iHome = (new DirectoryPath('~input'))->normalized();
        $this->oHome = (new DirectoryPath('~output'))->normalized();
    }

    #[Override]
    public function get(FilePath|string $path): File {
        $path = $this->path($path);

        try {
            $file = $this->fs->get($path);

            $this->notify($path, Result::Success);

            ($this->run)($file);
        } catch (Exception $exception) {
            $this->notify($path, Result::Failed);

            throw $exception;
        }

        return $file;
    }

    /**
     * @param FilePath|non-empty-string $path
     */
    #[Override]
    public function find(FilePath|string $path): ?File {
        $path = $this->path($path);

        try {
            $file = $this->fs->exists($path)
                ? $this->fs->get($path)
                : null;

            if ($file !== null) {
                $this->notify($path, Result::Success);

                ($this->run)($file);
            } else {
                $this->notify($path, Result::Null);
            }
        } catch (Exception $exception) {
            $this->notify($path, Result::Failed);

            throw $exception;
        }

        return $file;
    }

    #[Override]
    public function save(File|FilePath|string $path, object|string $content): File {
        $file = $path instanceof File ? $path : null;
        $path = $this->path($path instanceof File ? $path->path : $path);

        try {
            $file = $this->fs->write($file ?? $path, $content);

            $this->notify($path, Result::Success);

            ($this->run)($file);
        } catch (Exception $exception) {
            $this->notify($path, Result::Failed);

            throw $exception;
        }

        return $file;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function queue(FilePath|iterable|string $path): void {
        $iterator = $path instanceof FilePath || is_string($path) ? [$path] : $path;

        foreach ($iterator as $file) {
            $filepath = $this->path($file);

            try {
                $file = $this->fs->get($filepath);

                $this->notify($filepath, Result::Queued);

                ($this->queue)($file);
            } catch (Exception $exception) {
                $this->notify($filepath, Result::Failed);

                throw $exception;
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
        ?int $depth,
        DirectoryPath|string|null $directory = null,
    ): iterable {
        $directory = match (true) {
            $directory instanceof DirectoryPath => $directory,
            is_string($directory)               => new DirectoryPath($directory),
            $directory === null                 => new DirectoryPath('.'),
        };
        $directory = $this->path($directory);
        $include   = (array) $include;
        $exclude   = (array) $exclude;
        $files     = $this->fs->search($directory, $include, $exclude, $depth);

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

    protected function notify(FilePath $path, Result $result): void {
        $this->dispatcher->notify(
            new Event($path, $result),
        );
    }

    /**
     * @template T of DirectoryPath|FilePath|non-empty-string
     *
     * @param T $path
     *
     * @return (T is string ? FilePath : new<T>)
     */
    protected function path(DirectoryPath|FilePath|string $path): DirectoryPath|FilePath {
        $path = is_string($path) ? new FilePath($path) : $path;
        $path = match (true) {
            $path->parts[0] === $this->oHome->parts[0] => $this->output->resolve($this->oHome->relative($path) ?? $path),
            $path->parts[0] === $this->iHome->parts[0] => $this->input->resolve($this->iHome->relative($path) ?? $path),
            $path->relative                            => $this->directory->resolve($path),
            default                                    => $path->normalized(),
        };

        return $path;
    }
}
