<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Contracts;

interface Initializable {
    public function isInitialized(): bool;
}
