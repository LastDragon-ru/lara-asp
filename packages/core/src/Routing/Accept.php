<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Routing;

interface Accept {
    public const Any   = null;
    public const Json  = 'json';
    public const Html  = 'html';
    public const Image = 'image';
}
