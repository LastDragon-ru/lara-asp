<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\DependencyResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved as Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult as Result;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;
use Traversable;

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
    public function resolve(Dependency $dependency): Traversable|File|null {
        try {
            $resolved = $dependency($this->fs);
            $result   = $resolved === null ? Result::Null : Result::Success;

            $this->notify($dependency, $result);

            if ($resolved instanceof File) {
                ($this->run)($resolved);
            } elseif ($resolved instanceof Traversable) {
                $resolved = $this->iterate($dependency, $resolved);
            } else {
                // empty
            }
        } catch (Exception $exception) {
            $this->exception = $exception;

            $this->notify($dependency, Result::Failed);

            throw $exception;
        }

        return $resolved;
    }

    #[Override]
    public function queue(Dependency $dependency): void {
        try {
            $resolved = $dependency($this->fs);

            if ($resolved instanceof File) {
                ($this->queue)($resolved);

                $this->notify($resolved, Result::Queued);
            } elseif ($resolved instanceof Traversable) {
                $this->notify($dependency, Result::Success);

                foreach ($resolved as $file) {
                    ($this->queue)($file);

                    $this->notify($file, Result::Queued);
                }
            } else {
                $this->notify($dependency, Result::Null);
            }
        } catch (Exception $exception) {
            $this->exception = $exception;

            $this->notify($dependency, Result::Failed);

            throw $exception;
        }
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
     * @template D of Dependency<Traversable<mixed, File>|File|null>
     * @template T of Traversable<mixed, File>
     *
     * @param D $dependency
     * @param T $resolved
     *
     * @return T
     */
    protected function iterate(Dependency $dependency, Traversable $resolved): Traversable {
        // Process
        try {
            $last = null;

            foreach ($resolved as $key => $value) {
                $last = $value;

                $this->notify($value, Result::Success);

                ($this->run)($value);

                yield $key => $value;
            }
        } catch (Exception $exception) {
            $this->exception = $exception;

            $this->notify($last ?? $dependency, Result::Failed);

            throw $exception;
        }

        // Just for the case
        yield from [];
    }

    /**
     * @template V of Traversable<mixed, File>|File|null
     * @template D of Dependency<V>
     *
     * @param D|File $dependency
     */
    protected function notify(Dependency|File $dependency, Result $result): void {
        $path = match (true) {
            $dependency instanceof Dependency => $dependency->getPath($this->fs),
            default                           => $dependency,
        };
        $path = match (true) {
            $path instanceof File => $path->path,
            default               => $path,
        };

        $this->dispatcher->notify(
            new Event($path, $result),
        );
    }
}
