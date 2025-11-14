<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnavailable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;

/**
 * @internal
 */
#[CoversClass(Executor::class)]
final class ExecutorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderOnResolve')]
    public function testOnResolve(?bool $expected, ExecutorState $state): void {
        $file     = Mockery::mock(File::class);
        $executor = Mockery::mock(ExecutorTest__Executor::class);
        $executor->shouldAllowMockingProtectedMethods();
        $executor->makePartial();

        if ($expected !== false) {
            $executor
                ->shouldReceive('isSkipped')
                ->with($file)
                ->once()
                ->andReturn(false);
        }

        if ($expected === true) {
            $executor
                ->shouldReceive('file')
                ->with($file)
                ->once()
                ->andReturns();
        } elseif ($expected === false) {
            self::expectException(DependencyUnavailable::class);
        } else {
            // empty
        }

        (new ReflectionProperty(Executor::class, 'state'))->setValue($executor, $state);

        $executor->onResolve($file);
    }

    #[DataProvider('dataProviderOnQueue')]
    public function testOnQueue(bool $expected, ExecutorState $state): void {
        $file     = Mockery::mock(File::class);
        $executor = Mockery::mock(ExecutorTest__Executor::class);
        $executor->shouldAllowMockingProtectedMethods();
        $executor->makePartial();

        if ($expected) {
            $executor
                ->shouldReceive('isSkipped')
                ->with($file)
                ->once()
                ->andReturn(false);
            $executor
                ->shouldReceive('queue')
                ->with($file)
                ->once()
                ->andReturns();
        } else {
            self::expectException(DependencyUnavailable::class);
        }

        (new ReflectionProperty(Executor::class, 'state'))->setValue($executor, $state);

        $executor->onQueue($file);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{?bool, ExecutorState}>
     */
    public static function dataProviderOnResolve(): array {
        return [
            ExecutorState::Preparation->name => [null, ExecutorState::Preparation],
            ExecutorState::Iteration->name   => [true, ExecutorState::Iteration],
            ExecutorState::Finished->name    => [true, ExecutorState::Finished],
            ExecutorState::Created->name     => [false, ExecutorState::Created],
        ];
    }

    /**
     * @return array<string, array{bool, ExecutorState}>
     */
    public static function dataProviderOnQueue(): array {
        return [
            ExecutorState::Preparation->name => [true, ExecutorState::Preparation],
            ExecutorState::Iteration->name   => [true, ExecutorState::Iteration],
            ExecutorState::Finished->name    => [false, ExecutorState::Finished],
            ExecutorState::Created->name     => [true, ExecutorState::Created],
        ];
    }
    // </editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ExecutorTest__Executor extends Executor {
    #[Override]
    public function onResolve(File $resolved): void {
        parent::onResolve($resolved);
    }

    #[Override]
    public function onQueue(File $resolved): void {
        parent::onQueue($resolved);
    }
}
