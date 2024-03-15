<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Dev\App\Example;
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

// Configure paths
//
// In our case, tests located inside the same directory with class and
// `exclude-from-classmap` is used to exclude them from the class map.
// So we need to mark these excluded files as "dev".
$path     = Path::resolve($this->cwd, ($options->composerJson ?? 'composer.json'));
$json     = (string) file_get_contents($path);
$json     = json_decode($json, true, JSON_THROW_ON_ERROR);
$excluded = $json['autoload']['exclude-from-classmap'] ?? [];

if ($excluded) {
    $config = $config->disableComposerAutoloadPathScan();
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
    $regexp = '{('.implode(')|(', $regexp).')}';

    foreach ($composerJson->autoloadPaths as $absolutePath => $isDevPath) {
        if ($isDevPath) {
            $config = $config->addPathToScan($absolutePath, $isDevPath);
        } elseif (is_file($absolutePath)) {
            $config = $config->addPathToScan($absolutePath, (bool) preg_match($regexp, $absolutePath));
        } else {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolutePath));

            foreach ($iterator as $entry) {
                if (!$entry->isFile() || !in_array($entry->getExtension(), $config->getFileExtensions(), true)) {
                    continue;
                }

                $entryPath = $entry->getPathname();
                $config    = $config->addPathToScan($entryPath, (bool) preg_match($regexp, $entryPath));
            }
        }
    }
}

// Return
return $config;
