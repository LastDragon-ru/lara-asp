<?php declare(strict_types = 1);

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;
use ShipMonk\ComposerDependencyAnalyser\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;

// General
$config = (new Configuration())
    ->enableAnalysisOfUnusedDevDependencies()
    ->ignoreErrorsOnPackage('bamarni/composer-bin-plugin', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('laravel/scout', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreUnknownClasses([
        FormRequest::class,
        RefreshDatabase::class,
        RefreshDatabaseState::class,
        TestCase::class,
        TestbenchTestCase::class,
    ]);

// Load composer.json
$path = Path::realpath(getopt('', ['composer-json:'])['composer-json'] ?? 'composer.json');
$root = Path::realpath(dirname(__FILE__).'/composer.json') === $path;

if ($root) {
    $config
        ->ignoreErrorsOnPackage('phpstan/phpstan-mockery', [ErrorType::UNUSED_DEPENDENCY])
        ->ignoreErrorsOnPackage('phpstan/phpstan-phpunit', [ErrorType::UNUSED_DEPENDENCY])
        ->ignoreErrorsOnPackage('phpstan/phpstan-strict-rules', [ErrorType::UNUSED_DEPENDENCY])
        ->ignoreErrorsOnPackage('spaze/phpstan-disallowed-calls', [ErrorType::UNUSED_DEPENDENCY]);
} else {
    $config->disableReportingUnmatchedIgnores();
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
    $line = trim($line);

    if (str_starts_with($line, '#')) {
        $line = '';
    }

    if (str_ends_with($line, ' export-ignore')) {
        $line = trim(explode(' ', $line, 2)[0] ?? '');
    } else {
        $line = '';
    }

    // File?
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
        $line = ltrim($line, '/');
        $line = Glob::toRegex($line);
    }

    // Return
    return $line;
};

foreach ($files as $file) {
    // Parse
    $attributes = file($file->getPathname());
    $attributes = array_filter(array_map($parse, $attributes));

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
