<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use Illuminate\Container\Container;
use LogicException;
use Mockery;
use Mockery\Exception\InvalidCountException;
use Mockery\MockInterface;
use OutOfBoundsException;
use Override as OverrideAttribute;
use PHPUnit\Framework\TestCase;

use function is_callable;
use function is_string;
use function sprintf;

// @phpcs:disable Generic.Files.LineLength.TooLong

/**
 * @phpstan-require-extends TestCase
 */
trait Override {
    /**
     * @var array<class-string,MockInterface>
     */
    private array $overrides = [];

    /**
     * @before
     * @internal
     */
    public function initOverride(): void {
        $this->overrides = [];
    }

    #[OverrideAttribute]
    protected function assertPostConditions(): void {
        foreach ($this->overrides as $class => $spy) {
            try {
                $spy->shouldHaveBeenCalled();
            } catch (InvalidCountException $exception) {
                throw new OutOfBoundsException(
                    sprintf(
                        'Override for `%s` should be used at least 1 times but used 0 times.',
                        $class,
                    ),
                    0,
                    $exception,
                );
            }
        }

        parent::assertPostConditions();
    }

    /**
     * @template T
     * @template TMock of T&MockInterface
     *
     * @param class-string<T>                                                                                                   $class
     * @param callable(TMock,static=):void|callable(TMock,static=):TMock|callable(TMock,static=):T|TMock|T|class-string<T>|null $factory
     *
     * @return TMock|T
     */
    protected function override(string $class, mixed $factory = null): mixed {
        // Overridden?
        if (isset($this->overrides[$class])) {
            throw new LogicException(
                sprintf(
                    'Override for `%s` already defined.',
                    $class,
                ),
            );
        }

        // Mock
        /** @var TMock|T $mock */
        $mock = is_callable($factory) || $factory === null
            ? Mockery::mock($class)
            : $factory;

        if (is_callable($factory)) {
            /** @phpstan-ignore-next-line it may return `void` so it is fine here */
            $mock = $factory($mock, $this) ?: $mock;
        } elseif (is_string($factory)) {
            $mock = Container::getInstance()->make($factory);
        } else {
            // empty
        }

        // Override
        $this->overrides[$class] = Mockery::spy(static function () use ($mock): mixed {
            return $mock;
        });

        Container::getInstance()->bind(
            $class,
            ($this->overrides[$class])(...),
        );

        // Return
        return $mock; // @phpstan-ignore-line `ContainerExtension` is not so smart yet.
    }
}
