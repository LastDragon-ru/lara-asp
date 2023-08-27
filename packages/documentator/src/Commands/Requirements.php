<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use Exception;
use Illuminate\Console\Command;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Metadata\Metadata;
use LastDragon_ru\LaraASP\Documentator\Metadata\Storage;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\Utils\Git;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Utils\Version;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use Symfony\Component\Console\Attribute\AsCommand;

use function array_combine;
use function array_keys;
use function array_merge;
use function array_search;
use function array_unique;
use function assert;
use function count;
use function end;
use function explode;
use function getcwd;
use function is_array;
use function json_decode;
use function range;
use function reset;
use function trim;
use function uksort;
use function view;

use const JSON_THROW_ON_ERROR;

#[AsCommand(
    name       : Requirements::Name,
    description: 'Generates a table with the required versions of PHP/Laravel in Markdown format.',
)]
class Requirements extends Command {
    public const Name = Package::Name.':requirements';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    public $signature = self::Name.<<<'SIGNATURE'
        {cwd? : working directory (should be a git repository)}
    SIGNATURE;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected $help = <<<'HELP'
        Requirements will be cached into `<cwd>/metadata.json`. You can also use
        this file to specify the required requirements. For example, to include
        PHP only:

        ```json
        {
            "require": {
                "php": "PHP"
            }
        }
        ```
        HELP;

    public function __invoke(Git $git, Serializer $serializer): void {
        // Get Versions
        $cwd  = Cast::toString($this->argument('cwd') ?? getcwd());
        $tags = $this->getPackageVersions($git, $cwd, ['HEAD']);

        // Collect requirements
        $storage  = new Storage($serializer, $cwd);
        $metadata = $storage->load();
        $packages = $metadata->require ?: [
            'php'               => 'PHP',
            'laravel/framework' => 'Laravel',
        ];

        foreach ($tags as $tag) {
            // Cached?
            if ($tag !== 'HEAD' && array_keys($metadata->requirements[$tag] ?? []) === array_keys($packages)) {
                continue;
            }

            // Load
            $package = $this->getPackageInfo($git, $tag, $cwd);

            if (!$package) {
                break;
            }

            // Update
            $metadata->requirements[$tag] = [];

            foreach ($packages as $key => $title) {
                $metadata->requirements[$tag][$key] = explode('|', Cast::toString($package['require'][$key] ?? ''));
            }
        }

        // Cleanup
        foreach ($metadata->requirements as $tag => $requirement) {
            if (!isset($tags[$tag])) {
                unset($metadata->requirements[$tag]);
                break;
            }

            foreach ($requirement as $package => $versions) {
                if (!isset($packages[$package])) {
                    unset($metadata->requirements[$tag][$package]);
                }
            }
        }

        // Save
        $storage->save($metadata);

        // Prepare
        $requirements = $this->getRequirements($packages, $metadata);

        // Render
        $package = Package::Name;
        $output  = view("{$package}::requirements.markdown", [
            'packages'     => $packages,
            'requirements' => $requirements,
        ])->render();
        $output  = trim($output);

        $this->output->writeln($output);
    }

    /**
     * @param list<string> $tags
     *
     * @return array<string, string>
     */
    protected function getPackageVersions(Git $git, string $cwd, array $tags = []): array {
        $tags = array_merge($tags, $git->getTags(Version::isVersion(...), $cwd));
        $tags = array_unique($tags);
        $tags = array_combine($tags, $tags);

        uksort($tags, static fn($a, $b) => -Version::compare($a, $b));

        return $tags;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    protected function getPackageInfo(Git $git, string $tag, string $cwd): ?array {
        try {
            $package = $git->getFile(Path::join($cwd, 'composer.json'), $tag, $cwd);
            $package = json_decode($package, true, flags: JSON_THROW_ON_ERROR);

            assert(is_array($package));
        } catch (Exception) {
            $package = null;
        }

        return $package;
    }

    /**
     * @param array<string, string> $packages
     *
     * @return array<string, array<string, list<string|array{string, string}>>>
     */
    protected function getRequirements(array $packages, Metadata $metadata): array {
        // Extract
        $requirements = [];

        foreach ($metadata->requirements as $versionName => $versionPackages) {
            foreach ($versionPackages as $packageName => $packageVersions) {
                foreach ($packageVersions as $packageVersion) {
                    $requirements[$packageName][$packageVersion] ??= [];
                    $requirements[$packageName][$packageVersion][] = $versionName;
                }
            }
        }

        // Sort
        $priorities = array_combine(array_keys($packages), range(0, count($packages) - 1));

        uksort($requirements, static function (string $a, string $b) use ($priorities): int {
            return $priorities[$a] <=> $priorities[$b];
        });

        // Merge
        $versions = array_keys($metadata->requirements);

        foreach ($requirements as &$packageVersions) {
            foreach ($packageVersions as &$versionNames) {
                $versionNames = $this->getMergedVersions($versions, $versionNames);
            }
        }

        // Return
        return $requirements;
    }

    /**
     * @param list<string> $versions
     * @param list<string> $merge
     *
     * @return list<string|array{string, string}>
     */
    protected function getMergedVersions(array $versions, array $merge): array {
        // Group
        $ranges  = [];
        $current = [];

        foreach ($merge as $key => $version) {
            $index       = array_search($version, $versions, true);
            $nextMerge   = $merge[$key + 1] ?? null;
            $nextVersion = $index !== false ? ($versions[$index + 1] ?? null) : null;

            $current[] = $version;

            if ($nextMerge !== $nextVersion) {
                $ranges[] = $current;
                $current  = [];
            }
        }

        if ($current) {
            $ranges[] = $current;
        }

        // Merge
        $merged = [];

        foreach ($ranges as $range) {
            if (count($range) > 2) {
                $merged[] = [reset($range), end($range)];
            } elseif (count($range) === 2) {
                $merged[] = reset($range);
                $merged[] = end($range);
            } else {
                $merged[] = reset($range);
            }
        }

        return $merged;
    }
}
