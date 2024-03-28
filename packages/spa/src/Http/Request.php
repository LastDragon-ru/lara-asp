<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http;

use Illuminate\Foundation\Http\FormRequest;
use LastDragon_ru\LaraASP\Spa\Package;
use Override;

use function trigger_deprecation;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '6.2.0', 'Please use own class and `%s` trait.', WithValueProvider::class);

/**
 * @deprecated 6.2.0 Please use own class and {@see WithValueProvider} trait.
 */
abstract class Request extends FormRequest {
    use WithValueProvider {
        validated as private defaultValidated;
    }

    #[Override]
    public function validated(mixed $key = null, mixed $default = null): mixed {
        return $this->defaultValidated($key, $default);
    }
}
