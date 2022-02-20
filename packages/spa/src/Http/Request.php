<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

use function data_get;
use function is_null;

abstract class Request extends FormRequest {
    public function validated(mixed $key = null, mixed $default = null): mixed {
        // We need `\Illuminate\Validation\Validator::getRules()` but it doesn't
        // exists in `\Illuminate\Contracts\Validation\Validator`.
        $validator = $this->getValidatorInstance();
        $validated = parent::validated();

        if (!($validator instanceof Validator)) {
            return $validated;
        }

        // Replace values
        foreach ($validator->getRules() as $attribute => $rules) {
            /** @var ValueProvider|null $provider */
            $provider  = Arr::last($rules, static function ($rule): bool {
                return $rule instanceof ValueProvider;
            });
            $attribute = (string) $attribute;

            if ($provider && Arr::has($validated, $attribute)) {
                $value = Arr::get($validated, $attribute);

                if (!is_null($value)) {
                    Arr::set($validated, $attribute, $provider->getValue($value));
                }
            }
        }

        // Key?
        if ($key !== null) {
            $validated = data_get($validated, $key, $default);
        }

        // Return
        return $validated;
    }
}
