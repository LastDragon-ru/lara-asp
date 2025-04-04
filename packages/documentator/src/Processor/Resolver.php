<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\DependencyResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;
use Traversable;

/**
 * @internal
 */
class Resolver implements DependencyResolver {
    protected ?Exception $exception = null;

    /**
     * @template V of Traversable<mixed, Directory|File>|Directory|File|null
     *
     * @param Closure(File, V): V $run
     */
    public function __construct(
        protected readonly Dispatcher $dispatcher,
        protected readonly FileSystem $fs,
        protected readonly File $file,
        protected readonly Closure $run,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(Dependency $dependency): Traversable|Directory|File|null {
        try {
            $resolved = $dependency($this->fs);
            $resolved = $this->notify($dependency, $resolved);
            $resolved = ($this->run)($this->file, $resolved);

            if ($resolved instanceof Traversable) {
                $resolved = $this->iterate($dependency, $resolved);
            }
        } catch (Exception $exception) {
            $this->exception = $this->notify($dependency, $exception);

            throw $exception;
        }

        return $resolved;
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
                $last  = $value;
                $value = $this->notify($value, $value);
                $value = ($this->run)($this->file, $value);

                yield $key => $value;
            }
        } catch (Exception $exception) {
            $this->exception = $this->notify($last ?? $dependency, $exception);

            throw $exception;
        }

        // Just for the case
        yield from [];
    }

    /**
     * @template R of Traversable<mixed, Directory|File>|Directory|File|Exception|null
     * @template V of Traversable<mixed, Directory|File>|Directory|File|null
     * @template D of Dependency<V>
     *
     * @param D|Directory|File $dependency
     * @param R                $resolved
     *
     * @return R
     */
    protected function notify(
        Dependency|Directory|File $dependency,
        Traversable|Directory|File|Exception|null $resolved,
    ): Traversable|Directory|File|Exception|null {
        $path = match (true) {
            $dependency instanceof Dependency => $dependency->getPath($this->fs),
            default                           => $dependency,
        };
        $result = match (true) {
            $resolved instanceof DependencyUnresolvable => DependencyResolvedResult::Missed,
            $resolved instanceof Exception              => DependencyResolvedResult::Failed,
            $resolved !== null                          => DependencyResolvedResult::Success,
            default                                     => DependencyResolvedResult::Null,
        };

        $this->dispatcher->notify(
            new DependencyResolved(
                $this->fs->getPathname($path),
                $result,
            ),
        );

        return $resolved;
    }
}
