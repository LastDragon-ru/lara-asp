<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Package;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LogicException;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionAttribute;
use ReflectionClass;

use function is_a;
use function is_array;
use function reset;
use function sprintf;

/**
 * The origin of the test should in the actual test class otherwise will be
 * impossible go to Test location and rerun it in PHPStorm (not sure is
 * PHPStorm or PHPUnit issue). Anyway, this approach requires less
 * copy-pasting.
 *
 * @mixin TestCase
 *
 * @internal
 */
trait OperatorTests {
    /**
     * @template T of EloquentBuilder<EloquentModel>|QueryBuilder
     *
     * @param class-string<Handler>                                             $directive
     * @param array{query: string, bindings: array<array-key, mixed>}|Exception $expected
     * @param Closure(static): T                                                $builderFactory
     * @param Closure(static): Argument                                         $argumentFactory
     * @param Closure(static): Context|null                                     $contextFactory
     * @param Closure(T, Field): string|null                                    $resolver
     */
    private function testDatabaseOperator(
        string $directive,
        array|Exception $expected,
        Closure $builderFactory,
        Field $field,
        Closure $argumentFactory,
        ?Closure $contextFactory,
        ?Closure $resolver,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $builder = null;
        $actual  = $this->callOperator(
            $directive,
            $builderFactory,
            $field,
            $argumentFactory,
            $contextFactory,
            $resolver,
            $builder,
        );

        if (is_array($expected)) {
            if ($builder instanceof EloquentBuilder) {
                self::assertInstanceOf(EloquentBuilder::class, $actual);
                self::assertDatabaseQueryEquals($expected, $actual);
            } else {
                self::assertInstanceOf(QueryBuilder::class, $actual);
                self::assertDatabaseQueryEquals($expected, $actual);
            }
        } else {
            self::fail('Something wrong...');
        }
    }

    /**
     * @template T of ScoutBuilder<EloquentModel>
     *
     * @param class-string<Handler>          $directive
     * @param array<string, mixed>|Exception $expected
     * @param Closure(static): T             $builderFactory
     * @param Closure(static): Argument      $argumentFactory
     * @param Closure(static): Context|null  $contextFactory
     * @param Closure(T, Field): string|null $resolver
     */
    private function testScoutOperator(
        string $directive,
        array|Exception $expected,
        Closure $builderFactory,
        Field $field,
        Closure $argumentFactory,
        ?Closure $contextFactory,
        ?Closure $resolver,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $actual = $this->callOperator(
            $directive,
            $builderFactory,
            $field,
            $argumentFactory,
            $contextFactory,
            $resolver,
        );

        if (is_array($expected)) {
            self::assertInstanceOf(ScoutBuilder::class, $actual);
            self::assertScoutQueryEquals($expected, $actual);
        } else {
            self::fail('Something wrong...');
        }
    }

    /**
     * @return class-string<Operator>
     */
    private function getOperator(): string {
        $class = new ReflectionClass($this);
        $attrs = $class->getAttributes(CoversClass::class, ReflectionAttribute::IS_INSTANCEOF);
        $attr  = reset($attrs);
        $class = $attr !== false ? $attr->newInstance()->className() : null;

        if ($class === null || !is_a($class, Operator::class, true)) {
            throw new LogicException(
                sprintf(
                    'The `%s` attribute is missed or is not an `%s` instance.',
                    CoversClass::class,
                    Operator::class,
                ),
            );
        }

        return $class;
    }

    /**
     * @template T of object
     *
     * @param class-string<Handler>          $directive
     * @param Closure(static): T             $builderFactory
     * @param Closure(static): Argument      $argumentFactory
     * @param Closure(static): Context|null  $contextFactory
     * @param Closure(T, Field): string|null $resolver
     * @param ?T                             $builder
     * @param-out T                          $builder
     */
    private function callOperator(
        string $directive,
        object $builderFactory,
        Field $field,
        Closure $argumentFactory,
        ?Closure $contextFactory,
        ?Closure $resolver,
        ?object &$builder = null,
    ): object {
        if ($resolver !== null) {
            $this->override(
                BuilderFieldResolver::class,
                static function (MockInterface $mock) use ($resolver): void {
                    $mock
                        ->shouldReceive('getField')
                        ->atLeast()
                        ->once()
                        ->andReturnUsing($resolver);
                },
            );
        }

        $operator = $this->app()->make($this->getOperator());
        $argument = $argumentFactory($this);
        $context  = $contextFactory !== null ? $contextFactory($this) : new Context();
        $handler  = $this->app()->make($directive);
        $builder  = $builderFactory($this);
        $actual   = $operator->call($handler, $builder, $field, $argument, $context);

        return $actual;
    }
}
