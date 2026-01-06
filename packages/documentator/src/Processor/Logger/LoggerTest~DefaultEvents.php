<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskResult;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

// @phpcs:disable SlevomatCodingStandard.Numbers.RequireNumericLiteralSeparator

/**
 * @var list<array{float, Event}> $events
 */
$events = [
    [
        1767258559.941467,
        new ProcessBegin(
            new DirectoryPath(
                '/project/',
            ),
            new DirectoryPath(
                '/project/',
            ),
            [
                '**/*.md',
            ],
            [
                '**/**/*Test/**',
                '**/**/*Test.md',
                '**/**/*Test~*.md',
            ],
        ),
    ],
    [
        1767258560.023849,
        new FileBegin(
            new FilePath(
                '/project/README.md',
            ),
        ),
    ],
    [
        1767258560.195377,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\Preprocess\\Task',
        ),
    ],
    [
        1767258560.321817,
        new FileSystemReadBegin(
            new FilePath(
                '/project/README.md',
            ),
        ),
    ],
    [
        1767258560.337353,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            9452,
        ),
    ],
    [
        1767258561.265006,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/README.md',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258561.270537,
        new FileBegin(
            new FilePath(
                '/project/packages/graphql/README.md',
            ),
        ),
    ],
    [
        1767258561.270635,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\Preprocess\\Task',
        ),
    ],
    [
        1767258561.270647,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/README.md',
            ),
        ),
    ],
    [
        1767258561.272153,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            16514,
        ),
    ],
    [
        1767258561.649566,
        new Dependency(
            new FilePath(
                '/project/docs/Shared/Installation.md',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258561.653209,
        new FileBegin(
            new FilePath(
                '/project/docs/Shared/Installation.md',
            ),
        ),
    ],
    [
        1767258561.653354,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\Preprocess\\Task',
        ),
    ],
    [
        1767258561.653365,
        new FileSystemReadBegin(
            new FilePath(
                '/project/docs/Shared/Installation.md',
            ),
        ),
    ],
    [
        1767258561.654617,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            80,
        ),
    ],
    [
        1767258561.658758,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258561.67202,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\Task',
        ),
    ],
    [
        1767258561.672146,
        new Dependency(
            new FilePath(
                '/project/composer.json',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258561.69888,
        new FileSystemReadBegin(
            new FilePath(
                '/project/composer.json',
            ),
        ),
    ],
    [
        1767258561.700281,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            10566,
        ),
    ],
    [
        1767258561.700847,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258561.705385,
        new FileEnd(
            FileResult::Success,
        ),
    ],
    [
        1767258561.809199,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@searchBy.md',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258561.809311,
        new FileBegin(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@searchBy.md',
            ),
        ),
    ],
    [
        1767258561.809401,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\Preprocess\\Task',
        ),
    ],
    [
        1767258561.809414,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@searchBy.md',
            ),
        ),
    ],
    [
        1767258561.810773,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            9315,
        ),
    ],
    [
        1767258562.920702,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/src/SearchBy/Directives/DirectiveTest/Example.schema.graphql',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258562.923719,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/src/SearchBy/Directives/DirectiveTest/Example.schema.graphql',
            ),
        ),
    ],
    [
        1767258562.925762,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            456,
        ),
    ],
    [
        1767258562.996843,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@searchByConfigOperators.php',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258562.998444,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@searchByConfigOperators.php',
            ),
        ),
    ],
    [
        1767258562.999882,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            984,
        ),
    ],
    [
        1767258563.042281,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.042321,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\Task',
        ),
    ],
    [
        1767258563.042488,
        new Dependency(
            new FilePath(
                '/project/composer.json',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.042579,
        new FileSystemReadBegin(
            new FilePath(
                '/project/composer.json',
            ),
        ),
    ],
    [
        1767258563.044128,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            10566,
        ),
    ],
    [
        1767258563.084936,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.084965,
        new FileEnd(
            FileResult::Success,
        ),
    ],
    [
        1767258563.180834,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@sortBy.md',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.180924,
        new FileBegin(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@sortBy.md',
            ),
        ),
    ],
    [
        1767258563.180997,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\Preprocess\\Task',
        ),
    ],
    [
        1767258563.181007,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@sortBy.md',
            ),
        ),
    ],
    [
        1767258563.182833,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            5843,
        ),
    ],
    [
        1767258563.203856,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/src/SortBy/Directives/DirectiveTest/Example.schema.graphql',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.207056,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/src/SortBy/Directives/DirectiveTest/Example.schema.graphql',
            ),
        ),
    ],
    [
        1767258563.208631,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            353,
        ),
    ],
    [
        1767258563.235878,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@sortByConfigOrderByRandom.php',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.237494,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@sortByConfigOrderByRandom.php',
            ),
        ),
    ],
    [
        1767258563.238919,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            377,
        ),
    ],
    [
        1767258563.239005,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@sortByConfigNullsSingleValue.php',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.24063,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@sortByConfigNullsSingleValue.php',
            ),
        ),
    ],
    [
        1767258563.242293,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            240,
        ),
    ],
    [
        1767258563.242408,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@sortByConfigNullsArrayValue.php',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.243886,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@sortByConfigNullsArrayValue.php',
            ),
        ),
    ],
    [
        1767258563.245834,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            376,
        ),
    ],
    [
        1767258563.24751,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.247545,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\Task',
        ),
    ],
    [
        1767258563.247622,
        new Dependency(
            new FilePath(
                '/project/composer.json',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.256218,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.25624,
        new FileEnd(
            FileResult::Success,
        ),
    ],
    [
        1767258563.26187,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@stream.md',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.261956,
        new FileBegin(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@stream.md',
            ),
        ),
    ],
    [
        1767258563.26203,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\Preprocess\\Task',
        ),
    ],
    [
        1767258563.262055,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@stream.md',
            ),
        ),
    ],
    [
        1767258563.264062,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            4482,
        ),
    ],
    [
        1767258563.333668,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.333694,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\Task',
        ),
    ],
    [
        1767258563.333769,
        new Dependency(
            new FilePath(
                '/project/composer.json',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.339546,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.339561,
        new FileEnd(
            FileResult::Success,
        ),
    ],
    [
        1767258563.370123,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@type.md',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.370203,
        new FileBegin(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@type.md',
            ),
        ),
    ],
    [
        1767258563.37027,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\Preprocess\\Task',
        ),
    ],
    [
        1767258563.370279,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/docs/Directives/@type.md',
            ),
        ),
    ],
    [
        1767258563.371821,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            1133,
        ),
    ],
    [
        1767258563.401981,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.402005,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\Task',
        ),
    ],
    [
        1767258563.402072,
        new Dependency(
            new FilePath(
                '/project/composer.json',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.403373,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.403387,
        new FileEnd(
            FileResult::Success,
        ),
    ],
    [
        1767258563.43315,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/docs/Scalars/JsonString.md',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.433272,
        new FileBegin(
            new FilePath(
                '/project/packages/graphql/docs/Scalars/JsonString.md',
            ),
        ),
    ],
    [
        1767258563.433425,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\Preprocess\\Task',
        ),
    ],
    [
        1767258563.433435,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/docs/Scalars/JsonString.md',
            ),
        ),
    ],
    [
        1767258563.434976,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            760,
        ),
    ],
    [
        1767258563.436395,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.436424,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\Task',
        ),
    ],
    [
        1767258563.43649,
        new Dependency(
            new FilePath(
                '/project/composer.json',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.436612,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.436622,
        new FileEnd(
            FileResult::Success,
        ),
    ],
    [
        1767258563.438173,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/docs/Examples/BuilderInfoProvider.php',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.441295,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/docs/Examples/BuilderInfoProvider.php',
            ),
        ),
    ],
    [
        1767258563.443369,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            759,
        ),
    ],
    [
        1767258563.443634,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/docs/Examples/Printer.php',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.445591,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/docs/Examples/Printer.php',
            ),
        ),
    ],
    [
        1767258563.447494,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            822,
        ),
    ],
    [
        1767258563.806571,
        new Dependency(
            new FilePath(
                '/project/docs/Shared/Contributing.md',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.808613,
        new FileBegin(
            new FilePath(
                '/project/docs/Shared/Contributing.md',
            ),
        ),
    ],
    [
        1767258563.808707,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\Preprocess\\Task',
        ),
    ],
    [
        1767258563.808719,
        new FileSystemReadBegin(
            new FilePath(
                '/project/docs/Shared/Contributing.md',
            ),
        ),
    ],
    [
        1767258563.811228,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            373,
        ),
    ],
    [
        1767258563.812235,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.812263,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\Task',
        ),
    ],
    [
        1767258563.812382,
        new Dependency(
            new FilePath(
                '/project/composer.json',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.812496,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.812508,
        new FileEnd(
            FileResult::Success,
        ),
    ],
    [
        1767258563.817848,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.817875,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\Task',
        ),
    ],
    [
        1767258563.817945,
        new Dependency(
            new FilePath(
                '/project/composer.json',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.872301,
        new Dependency(
            new FilePath(
                '/project/packages/graphql/src/Builder/Config.php',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258563.903014,
        new FileSystemReadBegin(
            new FilePath(
                '/project/packages/graphql/src/Builder/Config.php',
            ),
        ),
    ],
    [
        1767258563.904762,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            1083,
        ),
    ],
    [
        1767258563.996173,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258563.9962,
        new FileEnd(
            FileResult::Success,
        ),
    ],
    [
        1767258564.599376,
        new Dependency(
            new FilePath(
                '/project/docs/Legend.md',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258564.616806,
        new FileBegin(
            new FilePath(
                '/project/docs/Legend.md',
            ),
        ),
    ],
    [
        1767258564.616908,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\Preprocess\\Task',
        ),
    ],
    [
        1767258564.616918,
        new FileSystemReadBegin(
            new FilePath(
                '/project/docs/Legend.md',
            ),
        ),
    ],
    [
        1767258564.618523,
        new FileSystemReadEnd(
            FileSystemReadResult::Success,
            1124,
        ),
    ],
    [
        1767258564.622253,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258564.62229,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\Task',
        ),
    ],
    [
        1767258564.622447,
        new Dependency(
            new FilePath(
                '/project/composer.json',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258564.622595,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258564.622605,
        new FileEnd(
            FileResult::Success,
        ),
    ],
    [
        1767258564.62605,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258564.626093,
        new TaskBegin(
            'LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\Task',
        ),
    ],
    [
        1767258564.626175,
        new Dependency(
            new FilePath(
                '/project/composer.json',
            ),
            DependencyResult::Found,
        ),
    ],
    [
        1767258564.640817,
        new TaskEnd(
            TaskResult::Success,
        ),
    ],
    [
        1767258564.655689,
        new FileSystemWriteBegin(
            new FilePath(
                '/project/README.md',
            ),
        ),
    ],
    [
        1767258564.692145,
        new FileSystemWriteEnd(
            FileSystemWriteResult::Success,
            4900,
        ),
    ],
    [
        1767258564.692194,
        new FileEnd(
            FileResult::Success,
        ),
    ],
    [
        1767258564.697014,
        new ProcessEnd(
            ProcessResult::Success,
        ),
    ],
];

return $events;
