<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http;

use Illuminate\Foundation\Http\FormRequest;
use Override;

/**
 * @deprecated ${version} Please use own class and {@see WithValueProvider} trait.
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
