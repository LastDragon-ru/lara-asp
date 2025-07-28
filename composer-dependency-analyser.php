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

if ($root) {
    $config
        ->ignoreErrorsOnPackage('larastan/larastan', [ErrorType::UNUSED_DEPENDENCY])
        ->ignoreErrorsOnPackage('phpstan/phpstan-mockery', [ErrorType::UNUSED_DEPENDENCY])
        ->ignoreErrorsOnPackage('phpstan/phpstan-phpunit', [ErrorType::UNUSED_DEPENDENCY])
        ->ignoreErrorsOnPackage('phpstan/phpstan-strict-rules', [ErrorType::UNUSED_DEPENDENCY])
        ->ignoreErrorsOnPackage('spaze/phpstan-disallowed-calls', [ErrorType::UNUSED_DEPENDENCY]);
} else {
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
//
// In our case, tests located inside the same directory with class and
// `.gitattributes` is used to exclude them from the release. So we need
// to mark these excluded files as "dev".
$files = Finder::create()
    ->ignoreDotFiles(false)
    ->ignoreVCSIgnored(true)
    ->exclude('node_modules')
    ->exclude('vendor-bin')
    ->exclude('vendor')
    ->exclude('dev')
    ->in(dirname($path))
    ->name('.gitattributes')
    ->files();
$parse = static function (string $line): string {
    // Simplified parser
    // https://git-scm.com/docs/gitattributes
    $line   = mb_trim($line);
    $marker = ' export-ignore';

    if (str_starts_with($line, '#')) {
        return '';
    }

    if (!str_ends_with($line, $marker)) {
        return '';
    }

    // File?
    $line = mb_trim(mb_substr($line, 0, - mb_strlen($marker)));
    $line = match (pathinfo($line, PATHINFO_EXTENSION)) {
        ''      => "{$line}/*.php",
        'php'   => $line,
        default => '',
    };

    if (!str_contains($line, '*')) {
        $line = '';
    }

    // Convert
    if ($line) {
        $line = mb_ltrim($line, '/');
        $line = Glob::toRegex($line);
    }

    // Return
    return $line;
};

foreach ($files as $file) {
    // Parse
    $attributes = file($file->getPathname());
    $attributes = array_filter(array_map($parse, array_merge($attributes, [
        '/src/Testing       export-ignore',
    ])));

    if (!$attributes) {
        continue;
    }

    // Add as dev
    $dependencies = Finder::create()
        ->ignoreVCSIgnored(true)
        ->notName('composer-dependency-analyser.php')
        ->notName('monorepo-builder.php')
        ->exclude('node_modules')
        ->exclude('vendor-bin')
        ->exclude('vendor')
        ->exclude('dev')
        ->in($file->getPath())
        ->path($attributes)
        ->name('*.php')
        ->files();

    foreach ($dependencies as $dependency) {
        $config->addPathToScan($dependency->getPathname(), true);
    }
}

// Return
return $config;
