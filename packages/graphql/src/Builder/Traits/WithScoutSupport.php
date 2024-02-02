<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Traits;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use Override;

use function is_a;

/**
 * @mixin Operator
 */
trait WithScoutSupport {
    #[Override]
    public function isAvailable(string $builder, Context $context): bool {
        return parent::isAvailable($builder, $context)
            || (is_a($builder, ScoutBuilder::class, true) && $this->isScoutSupported());
    }

    protected function isScoutSupported(): bool {
        $version   = $this->getScoutVersion();
        $supported = $version === null
            || InstalledVersions::satisfies(new VersionParser(), 'laravel/scout', $version);

        return $supported;
    }

    protected function getScoutVersion(): ?string {
        return null;
    }
}
