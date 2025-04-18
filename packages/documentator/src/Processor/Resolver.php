<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\DependencyResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved as Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult as Result;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;
use Traversable;

use function assert;

/**
 * @internal
 */
class Resolver implements DependencyResolver {
    protected ?Exception $exception = null;

    public function __construct(
        protected readonly Dispatcher $dispatcher,
        protected readonly Iterator $iterator,
        protected readonly FileSystem $fs,
        protected readonly File $file,
        /**
         * @var Closure(File, Traversable<mixed, Directory|File>|Directory|File|null): void
         */
        protected readonly Closure $run,
    ) {
        // empty
    }

    #[Override]
    public function resolve(Dependency $dependency): Traversable|Directory|File|null {
        try {
            $resolved = $dependency($this->fs);

            $this->notify($dependency, $this->result($resolved));

            ($this->run)($this->file, $resolved);

            if ($resolved instanceof Traversable) {
                $resolved = $this->iterate($dependency, $resolved);
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

            if ($resolved instanceof Traversable) {
                $this->notify($dependency, Result::Success);

                foreach ($resolved as $file) {
                    assert($file instanceof File, 'https://github.com/phpstan/phpstan/issues/12894');

                    $this->iterator->push($file->getPath());
                    $this->notify($file, Result::Queued);
                }
            } elseif ($resolved instanceof File) {
                $this->iterator->push($resolved->getPath());
                $this->notify($resolved, Result::Queued);
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
     * @template V of Traversable<mixed, Directory|File>|Directory|File|null
     * @template D of Dependency<V>
     * @template T of Traversable<mixed, Directory|File>
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

                ($this->run)($this->file, $value);

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
     * @template V of Traversable<mixed, Directory|File>|Directory|File|null
     * @template D of Dependency<V>
     *
     * @param D|Directory|File $dependency
     */
    protected function notify(Dependency|Directory|File $dependency, Result $result): void {
        $path = match (true) {
            $dependency instanceof Dependency => $dependency->getPath($this->fs),
            default                           => $dependency,
        };

        $this->dispatcher->notify(
            new Event(
                $this->fs->getPathname($path),
                $result,
            ),
        );
    }

    /**
     * @see https://github.com/phpstan/phpstan/issues/12894
     */
    private function result(mixed $value): Result {
        return $value === null ? Result::Null : Result::Success;
    }
}
