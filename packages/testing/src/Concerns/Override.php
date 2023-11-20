<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\TestCase;
use LogicException;
use Mockery;
use Mockery\Exception\InvalidCountException;
use Mockery\MockInterface;
use OutOfBoundsException;
use Override as OverrideAttribute;

use function is_callable;
use function sprintf;

/**
 * @mixin TestCase
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
     *
     * @param class-string<T>                                                                                   $class
     * @param callable(T&MockInterface,static=):void|callable(T&MockInterface,static=):T|(T&MockInterface)|null $factory
     *
     * @return T&MockInterface
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
        /** @var T&MockInterface $mock */
        $mock = Mockery::mock($class);

        if (is_callable($factory)) {
            /** @phpstan-ignore-next-line (void) is fine here */
            $mock = $factory($mock, $this) ?: $mock;
        } elseif ($factory) {
            $mock = $factory;
        } else {
            // empty
        }

        // Override
        $this->overrides[$class] = Mockery::spy(static function () use ($mock): mixed {
            return $mock;
        });

        Container::getInstance()->bind(
            $class,
            Closure::fromCallable($this->overrides[$class]),
        );

        // Return
        return $mock;
    }
}
