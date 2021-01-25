<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Mixins;

use Closure;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use LastDragon_ru\LaraASP\Core\Routing\AcceptValidator;
use function array_merge;

/**
 * @internal
 */
class RouteMixin {
    public function accept(): Closure {
        return function (?string $accept): Route {
            /** @var \Illuminate\Routing\Route $this */
            $this->action[AcceptValidator::Key] = array_merge(
                (array) ($this->action[AcceptValidator::Key] ?? []), Arr::wrap($accept)
            );

            return $this;
        };
    }
}
