<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Hook;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

// @phpcs:disable SlevomatCodingStandard.Numbers.RequireNumericLiteralSeparator

/**
 * @var list<array{float, Event}> $events
 */
$events = [
    [
        1767697044.8956,
        new ProcessBegin(
            new DirectoryPath(
                '/tmp/',
            ),
            new DirectoryPath(
                '/tmp/',
            ),
            [
                '**/*.htm',
            ],
            [
                // empty
            ],
        ),
    ],
    [
        1767697044.927005,
        new FileBegin(
            new FilePath(
                '/tmp/file.htm',
            ),
        ),
    ],
    [
        1767697044.9302,
        new TaskBegin(
            FileTask::class,
        ),
    ],
    [
        1767697044.933194,
        new Dependency(
            new FilePath(
                '/tmp/file.htm',
            ),
            DependencyResult::Saved,
        ),
    ],
    [
        1767697044.934657,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767697044.93467,
        new TaskBegin(
            FileTask::class,
        ),
    ],
    [
        1767697044.934689,
        new Dependency(
            new FilePath(
                '/tmp/file.htm',
            ),
            DependencyResult::Deleted,
        ),
    ],
    [
        1767697044.938051,
        new FileSystemDeleteBegin(
            new FilePath(
                '/tmp/file.htm',
            ),
        ),
    ],
    [
        1767697044.939797,
        new FileSystemDeleteEnd(
            FileSystemDeleteResult::Success,
        ),
    ],
    [
        1767697044.939807,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767697044.939839,
        new TaskBegin(
            FileTask::class,
        ),
    ],
    [
        1767697044.939842,
        new TaskEnd(
            TaskResult::Skipped,
        ),
    ],
    [
        1767697044.941372,
        new FileEnd(
            FileResult::Success,
        ),
    ],
    [
        1767697044.945949,
        new HookBegin(
            Hook::After,
            new FilePath(
                '/tmp/file.htm',
            ),
        ),
    ],
    [
        1767697044.946025,
        new TaskBegin(
            HookTask::class,
        ),
    ],
    [
        1767697044.946036,
        new TaskEnd(
            TaskResult::Skipped,
        ),
    ],
    [
        1767697044.947305,
        new HookEnd(
            HookResult::Success,
        ),
    ],
    [
        1767697044.948559,
        new ProcessEnd(
            ProcessResult::Success,
        ),
    ],
];

return $events;
