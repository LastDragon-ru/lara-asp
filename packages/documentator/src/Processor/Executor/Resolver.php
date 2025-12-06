<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\DependencyResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved as Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult as Result;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;

use function is_string;

/**
 * @internal
 */
class Resolver implements DependencyResolver {
    protected ?Exception $exception = null;

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
        // empty
    }

    #[Override]
    public function get(FilePath|string $path): File {
        try {
            $path = $this->path($path);
            $path = $this->fs->get($path);

            $this->notify($path, Result::Success);

            ($this->run)($path);
        } catch (Exception $exception) {
            $this->exception = $exception;

            $this->notify($path, Result::Failed);

            throw $exception;
        }

        return $path;
    }

    /**
     * @param FilePath|non-empty-string $path
     */
    #[Override]
    public function find(FilePath|string $path): ?File {
        try {
            $path = $this->path($path);
            $file = $this->fs->exists($path)
                ? $this->fs->get($path)
                : null;

            if ($file !== null) {
                $this->notify($file, Result::Success);

                ($this->run)($file);
            } else {
                $this->notify($path, Result::Null);
            }
        } catch (Exception $exception) {
            $this->exception = $exception;

            $this->notify($path, Result::Failed);

            throw $exception;
        }

        return $file;
    }

    #[Override]
    public function save(File|FilePath|string $path, object|string $content): File {
        try {
            $path = $path instanceof File ? $path : $this->path($path, true);
            $path = $this->fs->write($path, $content);

            $this->notify($path, Result::Success);

            ($this->run)($path);
        } catch (Exception $exception) {
            $this->exception = $exception;

            $this->notify($path, Result::Failed);

            throw $exception;
        }

        return $path;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function queue(FilePath|iterable|string $path): void {
        $iterator = $path instanceof FilePath || is_string($path) ? [$path] : $path;

        foreach ($iterator as $file) {
            try {
                $file = $this->path($file);
                $file = $this->fs->get($file);

                $this->notify($file, Result::Queued);

                ($this->queue)($file);
            } catch (Exception $exception) {
                $this->exception = $exception;

                $this->notify($file, Result::Failed);

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

    public function check(): void {
        if ($this->exception === null) {
            return;
        }

        $exception       = $this->exception;
        $this->exception = null;

        throw $exception;
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
     * @param File|FilePath|non-empty-string $path
     */
    protected function notify(File|FilePath|string $path, Result $result): void {
        $path = match (true) {
            $path instanceof File => $path->path,
            default               => $path,
        };
        $path = $this->path($path);

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
    protected function path(DirectoryPath|FilePath|string $path, bool $output = false): DirectoryPath|FilePath {
        $path = is_string($path) ? new FilePath($path) : $path;
        $path = ($output ? $this->output : $this->directory)->resolve($path);

        return $path;
    }
}
