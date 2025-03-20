<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use Override;

/**
 * @implements Mutation<Link>
 */
abstract readonly class Base implements Mutation {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function nodes(): array {
        return [
            Link::class,
        ];
    }
}
