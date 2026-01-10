<?php declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration

namespace LastDragon_ru\LaraASP\Formatter\Docs\Examples\Uppercase;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Formatter\Config\Config;
use LastDragon_ru\LaraASP\Formatter\Config\Format;
use LastDragon_ru\LaraASP\Formatter\Contracts\Format as FormatContract;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Formatter\PackageConfig;
use Override;
use Stringable;

use function mb_strtoupper;

/**
 * @implements FormatContract<null, Stringable|string|null>
 */
class UppercaseFormat implements FormatContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public function __invoke(mixed $value): string {
        return mb_strtoupper((string) $value);
    }
}

Formatter::macro('uppercase', function (Stringable|string|null $value): string {
    return $this->format('uppercase', $value);
});

Example::config(PackageConfig::class, static function (Config $config): void {
    $config->formats['uppercase'] = new Format(
        UppercaseFormat::class,
    );
});

// @phpstan-ignore method.notFound
Example::dump(app()->make(Formatter::class)->uppercase('string'));
