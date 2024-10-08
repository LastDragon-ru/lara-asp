<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
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
 * @mixin TestCase
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 * @internal
 */
trait OperatorTests {
    /**
     * The origin of the test should in the actual test class otherwise will be
     * impossible go to Test location and rerun it in PHPStorm (not sure is
     * PHPStorm or PHPUnit issue). Anyway, this approach requires less
     * copy-pasting.
     *
     * @param class-string<Handler>               $directive
     * @param array<array-key, mixed>|Exception   $expected
     * @param Closure(static): object             $builderFactory
     * @param Closure(static): Argument           $argumentFactory
     * @param Closure(static): Context|null       $contextFactory
     * @param Closure(object, Field): string|null $resolver
     */
    private function testOperator(
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

        if (is_array($expected)) {
            if ($builder instanceof EloquentBuilder) {
                self::assertArrayHasKey('query', $expected);
                self::assertArrayHasKey('bindings', $expected);
                self::assertInstanceOf(EloquentBuilder::class, $actual);
                self::assertDatabaseQueryEquals($expected, $actual);
            } elseif ($builder instanceof QueryBuilder) {
                self::assertArrayHasKey('query', $expected);
                self::assertArrayHasKey('bindings', $expected);
                self::assertInstanceOf(QueryBuilder::class, $actual);
                self::assertDatabaseQueryEquals($expected, $actual);
            } elseif ($builder instanceof ScoutBuilder) {
                self::assertInstanceOf(ScoutBuilder::class, $actual);
                self::assertScoutQueryEquals($expected, $actual);
            } else {
                self::fail(
                    sprintf(
                        'Builder `%s` is not supported.',
                        $builder::class,
                    ),
                );
            }
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
}
