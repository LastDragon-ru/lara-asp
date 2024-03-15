<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Dev\App\Example;
use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->enableAnalysisOfUnusedDevDependencies()
    ->disableReportingUnmatchedIgnores()
    ->ignoreUnknownClasses([
        Example::class,
    ])
    ->ignoreErrorsOnPackage('symfony/polyfill-php83', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('bamarni/composer-bin-plugin', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('orchestra/testbench', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('orchestra/testbench-core', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('phpstan/phpstan', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('laravel/scout', [ErrorType::DEV_DEPENDENCY_IN_PROD]);
