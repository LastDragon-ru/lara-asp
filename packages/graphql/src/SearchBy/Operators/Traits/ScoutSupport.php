<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Traits;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorPropertyDirective;

use function is_a;

/**
 * @mixin SearchByOperatorPropertyDirective
 */
trait ScoutSupport {
    public function __construct(
        private FieldResolver $fieldResolver,
    ) {
        parent::__construct();
    }

    protected function getFieldResolver(): FieldResolver {
        return $this->fieldResolver;
    }

    public function isBuilderSupported(string $builder): bool {
        return parent::isBuilderSupported($builder)
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
