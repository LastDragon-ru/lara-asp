<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Requirements;

use Attribute;
use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use LastDragon_ru\LaraASP\Testing\PhpUnit\Requirements\Extension;
use LastDragon_ru\LaraASP\Testing\PhpUnit\Requirements\Requirement;
use Override;

use function sprintf;

/**
 * @see Extension
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RequiresComposerPackage implements Requirement {
    public function __construct(
        protected readonly string $package,
        protected readonly ?string $version = null,
    ) {
        // empty
    }

    #[Override]
    public function isSatisfied(): bool {
        return $this->version
            ? InstalledVersions::satisfies(new VersionParser(), $this->package, $this->version)
            : InstalledVersions::isInstalled($this->package);
    }

    #[Override]
    public function __toString(): string {
        return sprintf(
            'The package `%s` is not installed.',
            $this->package.($this->version ? ":{$this->version}" : ''),
        );
    }
}
