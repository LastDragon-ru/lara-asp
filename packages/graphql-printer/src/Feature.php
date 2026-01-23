<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;

/**
 * @internal
 */
enum Feature {
    case OneOfDirective;
    case SchemaDescription;

    public function since(): string {
        return match ($this) {
            self::OneOfDirective    => '15.21.0',
            self::SchemaDescription => '15.30.0',
        };
    }

    public function available(): bool {
        /** @var array<string, bool> $cache */
        static $cache = [];

        if (!isset($cache[$this->name])) {
            $since              = $this->since();
            $package            = 'webonyx/graphql-php';
            $cache[$this->name] = InstalledVersions::satisfies(new VersionParser(), $package, ">={$since}");
        }

        return $cache[$this->name];
    }
}
