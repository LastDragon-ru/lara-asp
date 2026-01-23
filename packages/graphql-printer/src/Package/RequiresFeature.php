<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Package;

use Attribute;
use LastDragon_ru\GraphQLPrinter\Feature;
use LastDragon_ru\PhpUnit\Extensions\Requirements\Contracts\Requirement;
use Override;

use function sprintf;

/**
 * @internal
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RequiresFeature implements Requirement {
    public function __construct(
        protected readonly Feature $feature,
    ) {
        // empty
    }

    #[Override]
    public function isSatisfied(): bool {
        return $this->feature->available();
    }

    #[Override]
    public function __toString(): string {
        return sprintf(
            'The `%s` is not available.',
            $this->feature->name,
        );
    }
}
