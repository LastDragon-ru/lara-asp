<?php declare(strict_types = 1);

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use ShipMonk\ComposerDependencyAnalyser\CliOptions;
use ShipMonk\ComposerDependencyAnalyser\ComposerJson;
use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;
use ShipMonk\ComposerDependencyAnalyser\Initializer;
use ShipMonk\ComposerDependencyAnalyser\Path;

// Assertions
assert(isset($this) && $this instanceof Initializer);
assert(isset($options) && $options instanceof CliOptions);
assert(isset($composerJson) && $composerJson instanceof ComposerJson);

// General
$config = (new Configuration())
    ->enableAnalysisOfUnusedDevDependencies()
    ->ignoreErrorsOnPackage('symfony/deprecation-contracts', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/polyfill-php83', [ErrorType::UNUSED_DEPENDENCY])
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
$path = Path::resolve($this->cwd, ($options->composerJson ?? 'composer.json'));
$json = (string) file_get_contents($path);
$json = json_decode($json, true, JSON_THROW_ON_ERROR);
$root = Path::realpath(dirname(__FILE__).'/composer.json') === Path::realpath($path);

if (!$root) {
    $config->disableReportingUnmatchedIgnores();
}

// Configure paths
//
// In our case, tests located inside the same directory with class and
// `exclude-from-classmap` is used to exclude them from the class map.
// So we need to mark these excluded files as "dev".
//
// Also, we don't want to check examples. The `autoload-dev.exclude-from-classmap`
// can be used to ignore them.
$excluded = $json['autoload']['exclude-from-classmap'] ?? [];
$ignored  = $json['autoload-dev']['exclude-from-classmap'] ?? [];

if ($excluded || $ignored) {
    $config    = $config->disableComposerAutoloadPathScan();
    $regexp    = static function (array $excluded) use ($path): ?string {
        $regexp = array_map(
            static function (string $exclude) use ($path): string {
                // Similar to how composer process it, but not the exact match.
                $exclude = dirname($path)."/{$exclude}";
                $exclude = preg_replace('{/+}', '/', preg_quote(trim(strtr($exclude, '\\', '/'), '/')));
                $exclude = strtr($exclude, ['\\*\\*' => '.+?', '\\*' => '[^/]+?']);

                return $exclude;
            },
            $excluded,
        );
        $regexp = $regexp
            ? '{('.implode(')|(', $regexp).')}'
            : null;

        return $regexp;
    };
    $ignored   = $regexp($ignored);
    $excluded  = $regexp($excluded);
    $processor = static function (string $path, bool $isDev) use (&$processor, $config, $excluded, $ignored): void {
        if (is_file($path)) {
            $isDev     = $isDev || ($excluded && (bool) preg_match($excluded, $path));
            $isIgnored = $isDev && ($ignored && (bool) preg_match($ignored, $path));

            if (!$isIgnored) {
                $config->addPathToScan($path, $isDev);
            }
        } else {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

            foreach ($iterator as $entry) {
                if (!$entry->isFile() || !in_array($entry->getExtension(), $config->getFileExtensions(), true)) {
                    continue;
                }

                $processor($entry->getPathname(), $isDev);
            }
        }
    };

    foreach ($composerJson->autoloadPaths as $absolutePath => $isDevPath) {
        $processor($absolutePath, $isDevPath);
    }
}

// Return
return $config;
