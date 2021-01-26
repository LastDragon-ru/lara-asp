<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Http;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use function is_null;

abstract class Request extends FormRequest {
    /**
     * @inheritdoc
     */
    public function validated() {
        // We need `\Illuminate\Validation\Validator::getRules()` but it doesn't
        // exists in `\Illuminate\Contracts\Validation\Validator`.
        $validator = $this->getValidatorInstance();
        $validated = parent::validated();

        if (!($validator instanceof Validator)) {
            return $validated;
        }

        // Replace values
        foreach ($validator->getRules() as $attribute => $rules) {
            /** @var \LastDragon_ru\LaraASP\Core\Http\ValueProvider $provider */
            $provider = Arr::last($rules, function ($rule) {
                return $rule instanceof ValueProvider;
            });

            if ($provider && Arr::has($validated, $attribute)) {
                $value = Arr::get($validated, $attribute);

                if (!is_null($value)) {
                    Arr::set($validated, $attribute, $provider->getValue($value));
                }
            }
        }

        // Return
        return $validated;
    }
}
