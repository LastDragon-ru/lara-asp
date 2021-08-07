<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use Closure;
use Illuminate\Contracts\Container\Container;
use LogicException;
use Mockery;
use Mockery\Exception\InvalidCountException;
use Mockery\MockInterface;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

use function sprintf;

trait Override {
    /**
     * @var array<class-string,MockInterface>
     */
    private array $overrides = [];

    protected function tearDownOverride(): void {
        foreach ($this->overrides as $class => $spy) {
            try {
                $spy->shouldHaveBeenCalled();
            } catch (InvalidCountException $exception) {
                throw new OutOfBoundsException(sprintf(
                    'Override for `%s` should be used at least 1 times but used 0 times.',
                    $class,
                ), 0, $exception);
            }
        }
    }

    /**
     * @template T
     *
     * @param class-string<T>                            $class
     * @param null|Closure(T|MockInterface, TestCase): T $factory
     *
     * @return T|MockInterface
     */
    protected function override(string $class, Closure $factory = null): mixed {
        // Overridden?
        if (isset($this->overrides[$class])) {
            throw new LogicException(sprintf(
                'Override for `%s` already defined.',
                $class,
            ));
        }

        // Mock
        $mock = Mockery::mock($class);

        if ($factory) {
            $mock = $factory($mock, $this) ?: $mock;
        }

        // Override
        $this->overrides[$class] = Mockery::spy(static function () use ($mock): mixed {
            return $mock;
        });

        $this->getContainer()->bind($class, Closure::fromCallable($this->overrides[$class]));

        // Return
        return $mock;
    }

    abstract protected function getContainer(): Container;
}
