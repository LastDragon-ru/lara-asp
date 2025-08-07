<?php declare(strict_types = 1);

use Orchestra\Testbench\TestCase as TestbenchTestCase;
use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;
use ShipMonk\ComposerDependencyAnalyser\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;

// General
$config = (new Configuration())
    ->enableAnalysisOfUnusedDevDependencies()
    ->ignoreErrorsOnPackage('symfony/polyfill-php84', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('bamarni/composer-bin-plugin', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('laravel/scout', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreUnknownClasses([
        TestbenchTestCase::class,
    ]);

// Load composer.json
$path = Path::realpath(getopt('', ['composer-json:'])['composer-json'] ?? 'composer.json');
$root = Path::realpath(dirname(__FILE__).'/composer.json') === $path;

if (!$root) {
    $config->disableReportingUnmatchedIgnores();

    // fixme: Hotfix for https://github.com/shipmonk-rnd/composer-dependency-analyser/issues/216
    $config->ignoreErrorsOnPaths(
        [
            'packages/graphql-printer/src/Blocks/Document/Value.php',
            'packages/graphql-printer/src/Blocks/Document/InputValueDefinition.php',
            'packages/graphql-printer/src/Blocks/Document/ValueTest.php',
            'packages/graphql-printer/src/Blocks/Document/ValueTest.php',
            'packages/graphql-printer/src/Blocks/Document/ValueTest.php',
            'packages/graphql-printer/src/Blocks/Document/ValueTest.php',
            'packages/graphql-printer/src/Blocks/Document/ValueTest.php',
            'packages/graphql-printer/src/Blocks/Document/VariableDefinition.php',
            'packages/graphql-printer/src/Blocks/Document/Argument.php',
        ],
        [
            ErrorType::SHADOW_DEPENDENCY,
        ],
    );
}

// Configure paths
$files = Finder::create()
    ->in(dirname($path))
    ->ignoreVCSIgnored(true)
    ->ignoreDotFiles(true)
    ->exclude('node_modules')
    ->exclude('vendor-bin')
    ->exclude('vendor')
    ->exclude('dev')
    ->path(Glob::toRegex('*Test.php'))
    ->path(Glob::toRegex('*/**/*Test.php'))
    ->path(Glob::toRegex('*Test~*.php'))
    ->path(Glob::toRegex('*/**/*Test~*.php'))
    ->path(Glob::toRegex('*Test/*.php'))
    ->path(Glob::toRegex('*/**/*Test/*.php'))
    ->path(Glob::toRegex('*Test/**/*.php'))
    ->path(Glob::toRegex('*/**/*Test/**/*.php'))
    ->path(Glob::toRegex('src/Package/*.php'))
    ->path(Glob::toRegex('src/Package/**/*.php'))
    ->path(Glob::toRegex('packages/*/src/Package/*.php'))
    ->path(Glob::toRegex('packages/*/src/Package/**/*.php'))
    ->files();

foreach ($files as $file) {
    $config->addPathToScan($file->getPathname(), true);
}

// Return
return $config;
