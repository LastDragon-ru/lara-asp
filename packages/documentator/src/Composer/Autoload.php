<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Composer;

use LastDragon_ru\LaraASP\Serializer\Contracts\Partial;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class Autoload implements Serializable, Partial {
    public function __construct(
        /**
         * @var array<string, string|list<string>>|null
         */
        #[SerializedName('psr-4')]
        public ?array $psr4 = null,
    ) {
        // empty
    }
}
