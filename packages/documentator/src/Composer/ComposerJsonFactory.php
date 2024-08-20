<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Composer;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;

readonly class ComposerJsonFactory {
    public function __construct(
        protected Serializer $serializer,
    ) {
        // empty
    }

    public function createFromJson(string $json): ComposerJson {
        return $this->serializer->deserialize(ComposerJson::class, $json, 'json');
    }
}
