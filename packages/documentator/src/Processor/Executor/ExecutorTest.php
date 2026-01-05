<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnavailable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File as FileImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\FilePath;
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
    #[DataProvider('dataProviderOnRun')]
    public function testOnRun(?bool $expected, State $state): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $path       = new FilePath('/file.md');
        $file       = new FileImpl($filesystem, $path);

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

        $executor->onRun($file);
    }

    /**
     * @param 'file'|'queue'|null $expected
     * @param non-empty-string    $current
     * @param non-empty-string    $path
     */
    #[DataProvider('dataProviderOnSave')]
    public function testOnSave(?string $expected, State $state, string $current, ?bool $skipped, string $path): void {
        $fs   = Mockery::mock(FileSystem::class);
        $path = new FilePath($path);
        $file = new FileImpl($fs, $path);

        $executor = Mockery::mock(ExecutorTest__Executor::class);
        $executor->shouldAllowMockingProtectedMethods();
        $executor->makePartial();

        if ($skipped !== null) {
            $executor
                ->shouldReceive('isSkipped')
                ->with($file)
                ->once()
                ->andReturn($skipped);
        } else {
            $executor
                ->shouldReceive('isSkipped')
                ->never();
        }

        if ($expected !== null) {
            $executor
                ->shouldReceive($expected)
                ->with($file)
                ->once()
                ->andReturns();
        } else {
            $executor
                ->shouldReceive('file')
                ->never();
            $executor
                ->shouldReceive('queue')
                ->never();
        }

        (new ReflectionProperty(Executor::class, 'state'))->setValue($executor, $state);
        (new ReflectionProperty(Executor::class, 'stack'))->setValue($executor, [$path->file($current)]);
        (new ReflectionProperty(Executor::class, 'processed'))->setValue($executor, [
            $path->path => true,
            $current    => true,
        ]);

        $executor->onSave($file);

        if ($expected !== null) {
            self::assertSame(
                [$current => true],
                (new ReflectionProperty(Executor::class, 'processed'))->getValue($executor),
            );
        }
    }

    #[DataProvider('dataProviderOnQueue')]
    public function testOnQueue(bool $expected, State $state): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $path       = new FilePath('/file.md');
        $file       = new FileImpl($filesystem, $path);

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
     * @return array<string, array{?bool, State}>
     */
    public static function dataProviderOnRun(): array {
        return [
            State::Preparation->name => [null, State::Preparation],
            State::Iteration->name   => [true, State::Iteration],
            State::Finished->name    => [true, State::Finished],
            State::Created->name     => [false, State::Created],
        ];
    }

    /**
     * @return array<string, array{'file'|'queue'|null, State, non-empty-string, ?bool, non-empty-string}>
     */
    public static function dataProviderOnSave(): array {
        return [
            'equal to the current file'                  => [
                null,
                State::Iteration,
                '/file.txt',
                null,
                '/file.txt',
            ],
            'not equal to the current file'              => [
                'queue',
                State::Iteration,
                '/file.txt',
                false,
                '/file.md',
            ],
            'not equal to the current file but finished' => [
                'file',
                State::Finished,
                '/file.txt',
                false,
                '/file.md',
            ],
            'not equal to the current file but skipped'  => [
                null,
                State::Iteration,
                '/file.txt',
                true,
                '/file.md',
            ],
        ];
    }

    /**
     * @return array<string, array{bool, State}>
     */
    public static function dataProviderOnQueue(): array {
        return [
            State::Preparation->name => [true, State::Preparation],
            State::Iteration->name   => [true, State::Iteration],
            State::Finished->name    => [false, State::Finished],
            State::Created->name     => [true, State::Created],
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
    public function onRun(File $file): void {
        parent::onRun($file);
    }

    #[Override]
    public function onSave(File $file): void {
        parent::onSave($file);
    }

    #[Override]
    public function onQueue(File $file): void {
        parent::onQueue($file);
    }
}
